<?php

namespace App\Console\Commands\Backup;

use App\Models\ActivityLog;
use App\Models\Backup;
use App\Services\Backup\BackupEncryptor;
use App\Services\Backup\SystemBackupService;
use App\Support\Enums\BackupStatus;
use App\Support\Enums\BackupType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Console\Helper\Table;
use Throwable;
use ZipArchive;

/**
 * backup:restore — استعادة نظام كامل من نسخة احتياطية. CLI فقط — لا زر Restore
 * في Filament بأي شكل. راجع docs/RESTORE-RUNBOOK.md قبل أي استخدام فعلي.
 *
 * الاستخدام:
 *   php artisan backup:restore {backup_id} --force
 *
 * --force إلزامي في بيئة production (حماية ضد التنفيذ العرضي).
 * الأمر يطلب كتابة عبارة تأكيد صريحة (وليس مجرد y/n)، يأخذ نسخة حماية قبل
 * البدء، ويتحقق من checksum قبل فك تشفير/فك ضغط أي شيء.
 */
class RestoreBackupCommand extends Command
{
    protected $signature = 'backup:restore {backup : ULID للنسخة الاحتياطية (من جدول backups)} {--force : تجاوز حماية بيئة production}';

    protected $description = 'استعادة النظام من نسخة احتياطية — CLI فقط، يتطلب تأكيداً صريحاً';

    public function handle(BackupEncryptor $encryptor, SystemBackupService $backupService): int
    {
        $backupId = $this->argument('backup');

        /** @var Backup|null $backup */
        $backup = Backup::query()->find($backupId);

        if (! $backup) {
            $this->error("لا توجد نسخة احتياطية بالمعرّف: {$backupId}");
            return self::FAILURE;
        }

        if ($backup->status !== BackupStatus::Completed) {
            $this->error('لا يمكن الاستعادة من نسخة غير مكتملة.');
            return self::FAILURE;
        }

        // ==================== 1. عرض خطة الاستعادة ====================
        $this->showRestorePlan($backup);

        // ==================== 2. حماية production ====================
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('أنت في بيئة production. أعد تشغيل الأمر مع --force بعد التأكد التام من الخطة أعلاه.');
            return self::FAILURE;
        }

        // ==================== 3. تأكيد صريح (ليس y/n) ====================
        $this->warn('هذه العملية ستستبدل قاعدة البيانات الحالية (وملفات storage إن كانت نسخة كاملة).');
        $confirmationPhrase = 'RESTORE-'.$backup->id;
        $typed = $this->ask("اكتب \"{$confirmationPhrase}\" بالضبط للمتابعة");

        if ($typed !== $confirmationPhrase) {
            $this->error('نص التأكيد غير مطابق — تم إلغاء الاستعادة.');
            return self::FAILURE;
        }

        // ==================== 4. نسخة حماية قبل الاستعادة ====================
        $this->info('جارٍ أخذ نسخة حماية من الحالة الحالية قبل الاستعادة...');
        $safetyBackup = Backup::create([
            'name'   => 'pre-restore-safety-'.now()->format('Ymd-His'),
            'type'   => BackupType::Database,
            'status' => BackupStatus::Pending,
        ]);

        try {
            $backupService->run($safetyBackup); // تنفيذ متزامن هنا عمداً — عملية CLI صريحة وليست HTTP request
        } catch (Throwable $e) {
            $this->error("فشلت نسخة الحماية — تم إيقاف الاستعادة لأسباب أمان: {$e->getMessage()}");
            return self::FAILURE;
        }

        $this->info("✅ نسخة الحماية جاهزة: {$safetyBackup->id} (احتفظ بها حتى تتأكد من نجاح الاستعادة).");

        // ==================== 5. تنزيل + تحقق checksum ====================
        $workDir = storage_path('app/private/tmp/restore-'.Str::ulid());
        File::ensureDirectoryExists($workDir);

        try {
            $this->info('جارٍ تنزيل الأرشيف والتحقق من checksum...');
            $encryptedPath = $workDir.'/archive.zip.enc';
            File::put($encryptedPath, Storage::disk($backup->disk)->get($backup->path));

            $actualChecksum = hash_file('sha256', $encryptedPath);
            if ($actualChecksum !== $backup->checksum) {
                $this->error('❌ checksum غير مطابق — الأرشيف قد يكون تالفاً أو مُعدَّلاً. تم إيقاف الاستعادة.');
                return self::FAILURE;
            }
            $this->info('✅ checksum مطابق.');

            // ==================== 6. فك التشفير وفك الضغط ====================
            $this->info('جارٍ فك التشفير...');
            $zipPath = $workDir.'/archive.zip';
            $encryptor->decryptFile($encryptedPath, $zipPath);

            $extractDir = $workDir.'/extracted';
            File::ensureDirectoryExists($extractDir);

            $zip = new ZipArchive();
            $zip->open($zipPath);
            $zip->extractTo($extractDir);
            $zip->close();

            $manifest = json_decode(File::get($extractDir.'/manifest.json'), true);
            $this->info('محتوى manifest.json:');
            $this->line(json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            if (! $this->confirm('هل تريد المتابعة بتنفيذ الاستعادة الفعلية الآن؟', false)) {
                $this->warn('تم إلغاء الاستعادة بعد المراجعة — لم يتغيّر شيء في النظام.');
                return self::SUCCESS;
            }

            // ==================== 7. استعادة قاعدة البيانات ====================
            $this->restoreDatabase($extractDir.'/database.sql');
            $this->info('✅ تمت استعادة قاعدة البيانات.');

            // ==================== 8. استعادة ملفات storage (نسخة كاملة فقط) ====================
            if ($backup->type === BackupType::Full && File::isDirectory($extractDir.'/storage')) {
                $this->info('جارٍ استعادة ملفات storage...');
                File::copyDirectory($extractDir.'/storage', storage_path());
                $this->info('✅ تمت استعادة ملفات storage.');
            }

            ActivityLog::record(
                eventType: 'backup.restored',
                metadata: [
                    'backup_id'      => $backup->id,
                    'safety_backup'  => $safetyBackup->id,
                    'restored_by'    => getenv('USER') ?: getenv('USERNAME') ?: 'cli',
                ],
            );

            $this->newLine();
            $this->info('✅ اكتملت الاستعادة. الخطوات التالية الموصى بها:');
            $this->line('  1) php artisan migrate --force   (في حال وجود migrations أحدث من تاريخ النسخة)');
            $this->line('  2) php artisan config:clear && php artisan cache:clear');
            $this->line('  3) تحقّق يدوياً من عمل التطبيق قبل إعلامه للمستخدمين.');

            return self::SUCCESS;
        } finally {
            File::deleteDirectory($workDir);
        }
    }

    private function showRestorePlan(Backup $backup): void
    {
        $this->info('==================== خطة الاستعادة ====================');
        (new Table($this->output))
            ->setHeaders(['الحقل', 'القيمة'])
            ->setRows([
                ['الاسم', $backup->name],
                ['النوع', $backup->type->label()],
                ['تاريخ الإنشاء', optional($backup->completed_at)->toDateTimeString()],
                ['الحجم', $backup->humanSize() ?? '—'],
                ['Checksum', $backup->checksum],
                ['disk', $backup->disk],
                ['سيتم استبدال', $backup->type === BackupType::Full ? 'قاعدة البيانات + ملفات storage الضرورية' : 'قاعدة البيانات فقط'],
            ])
            ->render();
        $this->info('==========================================================');
    }

    private function restoreDatabase(string $sqlPath): void
    {
        $connection = config('database.connections.'.config('database.default'));

        if (($connection['driver'] ?? null) !== 'mysql') {
            throw new \RuntimeException('backup:restore يدعم mysql فقط حالياً.');
        }

        $command = [
            'mysql',
            '--host='.$connection['host'],
            '--port='.$connection['port'],
            '--user='.$connection['username'],
            $connection['database'],
        ];

        $result = Process::timeout(1800)
            ->env(['MYSQL_PWD' => $connection['password'] ?? ''])
            ->input(File::get($sqlPath))
            ->run($command);

        if (! $result->successful()) {
            throw new \RuntimeException('فشل استيراد قاعدة البيانات: '.$result->errorOutput());
        }
    }
}

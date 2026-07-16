<?php

namespace App\Services\Backup;

use App\Models\ActivityLog;
use App\Models\Backup;
use App\Support\Enums\BackupType;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;
use ZipArchive;

/**
 * SystemBackupService — ينشئ نسخة احتياطية تشغيلية كاملة (قاعدة بيانات، أو
 * قاعدة بيانات + ملفات storage الضرورية)، يشفّرها، ويخزّنها.
 *
 * ⚠️ لا تُشغَّل هذه الخدمة أبداً داخل HTTP request مباشر — فقط من:
 *  - RunSystemBackupJob (طابور backups، على connection الافتراضي database)
 *  - أوامر Artisan المجدولة (backup:database / backup:full)
 * راجع docs/BACKUP-SYSTEM.md.
 *
 * ⚠️ لا نستخدم shell_exec — ننفّذ mysqldump عبر Illuminate\Support\Facades\Process
 * (يغلّف Symfony Process) بمصفوفة أوامر منفصلة (بدون تمرير عبر shell)، وكلمة
 * مرور قاعدة البيانات تُمرَّر عبر متغيّر بيئة للعملية الفرعية (MYSQL_PWD) بدل
 * ظهورها في سطر الأوامر (وبالتالي في `ps aux`).
 */
class SystemBackupService
{
    public function __construct(
        private readonly BackupEncryptor $encryptor,
    ) {}

    public function run(Backup $backup): void
    {
        if (! $this->encryptor->hasKey()) {
            $backup->markFailed(
                'BACKUP_ENCRYPTION_KEY غير معرَّف — تم رفض إنشاء نسخة غير مشفَّرة. راجع docs/BACKUP-SYSTEM.md.'
            );
            return;
        }

        $backup->markRunning();

        // ⚠️ $workDir يُنشَأ داخل try عمداً: أي فشل هنا (مثلاً صلاحيات الكتابة) يجب أن
        // يؤدي أيضاً إلى Failed مثل أي خطأ آخر يحدث بعد markRunning() — لا نريد أي
        // مسار فشل "غير مغطّى" يترك السجل عالقاً في Running. راجع دائماً finally أدناه
        // (يعتمد على $workDir، لذا يُعرَّف كمتغيّر خارج try لكن يُملأ بداخله).
        $workDir = storage_path('app/private/tmp/system-backup-'.Str::ulid());

        try {
            File::ensureDirectoryExists($workDir);

            $dumpPath = $this->dumpDatabase($workDir);

            $manifest = [
                'name'          => $backup->name,
                'type'          => $backup->type->value,
                'created_at'    => now()->toIso8601String(),
                'app_env'       => app()->environment(),
                'laravel'       => app()->version(),
                'database'      => [
                    'driver'   => config('database.default'),
                    'dump_file' => basename($dumpPath),
                ],
                'storage_paths' => [],
                // ⚠️ لا تضف أي مفتاح/سر هنا مهما كان — manifest غير مشفَّر منطقياً
                // (يُقرأ لاحقاً أثناء الاستعادة قبل توفر المفتاح أحياناً).
            ];

            $archivePath = $workDir.'/archive.zip';
            $zip = new ZipArchive();
            $zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            $zip->addFile($dumpPath, 'database.sql');

            if ($backup->type === BackupType::Full) {
                $manifest['storage_paths'] = $this->addStorageFiles($zip);
            }

            $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $zip->close();

            $encryptedPath = $workDir.'/archive.zip.enc';
            $this->encryptor->encryptFile($archivePath, $encryptedPath);
            $checksum = $this->encryptor->checksum($encryptedPath);

            $disk = config('backups.system_backup.disk');
            $dir  = trim(config('backups.system_backup.path'), '/');
            $filename = $backup->id.'.zip.enc';
            $storagePath = "{$dir}/{$filename}";

            Storage::disk($disk)->put($storagePath, File::get($encryptedPath));

            $sizeBytes = Storage::disk($disk)->size($storagePath);

            $backup->markCompleted(
                disk: $disk,
                path: $storagePath,
                sizeBytes: $sizeBytes,
                checksum: $checksum,
                encrypted: true,
            );

            ActivityLog::record(
                eventType: 'backup.created',
                userId: $backup->triggered_by_user_id,
                entityType: Backup::class,
                entityId: $backup->id,
                metadata: [
                    'name'   => $backup->name,
                    'type'   => $backup->type->value,
                    'size'   => $sizeBytes,
                    'checksum' => $checksum,
                ],
            );
        } catch (Throwable $e) {
            Log::error('SystemBackupService: فشل إنشاء نسخة احتياطية', [
                'backup_id' => $backup->id,
                'error'     => $e->getMessage(),
            ]);
            $backup->markFailed($e->getMessage());

            ActivityLog::record(
                eventType: 'backup.failed',
                userId: $backup->triggered_by_user_id,
                entityType: Backup::class,
                entityId: $backup->id,
                metadata: ['reason' => $e->getMessage()],
            );

            throw $e; // إعادة الرمي — يسمح لـ Job بإعادة المحاولة وتسجيل failed_jobs
        } finally {
            File::deleteDirectory($workDir);
        }
    }

    /**
     * يتحقق من سلامة نسخة موجودة عبر مقارنة checksum المخزَّن مع الملف الفعلي.
     */
    public function verifyIntegrity(Backup $backup): bool
    {
        if (! $backup->disk || ! $backup->path || ! $backup->checksum) {
            return false;
        }

        if (! Storage::disk($backup->disk)->exists($backup->path)) {
            return false;
        }

        $tmp = storage_path('app/private/tmp/verify-'.Str::ulid().'.enc');
        File::put($tmp, Storage::disk($backup->disk)->get($backup->path));

        $actual = hash_file('sha256', $tmp);
        File::delete($tmp);

        $verified = $actual === $backup->checksum;

        $backup->update([
            'integrity_verified'   => $verified,
            'integrity_checked_at' => now(),
        ]);

        return $verified;
    }

    // ==================== Helpers ====================

    private function dumpDatabase(string $workDir): string
    {
        $connection = config('database.connections.'.config('database.default'));

        if (($connection['driver'] ?? null) !== 'mysql') {
            throw new RuntimeException(
                'SystemBackupService يدعم mysqldump فقط حالياً (driver الحالي: '.($connection['driver'] ?? 'غير معروف').').'
            );
        }

        $dumpPath = $workDir.'/database.sql';

        $command = [
            'mysqldump',
            '--host='.$connection['host'],
            '--port='.$connection['port'],
            '--user='.$connection['username'],
            '--single-transaction',
            '--quick',
            '--routines',
            '--triggers',
            '--result-file='.$dumpPath,
            $connection['database'],
        ];

        $result = Process::timeout(config('backups.system_backup.job_timeout', 1800))
            ->env(['MYSQL_PWD' => $connection['password'] ?? ''])
            ->run($command);

        if (! $result->successful()) {
            throw new RuntimeException('فشل تنفيذ mysqldump: '.$result->errorOutput());
        }

        if (! File::exists($dumpPath) || File::size($dumpPath) === 0) {
            throw new RuntimeException('ملف نسخة قاعدة البيانات فارغ أو غير موجود بعد تنفيذ mysqldump.');
        }

        return $dumpPath;
    }

    /** @return array<int,string> قائمة المسارات المُضمَّنة فعلياً (للـ manifest) */
    private function addStorageFiles(ZipArchive $zip): array
    {
        $included = config('backups.system_backup.include_storage_paths', []);
        $excluded = config('backups.system_backup.exclude_patterns', []);
        $addedPaths = [];

        foreach ($included as $relativeRoot) {
            $fullRoot = storage_path($relativeRoot);
            if (! is_dir($fullRoot)) {
                continue;
            }

            $this->addDirectoryToZip($zip, $fullRoot, 'storage/'.$relativeRoot, $excluded);
            $addedPaths[] = $relativeRoot;
        }

        return $addedPaths;
    }

    private function addDirectoryToZip(ZipArchive $zip, string $dir, string $zipPrefix, array $excludedPatterns): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            $realPath = $file->getPathname();

            foreach ($excludedPatterns as $pattern) {
                if (str_contains($realPath, $pattern)) {
                    continue 2;
                }
            }

            if ($file->isFile()) {
                $localName = $zipPrefix.'/'.ltrim(str_replace($dir, '', $realPath), '/\\');
                $zip->addFile($realPath, $localName);
            }
        }
    }
}

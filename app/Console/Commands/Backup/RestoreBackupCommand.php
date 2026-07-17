<?php

namespace App\Console\Commands\Backup;

use App\Exceptions\Backup\BackupIntegrityException;
use App\Exceptions\Backup\BackupManifestException;
use App\Exceptions\Backup\BackupNotFoundException;
use App\Exceptions\Backup\BackupRestoreException;
use App\Models\ActivityLog;
use App\Models\Backup;
use App\Services\Backup\RestoreService;
use App\Support\Enums\BackupStatus;
use App\Support\Enums\BackupType;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\Table;

/**
 * backup:restore — استعادة قاعدة البيانات من نسخة احتياطية. CLI فقط — لا زر
 * Restore في Filament بأي شكل. راجع docs/RESTORE-RUNBOOK.md قبل أي استخدام فعلي.
 *
 * الاستخدام:
 *   php artisan backup:restore {backup_id} --force
 *
 * --force إلزامي في بيئة production (حماية ضد التنفيذ العرضي).
 * الأمر يطلب كتابة عبارة تأكيد صريحة (وليس مجرد y/n) قبل أي تنفيذ فعلي.
 *
 * ⚠️ Restore Engine — المرحلة الثانية (الحالية): بعد تأكيد المستخدم الصريح،
 * هذا الأمر يستدعي RestoreService::run() **مرة واحدة فقط**. RestoreService هي
 * المالكة الوحيدة لدورة حياة العملية بالكامل: تحقق checksum → فك تشفير → فتح
 * ZIP → قراءة manifest → استخراج آمن (Zip Slip) → نسخة احتياطية طارئة →
 * Maintenance Mode → استيراد mysql فعلي (عبر DatabaseRestoreService) → تنظيف
 * كامل لمجلد العمل المؤقت. لا يوجد في هذا الأمر أي فك تشفير أو استخراج أو
 * استيراد مكرَّر — أُزيل عمداً في المرحلة الثانية لأنه كان يسبب ازدواجية
 * (وخطر تنفيذ استيراد مرتين) بعد أن أصبحت RestoreService تنفّذ الاستعادة
 * الفعلية بنفسها.
 *
 * ⚠️ استعادة الملفات (storage/) لا تزال تحقق فقط في هذه المرحلة (لم تُنفَّذ
 * فعلياً بعد) — راجع FilesRestoreService. هذا الأمر يعرض تحذيراً واضحاً عند
 * استعادة نسخة "كاملة" يوضّح أن ملفات storage/ لم تُستعد.
 */
class RestoreBackupCommand extends Command
{
    protected $signature = 'backup:restore {backup : ULID للنسخة الاحتياطية (من جدول backups)} {--force : تجاوز حماية بيئة production}';

    protected $description = 'استعادة قاعدة البيانات من نسخة احتياطية — CLI فقط، يتطلب تأكيداً صريحاً';

    public function handle(RestoreService $restoreService): int
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
        $this->warn('هذه العملية ستستبدل قاعدة البيانات الحالية بالكامل. (المرحلة الحالية: استعادة قاعدة البيانات فقط — ملفات storage لن تُمس حتى لو كانت هذه نسخة "كاملة").');
        $confirmationPhrase = 'RESTORE-'.$backup->id;
        $typed = $this->ask("اكتب \"{$confirmationPhrase}\" بالضبط للمتابعة");

        if ($typed !== $confirmationPhrase) {
            $this->error('نص التأكيد غير مطابق — تم إلغاء الاستعادة.');
            return self::FAILURE;
        }

        // ==================== 4. تنفيذ محرك الاستعادة — استدعاء واحد فقط ====================
        // يشمل داخلياً: تحقق checksum، فك تشفير، فتح ZIP، قراءة manifest،
        // استخراج آمن، نسخة احتياطية طارئة (تتوقف العملية كاملة إن فشلت)،
        // تفعيل Maintenance Mode، استيراد mysql فعلي، إلغاء Maintenance Mode
        // (مهما كانت النتيجة)، وتنظيف كامل لمجلد العمل المؤقت.
        $this->info('جارٍ تنفيذ الاستعادة عبر محرك الاستعادة (Restore Engine)...');

        try {
            $result = $restoreService->run($backup->id);
        } catch (BackupNotFoundException|BackupIntegrityException|BackupManifestException|BackupRestoreException $e) {
            $this->error('❌ فشلت الاستعادة: '.$e->getMessage());
            return self::FAILURE;
        }

        $this->info('✅ تمت استعادة قاعدة البيانات بنجاح (تشمل نسخة احتياطية طارئة قبل الاستيراد، وتفعيل/إلغاء Maintenance Mode تلقائياً).');

        if ($backup->type === BackupType::Full) {
            $this->warn('⚠️ هذه نسخة "كاملة" لكن استعادة ملفات storage/ لم تُنفَّذ بعد (مرحلة منفصلة لاحقة لم تبدأ) — تحقّق يدوياً إن كنت بحاجة لملفات storage.');
        }

        ActivityLog::record(
            eventType: 'backup.restored',
            metadata: [
                'backup_id'   => $backup->id,
                'restore_id'  => $result['restore_id'] ?? null,
                'restored_by' => getenv('USER') ?: getenv('USERNAME') ?: 'cli',
            ],
        );

        $this->newLine();
        $this->info('✅ اكتملت استعادة قاعدة البيانات. الخطوات التالية الموصى بها:');
        $this->line('  1) php artisan migrate --force   (في حال وجود migrations أحدث من تاريخ النسخة)');
        $this->line('  2) تحقّق يدوياً من عمل التطبيق قبل إعلامه للمستخدمين.');

        return self::SUCCESS;
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
                ['سيتم استبدال', 'قاعدة البيانات فقط (استعادة الملفات ليست جزءاً من هذه المرحلة)'],
            ])
            ->render();
        $this->info('==========================================================');
    }
}

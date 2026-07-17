<?php

namespace App\Console\Commands\Backup;

use App\Models\ActivityLog;
use App\Models\Backup;
use App\Services\Backup\BackupRetentionService;
use App\Support\Enums\BackupStatus;
use Illuminate\Console\Command;

/**
 * backup:apply-retention — يُجدوَل يومياً. يحذف النسخ الاحتياطية التي تجاوزت
 * سياسة الاحتفاظ المُعرَّفة في config('backups.system_backup.retention').
 *
 * ⚠️ يعالج أيضاً النسخ العالقة في status=running: إن تعطّل الخادم أو انقطعت
 * الكهرباء أثناء تنفيذ SystemBackupService، لا تصل الشيفرة أبداً لـ catch/
 * finally، فيبقى السجل "جارٍ التنفيذ" للأبد. أي نسخة running منذ أكثر من
 * ضعف job_timeout تُحوَّل هنا تلقائياً إلى failed برسالة واضحة. لا Job/
 * Scheduler جديد — هذا الأمر الموجود أصلاً (مجدوَل يومياً بعد backup:database
 * وbackup:full) هو من يقوم بذلك.
 */
class ApplyBackupRetentionCommand extends Command
{
    protected $signature = 'backup:apply-retention';

    protected $description = 'تطبيق سياسة الاحتفاظ بالنسخ الاحتياطية (حذف القديم وفق daily/weekly/monthly) + تحويل النسخ العالقة إلى فاشلة';

    public function handle(BackupRetentionService $service): int
    {
        $deleted = $service->apply();

        $this->info(count($deleted) > 0
            ? 'تم حذف '.count($deleted).' نسخة احتياطية قديمة وفق سياسة الاحتفاظ.'
            : 'لا توجد نسخ تستوجب الحذف حالياً.');

        $abandoned = $this->failAbandonedRunningBackups();

        $this->info(count($abandoned) > 0
            ? 'تم تحويل '.count($abandoned).' نسخة عالقة في "جارٍ التنفيذ" إلى "فشلت".'
            : 'لا توجد نسخ عالقة في "جارٍ التنفيذ".');

        ActivityLog::record(
            eventType: 'backup.retention_applied',
            metadata: [
                'deleted_count'   => count($deleted),
                'deleted_ids'     => $deleted,
                'abandoned_count' => count($abandoned),
                'abandoned_ids'   => $abandoned,
            ],
        );

        return self::SUCCESS;
    }

    /**
     * يحوّل أي نسخة عالقة في status=running منذ أطول من job_timeout×2 إلى
     * failed. راجع تعليق الفئة أعلاه.
     *
     * @return array<int,string> IDs النسخ التي حُوِّلت
     */
    private function failAbandonedRunningBackups(): array
    {
        $threshold = now()->subSeconds(
            (int) config('backups.system_backup.job_timeout', 1800) * 2
        );

        $abandoned = Backup::query()
            ->where('status', BackupStatus::Running->value)
            ->where('started_at', '<', $threshold)
            ->get();

        $ids = [];

        foreach ($abandoned as $backup) {
            /** @var Backup $backup */
            $backup->markFailed(
                'Backup appears to have been abandoned after exceeding the maximum execution window.'
            );

            $ids[] = $backup->id;
        }

        return $ids;
    }
}

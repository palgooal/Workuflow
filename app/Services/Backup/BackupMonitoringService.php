<?php

namespace App\Services\Backup;

use App\Models\Backup;
use App\Models\Setting;
use App\Support\Enums\BackupStatus;
use App\Support\Enums\BackupType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * BackupMonitoringService — المرحلة السادسة (الجزء الأول): عرض فقط. لا يضيف
 * أي منطق نسخ/جدولة جديد، ولا يُعدَّل بسببه أي شيء في SystemBackupService/
 * RestoreService/ScheduledBackupRunner/BackupObserver — يقرأ فقط سجلات
 * Backup الحالية وإعدادات backup_schedule الموجودة أصلاً (نفس مصدر
 * ScheduledBackupRunner وBackupScheduleSettings)، عبر استعلام Eloquent واحد
 * فقط لجدول backups (لا SQL خام)، وبقية الحسابات على الـCollection الناتجة
 * في الذاكرة — بلا أي استعلام إضافي مكرَّر.
 */
class BackupMonitoringService
{
    // نفس عامل ApplyBackupRetentionCommand::failAbandonedRunningBackups()
    // (job_timeout × 2) — تعريف محلي منفصل هنا لأن هذا الملف عرض فقط ولا
    // يستدعي/يُعدِّل ذلك الأمر، لكنه يتبنى نفس التعريف لـ"Running عالقة".
    private const STUCK_RUNNING_MULTIPLIER = 2;

    // أكثر من ضعف الدورة اليومية المتوقعة لنسخة قاعدة البيانات
    private const STALE_BACKUP_HOURS = 48;

    private const RECENT_FAILURE_HOURS = 24;

    /**
     * @return array{
     *     last_successful: ?Backup,
     *     last_failed: ?Backup,
     *     last_database_backup: ?Backup,
     *     last_full_backup: ?Backup,
     *     next_database_run: ?Carbon,
     *     next_full_run: ?Carbon,
     *     counts: array{completed:int,running:int,failed:int,database:int,full:int},
     *     total_size_bytes: int,
     *     total_size_human: string,
     *     health_status: string,
     * }
     */
    public function snapshot(): array
    {
        // استعلام واحد فقط — كل الحسابات أدناه تُشتَق من نفس الـCollection.
        $backups = Backup::query()->get();

        // "آخر نسخة" = الأحدث بوقت الاكتمال الفعلي (completed_at)، وليس وقت
        // إنشاء السجل (created_at) — ترتيب في الذاكرة فقط، بلا أي استعلام إضافي.
        // نقارن بـtimestamp رقمي (لا Carbon::minValue() — غير متوفرة في نسخة
        // Carbon الحالية للمشروع): السجلات بدون completed_at (لم تكتمل/تفشل
        // بعد) تُعامَل كأقدم قيمة ممكنة (0) فتبقى آخر الترتيب تلقائياً، وهذا
        // لا يؤثر على النتيجة أصلاً لأن كل الفلاتر أدناه تقتصر على
        // status=Completed/Failed، وكلتاهما تضمنان completed_at غير فارغة
        // فعلياً (Backup::markCompleted()/markFailed() تضبطانها دائماً).
        $byCompletedAtDesc = $backups->sortByDesc(
            fn (Backup $b) => $b->completed_at?->timestamp ?? 0
        );

        $lastSuccessful = $byCompletedAtDesc->first(fn (Backup $b) => $b->status === BackupStatus::Completed);
        $lastFailed     = $byCompletedAtDesc->first(fn (Backup $b) => $b->status === BackupStatus::Failed);

        $lastDatabaseBackup = $byCompletedAtDesc->first(
            fn (Backup $b) => $b->type === BackupType::Database && $b->status === BackupStatus::Completed
        );
        $lastFullBackup = $byCompletedAtDesc->first(
            fn (Backup $b) => $b->type === BackupType::Full && $b->status === BackupStatus::Completed
        );

        $counts = [
            'completed' => $backups->where('status', BackupStatus::Completed)->count(),
            'running'   => $backups->where('status', BackupStatus::Running)->count(),
            'failed'    => $backups->where('status', BackupStatus::Failed)->count(),
            'database'  => $backups->where('type', BackupType::Database)->count(),
            'full'      => $backups->where('type', BackupType::Full)->count(),
        ];

        $totalSizeBytes = (int) $backups->sum('size_bytes');

        $stuckRunning = $this->hasStuckRunningBackup($backups);
        $healthStatus = $this->computeHealthStatus($lastSuccessful, $lastFailed, $stuckRunning);

        [$nextDatabaseRun, $nextFullRun] = $this->nextScheduledRuns();

        return [
            'last_successful'      => $lastSuccessful,
            'last_failed'          => $lastFailed,
            'last_database_backup' => $lastDatabaseBackup,
            'last_full_backup'     => $lastFullBackup,
            'next_database_run'    => $nextDatabaseRun,
            'next_full_run'        => $nextFullRun,
            'counts'               => $counts,
            'total_size_bytes'     => $totalSizeBytes,
            'total_size_human'     => $this->humanSize($totalSizeBytes),
            'health_status'        => $healthStatus,
        ];
    }

    /**
     * "Running عالقة" — نفس تعريف job_timeout×2 المستخدَم فعلياً في
     * ApplyBackupRetentionCommand، لضمان اتساق تعريف "العالقة" في كل مكان
     * بالنظام دون تكرار الاستدعاء الفعلي لذلك الأمر (هذا الملف عرض فقط).
     */
    private function hasStuckRunningBackup(Collection $backups): bool
    {
        $threshold = now()->subSeconds(
            (int) config('backups.system_backup.job_timeout', 1800) * self::STUCK_RUNNING_MULTIPLIER
        );

        return $backups->contains(
            fn (Backup $b) => $b->status === BackupStatus::Running
                && $b->started_at !== null
                && $b->started_at->lt($threshold)
        );
    }

    private function computeHealthStatus(?Backup $lastSuccessful, ?Backup $lastFailed, bool $stuckRunning): string
    {
        // Critical: لا توجد أي نسخة ناجحة إطلاقاً، أو توجد Running عالقة.
        if ($lastSuccessful === null || $stuckRunning) {
            return 'critical';
        }

        $isStale = $lastSuccessful->completed_at !== null
            && $lastSuccessful->completed_at->lt(now()->subHours(self::STALE_BACKUP_HOURS));

        $hasRecentFailure = $lastFailed !== null
            && $lastFailed->completed_at !== null
            && $lastFailed->completed_at->gte(now()->subHours(self::RECENT_FAILURE_HOURS));

        // Warning: آخر نسخة ناجحة قديمة، أو يوجد فشل حديث.
        if ($isStale || $hasRecentFailure) {
            return 'warning';
        }

        return 'healthy';
    }

    /**
     * محسوبة برمجياً من إعدادات backup_schedule الحالية (نفس مصدر
     * ScheduledBackupRunner::isEnabled() وBackupScheduleSettings::scheduleStatus()) —
     * لا اعتماد على schedule:list إطلاقاً.
     *
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    private function nextScheduledRuns(): array
    {
        $settings = Setting::group('backup_schedule');

        $timezone = $settings['backup_timezone'] ?? config('app.timezone');
        $time     = $settings['backup_time'] ?? '02:00';

        $databaseEnabled = ($settings['database_backup_enabled'] ?? '1') === '1';
        $fullEnabled     = ($settings['full_backup_enabled'] ?? '1') === '1';

        $nextDatabaseRun = null;
        $nextFullRun     = null;

        try {
            [$hour, $minute] = array_pad(array_map('intval', explode(':', $time)), 2, 0);

            if ($databaseEnabled) {
                $nextDatabaseRun = now($timezone)->setTime($hour, $minute, 0);
                if ($nextDatabaseRun->isPast()) {
                    $nextDatabaseRun->addDay();
                }
            }

            if ($fullEnabled) {
                $nextFullRun = now($timezone)->setTime($hour, $minute, 0);
                $guard = 0;
                while ((! $nextFullRun->isFriday() || $nextFullRun->isPast()) && $guard < 8) {
                    $nextFullRun->addDay();
                    $guard++;
                }
            }
        } catch (\Throwable) {
            // إعداد وقت/منطقة زمنية غير صالح مؤقتاً (مثلاً أثناء التعديل من
            // BackupScheduleSettings) — لا نكسر اللوحة، فقط نعيد null.
            $nextDatabaseRun = null;
            $nextFullRun     = null;
        }

        return [$nextDatabaseRun, $nextFullRun];
    }

    private function humanSize(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size  = (float) $bytes;
        $i     = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 1).' '.$units[$i];
    }
}

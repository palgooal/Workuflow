<?php

namespace App\Console\Commands\Backup;

use App\Models\ActivityLog;
use App\Services\Backup\BackupRetentionService;
use Illuminate\Console\Command;

/**
 * backup:apply-retention — يُجدوَل يومياً. يحذف النسخ الاحتياطية التي تجاوزت
 * سياسة الاحتفاظ المُعرَّفة في config('backups.system_backup.retention').
 */
class ApplyBackupRetentionCommand extends Command
{
    protected $signature = 'backup:apply-retention';

    protected $description = 'تطبيق سياسة الاحتفاظ بالنسخ الاحتياطية (حذف القديم وفق daily/weekly/monthly)';

    public function handle(BackupRetentionService $service): int
    {
        $deleted = $service->apply();

        $this->info(count($deleted) > 0
            ? 'تم حذف '.count($deleted).' نسخة احتياطية قديمة وفق سياسة الاحتفاظ.'
            : 'لا توجد نسخ تستوجب الحذف حالياً.');

        ActivityLog::record(
            eventType: 'backup.retention_applied',
            metadata: ['deleted_count' => count($deleted), 'deleted_ids' => $deleted],
        );

        return self::SUCCESS;
    }
}

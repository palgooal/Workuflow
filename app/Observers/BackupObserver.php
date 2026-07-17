<?php

namespace App\Observers;

use App\Models\Backup;
use App\Support\Enums\BackupStatus;
use App\Support\Enums\BackupTrigger;
use Illuminate\Support\Facades\Log;

/**
 * BackupObserver — يُسجّل "Scheduled backup completed"/"Scheduled backup
 * failed" عندما ينتقل سجل Backup (triggered_by=scheduled) فعلياً إلى
 * completed/failed، بمراقبة تغيّر status فقط — بدون أي تعديل على
 * SystemBackupService أو RunSystemBackupJob (اللذين يستدعيان
 * markCompleted()/markFailed() الحاليتين دون تغيير).
 *
 * مسجَّل في AppServiceProvider::boot() عبر Backup::observe(BackupObserver::class).
 */
class BackupObserver
{
    public function updated(Backup $backup): void
    {
        if ($backup->triggered_by !== BackupTrigger::Scheduled) {
            return;
        }

        if (! $backup->wasChanged('status')) {
            return;
        }

        if ($backup->status === BackupStatus::Completed) {
            Log::info('Scheduled backup completed', [
                'backup_id' => $backup->id,
                'type'      => $backup->type->value,
            ]);
        } elseif ($backup->status === BackupStatus::Failed) {
            Log::error('Scheduled backup failed', [
                'backup_id' => $backup->id,
                'type'      => $backup->type->value,
                'error'     => $backup->error_message,
            ]);
        }
    }
}

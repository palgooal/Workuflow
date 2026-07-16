<?php

namespace App\Jobs\Backup;

use App\Models\Backup;
use App\Services\Backup\SystemBackupService;
use App\Support\Enums\BackupStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * RunSystemBackupJob — ينفّذ نسخة احتياطية تشغيلية (database أو full) بشكل
 * غير متزامن، يُطلَق من:
 *  - Filament (زر "إنشاء نسخة يدوية")
 *  - أوامر backup:database / backup:full المجدولة
 *
 * Connection vs Queue name — لتفادي أي التباس:
 *  - Connection: يستخدم القناة الافتراضية للتطبيق (QUEUE_CONNECTION=database
 *    في هذه المرحلة، راجع config/queue.php) — مضبوطة صراحةً في constructor
 *    عبر onConnection() لعدم الاعتماد على الإعداد الافتراضي فقط.
 *  - Queue name: مضبوطة صراحةً على "backups" عبر onQueue() (وليس "database"
 *    — ذاك اسم القناة لا الطابور) حتى يمكن تشغيل worker مخصّص لها، بمهلة أطول
 *    ومعزول عن باقي طوابير التطبيق: `php artisan queue:work database --queue=backups`.
 *    ⚠️ لا تُعرَّف خاصية `$queue`/`$connection` كـ property هنا — trait
 *    Illuminate\Bus\Queueable تُعرّفهما بالفعل، والتعريف المزدوج يسبّب:
 *    "Fatal error: ... and Illuminate\Bus\Queueable define the same property".
 *    استخدم onConnection()/onQueue() داخل الـ constructor فقط.
 */
class RunSystemBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $backoff = 300;
    public int $timeout;

    public function __construct(
        private readonly string $backupId,
    ) {
        $this->onConnection('database');
        $this->onQueue('backups');

        // مهلة الـ Job = مهلة تنفيذ النسخ الاحتياطي المُعرَّفة في config + هامش أمان
        $this->timeout = (int) config('backups.system_backup.job_timeout', 1800) + 60;
    }

    public function retryUntil(): \DateTime
    {
        return now()->addSeconds((int) config('backups.system_backup.job_timeout', 1800) + 300);
    }

    public function handle(SystemBackupService $service): void
    {
        /** @var Backup|null $backup */
        $backup = Backup::query()->find($this->backupId);

        if (! $backup) {
            Log::warning("RunSystemBackupJob: نسخة {$this->backupId} غير موجودة");
            return;
        }

        // Idempotency: لا تُعاد نسخة مكتملة بالفعل
        if ($backup->status === BackupStatus::Completed) {
            Log::info("RunSystemBackupJob: النسخة {$this->backupId} مكتملة بالفعل — تخطّي");
            return;
        }

        $service->run($backup);
    }

    public function failed(Throwable $exception): void
    {
        $backup = Backup::query()->find($this->backupId);
        if ($backup && $backup->status !== BackupStatus::Completed) {
            $backup->markFailed($exception->getMessage());
        }
    }
}

<?php

namespace App\Jobs\Export;

use App\Models\ActivityLog;
use App\Models\DataExportRequest;
use App\Notifications\DataExportFailedNotification;
use App\Notifications\DataExportReadyNotification;
use App\Services\DataExport\UserDataExportService;
use App\Support\Enums\DataExportStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * ExportUserDataJob — يبني نسخة بيانات مستخدم واحد بشكل غير متزامن.
 *
 * ⚠️ عمداً لا يُنفَّذ داخل HTTP request — يُطلَق من DataExportController@store
 * عبر dispatch() فقط. راجع docs/DATA-EXPORT.md.
 *
 * Connection vs Queue name — لتفادي أي التباس:
 *  - Connection: يستخدم القناة الافتراضية للتطبيق (QUEUE_CONNECTION=database
 *    في هذه المرحلة، راجع config/queue.php) — مضبوطة صراحةً في constructor
 *    عبر onConnection() لعدم الاعتماد على الإعداد الافتراضي فقط.
 *  - Queue name: مضبوطة صراحةً على "exports" عبر onQueue() (وليس "database"
 *    — ذاك اسم القناة لا الطابور) حتى يمكن تشغيل worker مخصّص لها بمعزل عن
 *    باقي طوابير التطبيق: `php artisan queue:work database --queue=exports`.
 *    ⚠️ لا تُعرَّف خاصية `$queue`/`$connection` كـ property هنا — trait
 *    Illuminate\Bus\Queueable تُعرّفهما بالفعل، والتعريف المزدوج يسبّب:
 *    "Fatal error: ... and Illuminate\Bus\Queueable define the same property".
 *    استخدم onConnection()/onQueue() داخل الـ constructor فقط.
 */
class ExportUserDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 900; // 15 دقيقة — يكفي لحسابات كبيرة نسبياً
    public int $backoff = 120;

    public function __construct(
        private readonly string $dataExportRequestId,
    ) {
        $this->onConnection('database');
        $this->onQueue('exports');
    }

    public function handle(UserDataExportService $service): void
    {
        /** @var DataExportRequest|null $exportRequest */
        $exportRequest = DataExportRequest::query()->find($this->dataExportRequestId);

        if (! $exportRequest) {
            Log::warning("ExportUserDataJob: طلب {$this->dataExportRequestId} غير موجود");
            return;
        }

        // Idempotency: لا تُعاد معالجة طلب مكتمل بالفعل
        if ($exportRequest->status->isFinished()) {
            Log::info("ExportUserDataJob: الطلب {$this->dataExportRequestId} منتهٍ بالفعل — تخطّي");
            return;
        }

        $exportRequest->update(['status' => DataExportStatus::Processing]);

        try {
            $user = $exportRequest->user()->withoutGlobalScopes()->first();
            if (! $user) {
                throw new \RuntimeException('المستخدم المرتبط بالطلب غير موجود.');
            }

            $storagePath = $service->build($user);
            $disk        = config('backups.user_export.disk');
            $fileSize    = Storage::disk($disk)->size($storagePath);

            $exportRequest->update([
                'status'       => DataExportStatus::Completed,
                'file_path'    => $storagePath,
                'file_size'    => $fileSize,
                'completed_at' => now(),
                'expires_at'   => now()->addHours((int) config('backups.user_export.retention_hours', 72)),
            ]);

            ActivityLog::record(
                eventType: 'export.completed',
                userId: $user->id,
                entityType: DataExportRequest::class,
                entityId: $exportRequest->id,
                metadata: ['file_size' => $fileSize],
            );

            $user->notify(new DataExportReadyNotification($exportRequest));

            Log::info("ExportUserDataJob: اكتمل التصدير للمستخدم {$user->id}");
        } catch (Throwable $e) {
            $this->failExport($exportRequest, $e->getMessage());
            throw $e; // إعادة الرمي لتسجيل failed_jobs والسماح بإعادة المحاولة
        }
    }

    public function failed(Throwable $exception): void
    {
        $exportRequest = DataExportRequest::query()->find($this->dataExportRequestId);
        if ($exportRequest && $exportRequest->status->isActive()) {
            $this->failExport($exportRequest, $exception->getMessage());
        }
    }

    private function failExport(DataExportRequest $exportRequest, string $reason): void
    {
        $exportRequest->update([
            'status'         => DataExportStatus::Failed,
            'failure_reason' => $reason,
            'completed_at'   => now(),
        ]);

        ActivityLog::record(
            eventType: 'export.failed',
            userId: $exportRequest->user_id,
            entityType: DataExportRequest::class,
            entityId: $exportRequest->id,
            metadata: ['reason' => $reason],
        );

        Log::error("ExportUserDataJob: فشل الطلب {$exportRequest->id} — {$reason}");

        $user = $exportRequest->user()->withoutGlobalScopes()->first();
        $user?->notify(new DataExportFailedNotification($exportRequest));
    }
}

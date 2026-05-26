<?php

namespace App\Modules\CRM\Jobs;

use App\Modules\CRM\Enums\ImportStatus;
use App\Modules\CRM\Imports\ClientsImport;
use App\Modules\CRM\Models\ClientImportLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

/**
 * ImportClientsJob — استيراد العملاء بشكل غير متزامن
 *
 * Sprint 4 — S4.3
 * - يُشغَّل على queue: 'imports'
 * - يعالج الملف بـ chunks (chunkSize = 1000)
 * - يُحدِّث ClientImportLog بالتقدم الحي
 * - Idempotent: لن يُعاد تشغيله إذا كان الـ log مكتملاً
 * - يُطلق ClientImportCompleted بعد الانتهاء
 */
class ImportClientsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * أقصى عدد محاولات إعادة التشغيل عند الفشل
     */
    public int $tries = 2;

    /**
     * مهلة التنفيذ بالثواني (10 دقائق للملفات الكبيرة)
     */
    public int $timeout = 600;

    /**
     * مهلة قبل إعادة المحاولة (دقيقتان)
     */
    public int $backoff = 120;

    public function __construct(
        private readonly string $importLogId,
        private readonly bool   $skipDuplicates = true,
        private readonly bool   $updateExisting = false,
        private readonly array  $columnMap      = [],
    ) {}

    // ==================== Handle ====================

    public function handle(): void
    {
        /** @var ClientImportLog $log */
        $log = ClientImportLog::find($this->importLogId);

        if (!$log) {
            Log::warning("ImportClientsJob: log {$this->importLogId} not found");
            return;
        }

        // Idempotency: لا تُعيد المعالجة إذا اكتملت
        if ($log->isFinished()) {
            Log::info("ImportClientsJob: log {$this->importLogId} already finished — skipping");
            return;
        }

        // تحقق من وجود الملف
        if (!Storage::disk('local')->exists($log->filename)) {
            $this->failLog($log, "الملف غير موجود: {$log->filename}");
            return;
        }

        // تحديث الحالة إلى "جارٍ المعالجة"
        $log->update([
            'status'     => ImportStatus::Processing,
            'total_rows' => 0,
        ]);

        try {
            $import = new ClientsImport(
                userId:         $log->user_id,
                log:            $log,
                skipDuplicates: $this->skipDuplicates,
                updateExisting: $this->updateExisting,
                columnMap:      $this->columnMap,
            );

            // تشغيل الاستيراد — يُعالج على chunks تلقائياً
            $filePath = Storage::disk('local')->path($log->filename);
            Excel::import($import, $filePath);

            // تحديد الحالة النهائية
            $finalStatus = $import->getErrorCount() === 0
                ? ImportStatus::Completed
                : ImportStatus::Partial;

            $log->update([
                'status'        => $finalStatus,
                'total_rows'    => $import->getSuccessCount()
                                 + $import->getErrorCount()
                                 + $import->getSkippedCount(),
                'success_count' => $import->getSuccessCount(),
                'error_count'   => $import->getErrorCount(),
                'skipped_count' => $import->getSkippedCount(),
                'errors'        => $import->getErrors(),
                'completed_at'  => now(),
            ]);

            Log::info("ImportClientsJob: completed — user={$log->user_id} success={$import->getSuccessCount()} errors={$import->getErrorCount()}");

        } catch (Throwable $e) {
            $this->failLog($log, $e->getMessage());
            throw $e; // إعادة الرمي لتسجيل failed_jobs
        } finally {
            // حذف الملف المؤقت بعد الانتهاء
            $this->cleanupTempFile($log);
        }
    }

    // ==================== Failure ====================

    /**
     * يُستدعى عند استنفاد جميع المحاولات
     */
    public function failed(Throwable $exception): void
    {
        $log = ClientImportLog::find($this->importLogId);
        if ($log && !$log->isFinished()) {
            $this->failLog($log, $exception->getMessage());
        }
    }

    // ==================== Helpers ====================

    private function failLog(ClientImportLog $log, string $message): void
    {
        $log->update([
            'status'       => ImportStatus::Failed,
            'errors'       => [['row' => 0, 'errors' => [$message]]],
            'completed_at' => now(),
        ]);

        Log::error("ImportClientsJob: failed — {$message}", [
            'import_log_id' => $log->id,
            'user_id'       => $log->user_id,
        ]);
    }

    private function cleanupTempFile(ClientImportLog $log): void
    {
        try {
            if (str_starts_with($log->filename, 'imports/tmp/')) {
                Storage::disk('local')->delete($log->filename);
            }
        } catch (Throwable $e) {
            Log::warning("ImportClientsJob: failed to delete temp file {$log->filename}: {$e->getMessage()}");
        }
    }
}

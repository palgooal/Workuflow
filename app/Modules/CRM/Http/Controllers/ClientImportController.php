<?php

namespace App\Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Modules\CRM\DTOs\ImportClientsDTO;
use App\Modules\CRM\Enums\ImportStatus;
use App\Modules\CRM\Exports\ClientsImportTemplate;
use App\Modules\CRM\Jobs\ImportClientsJob;
use App\Modules\CRM\Models\ClientImportLog;
use App\Modules\CRM\Requests\ImportClientRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * ClientImportController — Sprint 4 S4.2 / S4.3
 *
 * GET  /clients/import/template  — تنزيل قالب CSV
 * POST /clients/import           — رفع ملف + dispatch ImportClientsJob
 * GET  /clients/import/{log}     — حالة عملية استيراد (polling)
 * GET  /clients/import/history   — آخر 20 عملية
 */
class ClientImportController extends Controller
{
    /**
     * تحميل قالب Excel (.xlsx) للاستيراد
     */
    public function template(Request $request): BinaryFileResponse
    {
        $this->authorize('importClients', Client::class);

        return Excel::download(
            new ClientsImportTemplate(),
            'clients-import-template.xlsx',
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    /**
     * رفع ملف الاستيراد + dispatch Job + Idempotency
     */
    public function store(ImportClientRequest $request): JsonResponse
    {
        $this->authorize('importClients', Client::class);

        $dto = ImportClientsDTO::fromRequest($request);

        // Idempotency: لا تُعيد المعالجة لنفس الملف مرتين
        $existing = ClientImportLog::where('idempotency_key', $dto->idempotencyKey)
                                   ->where('user_id', $dto->userId)
                                   ->where('status', '!=', ImportStatus::Failed->value)
                                   ->first();

        if ($existing) {
            return response()->json([
                'data'    => $this->formatLog($existing),
                'message' => 'تم استلام هذا الملف مسبقاً.',
            ], 200);
        }

        // إنشاء سجل جديد
        $log = ClientImportLog::create([
            'user_id'         => $dto->userId,
            'filename'        => $dto->filePath,
            'idempotency_key' => $dto->idempotencyKey,
            'status'          => ImportStatus::Pending,
            'total_rows'      => 0,
            'success_count'   => 0,
            'error_count'     => 0,
            'skipped_count'   => 0,
        ]);

        // Dispatch Job على queue 'imports'
        ImportClientsJob::dispatch(
            importLogId:    $log->id,
            skipDuplicates: $dto->skipDuplicates,
            updateExisting: $dto->updateExisting,
            columnMap:      $dto->columnMap,
        )->onQueue('imports');

        return response()->json([
            'data'    => $this->formatLog($log),
            'message' => 'جارٍ معالجة الملف، يمكنك متابعة التقدم.',
        ], 202);
    }

    /**
     * حالة عملية استيراد محددة (polling-friendly)
     */
    public function show(Request $request, string $logId): JsonResponse
    {
        $this->authorize('importClients', Client::class);

        $log = ClientImportLog::where('id', $logId)
                              ->where('user_id', $request->user()->id)
                              ->firstOrFail();

        return response()->json([
            'data'        => $this->formatLog($log),
            'is_finished' => $log->isFinished(),
        ]);
    }

    /**
     * آخر 20 عملية استيراد
     */
    public function history(Request $request): JsonResponse
    {
        $this->authorize('importClients', Client::class);

        $logs = ClientImportLog::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn ($log) => $this->formatLog($log));

        return response()->json(['data' => $logs]);
    }

    // ==================== Helper ====================

    private function formatLog(ClientImportLog $log): array
    {
        return [
            'id'            => $log->id,
            'status'        => $log->status instanceof ImportStatus ? $log->status->value : $log->status,
            'status_label'  => $log->status instanceof ImportStatus ? $log->status->label() : '',
            'badge_class'   => $log->status instanceof ImportStatus ? $log->status->badgeClass() : '',
            'total_rows'    => $log->total_rows,
            'success_count' => $log->success_count,
            'error_count'   => $log->error_count,
            'skipped_count' => $log->skipped_count,
            'success_rate'  => $log->successRate(),
            'errors'        => $log->errors ?? [],
            'summary'       => $log->summary(),
            'completed_at'  => $log->completed_at?->format('Y/m/d H:i'),
            'created_at'    => $log->created_at->format('Y/m/d H:i'),
        ];
    }
}

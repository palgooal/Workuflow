<?php

namespace App\Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Modules\CRM\Builders\ClientQueryBuilder;
use App\Modules\CRM\DTOs\ClientFiltersDTO;
use App\Modules\CRM\Exports\ClientsExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * ClientExportController — Sprint 4 S4.1 / S4.4
 *
 * GET  /clients/export            — تصدير فوري CSV (مجاني) أو Excel
 * POST /clients/export/schedule   — تصدير async (قيد التطوير)
 */
class ClientExportController extends Controller
{
    /**
     * تصدير فوري
     * ?format=csv  → CSV  (sync, BOM-encoded, كل الخطط)
     * ?format=xlsx → Excel (sync, styled, Pro+)
     */
    public function download(Request $request): Response|BinaryFileResponse
    {
        $this->authorize('exportClients', Client::class);

        $request->validate([
            'format' => ['nullable', 'in:csv,xlsx'],
        ]);

        $format  = $request->input('format', 'csv');
        $filters = ClientFiltersDTO::fromRequest($request);

        $filename = 'clients-export-' . now()->format('Y-m-d');

        if ($format === 'xlsx') {
            // Excel مع تنسيق احترافي
            return Excel::download(
                new ClientsExport($request->user()->id, $filters),
                $filename . '.xlsx',
                \Maatwebsite\Excel\Excel::XLSX
            );
        }

        // CSV: خفيف وسريع (كل الخطط)
        return $this->downloadCsv($request->user()->id, $filters, $filename . '.csv');
    }

    /**
     * جدولة تصدير كبير (async) — TODO: تفعيل في Sprint لاحق
     */
    public function scheduleExport(Request $request): JsonResponse
    {
        $this->authorize('exportClients', Client::class);

        $request->validate([
            'format'  => ['required', 'in:csv,xlsx'],
            'filters' => ['sometimes', 'array'],
        ]);

        // TODO: dispatch ExportClientsJob + نتيجة بـ notification
        return response()->json([
            'message' => 'سيتم تجهيز ملف التصدير وإرساله إلى بريدك الإلكتروني قريباً.',
        ], 202);
    }

    // ==================== CSV Helper ====================

    private function downloadCsv(int $userId, ClientFiltersDTO $filters, string $filename): Response
    {
        $clients = (new ClientQueryBuilder($userId))
            ->applyFilters($filters)
            ->toExportQuery()
            ->select([
                'clients.name',
                'clients.email',
                'clients.phone',
                'clients.company',
                'clients.position',
                'clients.status',
                'clients.source',
                'clients.city',
                'clients.country',
                'clients.total_revenue',
                'clients.health_score',
                'clients.created_at',
            ])
            ->limit(ClientsExport::MAX_SYNC_ROWS)
            ->get();

        $headers = [
            'الاسم', 'البريد الإلكتروني', 'الهاتف', 'الشركة', 'المسمى الوظيفي',
            'الحالة', 'المصدر', 'المدينة', 'الدولة',
            'إجمالي الإيراد', 'نقاط الصحة', 'تاريخ الإضافة',
        ];

        $csv = "\xEF\xBB\xBF" . implode(',', $headers) . "\n";

        foreach ($clients as $client) {
            $row = [
                $this->cell($client->name),
                $this->cell($client->email ?? ''),
                $this->cell($client->phone ?? ''),
                $this->cell($client->company ?? ''),
                $this->cell($client->position ?? ''),
                $this->cell((string)$client->status),
                $this->cell((string)$client->source),
                $this->cell($client->city ?? ''),
                $this->cell($client->country ?? ''),
                number_format((float)($client->total_revenue ?? 0), 2),
                $client->health_score ?? '',
                $client->created_at ?? '',
            ];
            $csv .= implode(',', $row) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function cell(string $value): string
    {
        if (str_contains($value, ',') || str_contains($value, '"') || str_contains($value, "\n")) {
            return '"' . str_replace('"', '""', $value) . '"';
        }
        return $value;
    }
}

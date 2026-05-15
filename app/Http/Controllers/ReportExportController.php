<?php

namespace App\Http\Controllers;

use App\Exports\TransactionsExport;
use App\Models\Transaction;
use App\Modules\Reports\Services\ReportService;
use App\Support\Enums\SubscriptionPlan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportExportController extends Controller
{
    public function __construct(
        private readonly ReportService $service
    ) {}

    // ─── PDF ──────────────────────────────────────────────────
    public function pdf(Request $request): Response
    {
        $this->checkExportAccess();

        ['from' => $from, 'to' => $to] = $this->getDateRange($request);

        $transactions = Transaction::with(['project', 'category'])
            ->dateBetween($from, $to)
            ->orderBy('transaction_date', 'desc')
            ->get();

        $summary = $this->service->getSummary($from, $to);

        $pdf = Pdf::loadView('reports.exports.pdf', compact(
            'transactions', 'summary', 'from', 'to'
        ))
        ->setPaper('a4', 'portrait')
        ->setOption('defaultFont', 'DejaVu Sans')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', false)
        ->setOption('chroot', public_path());

        $filename = 'workuflow-report-' . $from . '-to-' . $to . '.pdf';

        return $pdf->download($filename);
    }

    // ─── Excel ────────────────────────────────────────────────
    public function excel(Request $request): BinaryFileResponse
    {
        $this->checkExportAccess();

        ['from' => $from, 'to' => $to] = $this->getDateRange($request);

        $filename = 'workuflow-transactions-' . $from . '-to-' . $to . '.xlsx';

        return Excel::download(
            new TransactionsExport($from, $to),
            $filename
        );
    }

    // ─── Helpers ──────────────────────────────────────────────

    private function checkExportAccess(): void
    {
        $user = auth()->user();

        if (! $user->currentPlan()->canExport()) {
            abort(403, 'تصدير التقارير متاح لمشتركي Pro وBusiness فقط. يرجى ترقية خطتك.');
        }
    }

    private function getDateRange(Request $request): array
    {
        $from = $request->input('from', now()->startOfYear()->toDateString());
        $to   = $request->input('to',   now()->endOfMonth()->toDateString());

        // تأكد من أن from <= to
        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        return compact('from', 'to');
    }
}

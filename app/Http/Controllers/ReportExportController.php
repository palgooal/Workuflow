<?php

namespace App\Http\Controllers;

use App\Exports\TransactionsExport;
use App\Models\Transaction;
use App\Modules\Reports\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
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

        // ─── إعداد mPDF مع دعم العربية ────────────────
        $defaultConfig   = (new ConfigVariables())->getDefaults();
        $fontDirectories = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData          = $defaultFontConfig['fontdata'];

        $mpdf = new Mpdf([
            'mode'              => 'utf-8',
            'format'            => 'A4',
            'orientation'       => 'P',
            'margin_top'        => 10,
            'margin_bottom'     => 10,
            'margin_left'       => 10,
            'margin_right'      => 10,
            'fontDir'           => array_merge($fontDirectories, [
                base_path('resources/fonts'),
            ]),
            'fontdata'          => $fontData,
            'default_font'      => 'dejavusans',
            'autoScriptToLang'  => true,
            'autoLangToFont'    => true,
            'direction'         => 'rtl',
            'allow_charset_conversion' => true,
        ]);

        $mpdf->SetDirectionality('rtl');

        $html = view('reports.exports.pdf', compact(
            'transactions', 'summary', 'from', 'to'
        ))->render();

        $mpdf->WriteHTML($html);

        $filename = 'workuflow-report-' . $from . '-to-' . $to . '.pdf';

        return response(
            $mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
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
        if (! auth()->user()->currentPlan()->canExport()) {
            abort(403, 'تصدير التقارير متاح لمشتركي Pro وBusiness فقط. يرجى ترقية خطتك.');
        }
    }

    private function getDateRange(Request $request): array
    {
        $from = $request->input('from', now()->startOfYear()->toDateString());
        $to   = $request->input('to',   now()->endOfMonth()->toDateString());

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        return compact('from', 'to');
    }
}

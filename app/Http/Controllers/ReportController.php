<?php

namespace App\Http\Controllers;

use App\Modules\Reports\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $service,
    ) {}

    public function index(Request $request): View
    {
        // افتراضي: السنة الحالية
        $year    = $request->integer('year', now()->year);
        $from    = $request->input('from', now()->startOfYear()->toDateString());
        $to      = $request->input('to',   now()->endOfMonth()->toDateString());
        $catType = $request->input('cat_type', 'expense');

        // التأكد من أن from <= to
        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        $summary         = $this->service->getSummary($from, $to);
        $trend           = $this->service->getMonthlyTrend($from, $to);
        $categories      = $this->service->getCategoryBreakdown($from, $to, $catType);
        $projects        = $this->service->getProjectProfitability($from, $to);
        $bestWorst       = $this->service->getBestAndWorstMonths($trend);
        $serviceMargins  = $this->service->getServiceProfitability();
        $teamEfficiency  = $this->service->getTeamMemberEfficiency();

        // سنوات متاحة للفلتر (من أول معاملة حتى اليوم)
        $firstYear = \App\Models\Transaction::min('transaction_date')
            ? (int) substr(\App\Models\Transaction::min('transaction_date'), 0, 4)
            : now()->year;
        $years = range($firstYear, now()->year);

        return view('reports.index', compact(
            'summary', 'trend', 'categories', 'projects', 'bestWorst',
            'serviceMargins', 'teamEfficiency',
            'from', 'to', 'catType', 'years', 'year'
        ));
    }
}

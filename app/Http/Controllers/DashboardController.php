<?php

namespace App\Http\Controllers;

use App\Modules\Dashboard\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    public function index(): View
    {
        $data = $this->dashboardService->getData(auth()->id());

        return view('dashboard', [
            'kpis'      => $data['kpis'],
            'chart'     => $data['chart'],
            'recent'    => $data['recent'],
            'projects'  => $data['projects'],
            'debtsDue'  => $data['debts_due'],
        ]);
    }
}

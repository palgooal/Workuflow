<?php

namespace App\Http\Controllers;

use App\Modules\Dashboard\Services\DashboardService;
use App\Services\OnboardingService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService  $dashboardService,
        private readonly OnboardingService $onboardingService,
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $data = $this->dashboardService->getData($user->id);

        // Onboarding
        $showOnboarding   = $this->onboardingService->shouldShow($user);
        $onboardingSteps  = $showOnboarding ? $this->onboardingService->getSteps($user)    : [];
        $onboardingProgress = $showOnboarding ? $this->onboardingService->getProgressPercentage($user) : 0;
        $onboardingCompleted = $showOnboarding ? $this->onboardingService->getCompletedCount($user)    : 0;
        $onboardingTotal     = $this->onboardingService->getTotalCount();

        return view('dashboard', [
            'kpis'                => $data['kpis'],
            'chart'               => $data['chart'],
            'recent'              => $data['recent'],
            'projects'            => $data['projects'],
            'debtsDue'            => $data['debts_due'],
            'showOnboarding'      => $showOnboarding,
            'onboardingSteps'     => $onboardingSteps,
            'onboardingProgress'  => $onboardingProgress,
            'onboardingCompleted' => $onboardingCompleted,
            'onboardingTotal'     => $onboardingTotal,
        ]);
    }
}

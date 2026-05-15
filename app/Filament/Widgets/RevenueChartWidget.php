<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Support\Enums\SubscriptionPlan;
use Filament\Widgets\ChartWidget;

class RevenueChartWidget extends ChartWidget
{
    protected static ?string $heading = 'توزيع خطط الاشتراك';
    protected static ?int    $sort    = 4;

    protected function getData(): array
    {
        $free     = User::where('subscription_plan', SubscriptionPlan::Free)->count();
        $pro      = User::where('subscription_plan', SubscriptionPlan::Pro)->count();
        $business = User::where('subscription_plan', SubscriptionPlan::Business)->count();

        return [
            'datasets' => [
                [
                    'label'           => 'المستخدمون',
                    'data'            => [$free, $pro, $business],
                    'backgroundColor' => [
                        'rgba(107, 114, 128, 0.8)',   // gray  — Free
                        'rgba(99, 102, 241, 0.8)',    // indigo — Pro
                        'rgba(16, 185, 129, 0.8)',    // green  — Business
                    ],
                    'borderColor'     => [
                        'rgb(107, 114, 128)',
                        'rgb(99, 102, 241)',
                        'rgb(16, 185, 129)',
                    ],
                    'borderWidth' => 2,
                    'hoverOffset' => 6,
                ],
            ],
            'labels' => [
                "مجاني ({$free})",
                "Pro ({$pro})",
                "Business ({$business})",
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels'   => [
                        'padding'  => 20,
                        'boxWidth' => 14,
                    ],
                ],
            ],
            'cutout' => '65%',
        ];
    }
}

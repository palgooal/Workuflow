<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Support\Enums\SubscriptionPlan;
use Filament\Widgets\ChartWidget;

class MrrTrendWidget extends ChartWidget
{
    protected static ?string $heading = 'نمو الإيرادات الشهرية (MRR) — آخر 12 شهراً';
    protected static ?int    $sort    = 5;

    private const PRICE_PRO      = 99;
    private const PRICE_BUSINESS = 299;

    protected function getData(): array
    {
        $mrrData  = [];
        $labels   = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);

            // عدد مستخدمي Pro و Business الذين اشتركوا قبل نهاية هذا الشهر
            $pro = User::where('subscription_plan', SubscriptionPlan::Pro)
                ->where('created_at', '<=', $date->copy()->endOfMonth())
                ->count();

            $biz = User::where('subscription_plan', SubscriptionPlan::Business)
                ->where('created_at', '<=', $date->copy()->endOfMonth())
                ->count();

            $mrrData[] = ($pro * self::PRICE_PRO) + ($biz * self::PRICE_BUSINESS);
            $labels[]  = $date->translatedFormat('M Y');
        }

        return [
            'datasets' => [
                [
                    'label'           => 'MRR (SAR)',
                    'data'            => $mrrData,
                    'fill'            => true,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderColor'     => 'rgb(16, 185, 129)',
                    'tension'         => 0.4,
                    'pointBackgroundColor' => 'rgb(16, 185, 129)',
                    'pointRadius'     => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks'       => [
                        'callback' => 'function(value) { return value.toLocaleString() + " SAR"; }',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => ['display' => false],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return context.parsed.y.toLocaleString() + " SAR"; }',
                    ],
                ],
            ],
        ];
    }
}

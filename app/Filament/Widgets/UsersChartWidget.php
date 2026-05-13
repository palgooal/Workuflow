<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class UsersChartWidget extends ChartWidget
{
    protected static ?string $heading = 'تسجيلات المستخدمين — آخر 12 شهراً';
    protected static ?int    $sort    = 2;

    protected function getData(): array
    {
        $data   = [];
        $labels = [];

        for ($i = 11; $i >= 0; $i--) {
            $date     = now()->subMonths($i);
            $labels[] = $date->translatedFormat('M Y');
            $data[]   = User::whereYear('created_at', $date->year)
                            ->whereMonth('created_at', $date->month)
                            ->count();
        }

        return [
            'datasets' => [
                [
                    'label'           => 'مستخدمون جدد',
                    'data'            => $data,
                    'fill'            => true,
                    'backgroundColor' => 'rgba(99, 102, 241, 0.15)',
                    'borderColor'     => 'rgb(99, 102, 241)',
                    'tension'         => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}

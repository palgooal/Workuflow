<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\MrrTrendWidget;
use App\Filament\Widgets\RevenueChartWidget;
use App\Filament\Widgets\RevenueStatsWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\SystemHealthWidget;
use App\Filament\Widgets\UsersChartWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors(['primary' => Color::Indigo])
            ->brandName('Workuflow Admin')
            ->favicon(asset('favicon.ico'))

            // Auto-discover Resources, Pages, Widgets
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')

            ->pages([Pages\Dashboard::class])
            ->widgets([
                Widgets\AccountWidget::class,
                StatsOverviewWidget::class,     // sort: 1 — إحصائيات عامة
                UsersChartWidget::class,         // sort: 2 — نمو المستخدمين
                RevenueStatsWidget::class,       // sort: 3 — MRR / ARR / Churn
                RevenueChartWidget::class,       // sort: 4 — Donut توزيع الخطط
                MrrTrendWidget::class,           // sort: 5 — خط نمو الإيرادات
                SystemHealthWidget::class,       // sort: 6 — صحة النظام
            ])

            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class])

            // Navigation groups
            ->navigationGroups([
                'إدارة المستخدمين',
                'البيانات المالية',
                'النظام',
            ]);
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\Subscription;
use App\Models\User;
use App\Support\Enums\SubscriptionPlan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RevenueStatsWidget extends BaseWidget
{
    protected static ?int  $sort    = 3;
    protected ?string      $heading = 'إحصائيات الإيرادات';

    // أسعار الخطط (SAR / شهر)
    private const PRICE_PRO      = 99;
    private const PRICE_BUSINESS = 299;

    protected function getStats(): array
    {
        // ─── عدد المشتركين النشطين ──────────────────────────────
        $activeSubs = Subscription::where('status', 'active')->count();

        $proUsers      = User::where('subscription_plan', SubscriptionPlan::Pro)->count();
        $businessUsers = User::where('subscription_plan', SubscriptionPlan::Business)->count();

        // ─── MRR ────────────────────────────────────────────────
        $mrr = ($proUsers * self::PRICE_PRO) + ($businessUsers * self::PRICE_BUSINESS);

        // ─── ARR ────────────────────────────────────────────────
        $arr = $mrr * 12;

        // ─── Churn Rate (هذا الشهر) ─────────────────────────────
        $cancelledThisMonth = Subscription::where('status', 'cancelled')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();

        $totalLastMonth = Subscription::whereDate('created_at', '<=', now()->startOfMonth())
            ->count();

        $churnRate = $totalLastMonth > 0
            ? round(($cancelledThisMonth / $totalLastMonth) * 100, 1)
            : 0;

        // ─── نمو المشتركين (مقارنة بالشهر الماضي) ───────────────
        $newPaidThisMonth = Subscription::where('status', 'active')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $newPaidLastMonth = Subscription::where('status', 'active')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        $growthIcon = $newPaidThisMonth >= $newPaidLastMonth
            ? 'heroicon-m-arrow-trending-up'
            : 'heroicon-m-arrow-trending-down';

        $growthColor = $newPaidThisMonth >= $newPaidLastMonth ? 'success' : 'danger';

        return [
            Stat::make('MRR (شهري)', number_format($mrr) . ' SAR')
                ->description("Pro: {$proUsers} × " . self::PRICE_PRO . " | Biz: {$businessUsers} × " . self::PRICE_BUSINESS)
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart($this->getMrrChart()),

            Stat::make('ARR (سنوي)', number_format($arr) . ' SAR')
                ->description('MRR × 12')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),

            Stat::make('الاشتراكات النشطة', $activeSubs)
                ->description("{$newPaidThisMonth} جديد هذا الشهر")
                ->descriptionIcon($growthIcon)
                ->color($growthColor),

            Stat::make('Churn Rate (هذا الشهر)', $churnRate . '%')
                ->description("{$cancelledThisMonth} إلغاء من {$totalLastMonth} إجمالي")
                ->descriptionIcon('heroicon-m-arrow-uturn-left')
                ->color($churnRate > 5 ? 'danger' : ($churnRate > 2 ? 'warning' : 'success')),
        ];
    }

    /**
     * بيانات MRR لآخر 6 أشهر للرسم البياني الصغير
     */
    private function getMrrChart(): array
    {
        $chart = [];
        for ($i = 5; $i >= 0; $i--) {
            $date          = now()->subMonths($i);
            $pro           = User::where('subscription_plan', SubscriptionPlan::Pro)
                ->whereDate('created_at', '<=', $date->endOfMonth())
                ->count();
            $biz           = User::where('subscription_plan', SubscriptionPlan::Business)
                ->whereDate('created_at', '<=', $date->endOfMonth())
                ->count();
            $chart[] = ($pro * self::PRICE_PRO) + ($biz * self::PRICE_BUSINESS);
        }
        return $chart;
    }
}

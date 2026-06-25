<?php

namespace App\Filament\Widgets;

use App\Models\PaymentOrder;
use App\Models\Subscription;
use App\Models\User;
use App\Support\Enums\SubscriptionPlan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * PaymentRevenueWidget — لوحة إيرادات دقيقة مبنية على PaymentOrder + Subscription
 *
 * لا تحتوي على أي قيم مُشفَّرة.
 * تحترم الدورتين (شهري / سنوي).
 */
class PaymentRevenueWidget extends BaseWidget
{
    protected static ?int $sort    = 4;
    protected ?string     $heading = 'الإيرادات الفعلية (PaymentOrder)';

    protected function getStats(): array
    {
        return [
            $this->activeSubscriptionsStat(),
            $this->mrrStat(),
            $this->arrStat(),
            $this->paymentsTodayStat(),
            $this->paymentsThisMonthStat(),
            $this->conversionRateStat(),
            $this->expiringSoonStat(),
        ];
    }

    // ─── 1. Active Subscriptions ────────────────────────────────────────────
    private function activeSubscriptionsStat(): Stat
    {
        $active = Subscription::where('status', 'active')->count();

        $newThisMonth = Subscription::where('status', 'active')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return Stat::make('الاشتراكات النشطة', $active)
            ->description("{$newThisMonth} جديد هذا الشهر")
            ->descriptionIcon('heroicon-m-users')
            ->color('success');
    }

    // ─── 2. MRR (Monthly Recurring Revenue) ────────────────────────────────
    private function mrrStat(): Stat
    {
        // MRR = مجموع المدفوعات الشهرية + (مدفوعات السنوية ÷ 12)
        $monthlyRevenue = PaymentOrder::where('status', 'paid')
            ->where('cycle', 'monthly')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        // للسنوية: نقسم المبلغ السنوي على 12 للحصول على الحصة الشهرية
        $annualOrders = PaymentOrder::where('status', 'paid')
            ->where('cycle', 'annual')
            ->selectRaw('SUM(amount) / 12 as monthly_equiv')
            ->whereRaw('YEAR(paid_at) = ?', [now()->year])
            ->value('monthly_equiv') ?? 0;

        $mrr = round($monthlyRevenue + $annualOrders, 2);

        // مقارنة بالشهر الماضي
        $lastMonthMrr = PaymentOrder::where('status', 'paid')
            ->where('cycle', 'monthly')
            ->whereMonth('paid_at', now()->subMonth()->month)
            ->whereYear('paid_at', now()->subMonth()->year)
            ->sum('amount');

        $trend = $mrr >= $lastMonthMrr
            ? 'heroicon-m-arrow-trending-up'
            : 'heroicon-m-arrow-trending-down';

        $trendColor = $mrr >= $lastMonthMrr ? 'success' : 'danger';

        return Stat::make('MRR', '$' . number_format($mrr, 0))
            ->description('الإيراد الشهري المتكرر')
            ->descriptionIcon($trend)
            ->color($trendColor);
    }

    // ─── 3. ARR (Annual Recurring Revenue) ─────────────────────────────────
    private function arrStat(): Stat
    {
        // ARR من الاشتراكات الشهرية النشطة
        $monthlyActiveRevenue = PaymentOrder::where('status', 'paid')
            ->where('cycle', 'monthly')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        // ARR من الاشتراكات السنوية المدفوعة هذا العام
        $annualRevenue = PaymentOrder::where('status', 'paid')
            ->where('cycle', 'annual')
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        $arr = round(($monthlyActiveRevenue * 12) + $annualRevenue, 2);

        return Stat::make('ARR', '$' . number_format($arr, 0))
            ->description('الإيراد السنوي المتوقع')
            ->descriptionIcon('heroicon-m-chart-bar')
            ->color('primary');
    }

    // ─── 4. Payments Today ──────────────────────────────────────────────────
    private function paymentsTodayStat(): Stat
    {
        $countToday  = PaymentOrder::where('status', 'paid')->whereDate('paid_at', today())->count();
        $amountToday = PaymentOrder::where('status', 'paid')->whereDate('paid_at', today())->sum('amount');

        return Stat::make('مدفوعات اليوم', $countToday)
            ->description('$' . number_format($amountToday, 0) . ' إجمالي')
            ->descriptionIcon('heroicon-m-banknotes')
            ->color($countToday > 0 ? 'success' : 'gray');
    }

    // ─── 5. Payments This Month ─────────────────────────────────────────────
    private function paymentsThisMonthStat(): Stat
    {
        $countMonth  = PaymentOrder::where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->count();

        $amountMonth = PaymentOrder::where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        // مقارنة بالشهر الماضي
        $countLastMonth = PaymentOrder::where('status', 'paid')
            ->whereMonth('paid_at', now()->subMonth()->month)
            ->whereYear('paid_at', now()->subMonth()->year)
            ->count();

        $diff  = $countMonth - $countLastMonth;
        $label = $diff >= 0 ? "+{$diff} عن الشهر الماضي" : "{$diff} عن الشهر الماضي";
        $color = $diff >= 0 ? 'success' : 'danger';
        $icon  = $diff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';

        return Stat::make('مدفوعات هذا الشهر', $countMonth)
            ->description('$' . number_format($amountMonth, 0) . ' — ' . $label)
            ->descriptionIcon($icon)
            ->color($color);
    }

    // ─── 6. Free → Pro/Business Conversion Rate ─────────────────────────────
    private function conversionRateStat(): Stat
    {
        $totalUsers = User::count();
        $paidUsers  = User::whereIn('subscription_plan', [
            SubscriptionPlan::Pro,
            SubscriptionPlan::Business,
        ])->count();

        $rate = $totalUsers > 0
            ? round(($paidUsers / $totalUsers) * 100, 1)
            : 0;

        $proCount = User::where('subscription_plan', SubscriptionPlan::Pro)->count();
        $bizCount = User::where('subscription_plan', SubscriptionPlan::Business)->count();

        return Stat::make('معدل التحويل (Free → مدفوع)', $rate . '%')
            ->description("Pro: {$proCount} | Business: {$bizCount} من {$totalUsers} إجمالي")
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color($rate >= 10 ? 'success' : ($rate >= 5 ? 'warning' : 'danger'));
    }

    // ─── 7. Expiring Within 7 Days ──────────────────────────────────────────
    private function expiringSoonStat(): Stat
    {
        $expiringSoon = Subscription::where('status', 'active')
            ->whereBetween('ends_at', [now(), now()->addDays(7)])
            ->count();

        return Stat::make('تنتهي خلال 7 أيام', $expiringSoon)
            ->description($expiringSoon > 0 ? 'تحتاج متابعة أو تجديد' : 'لا توجد اشتراكات وشيكة الانتهاء')
            ->descriptionIcon('heroicon-m-clock')
            ->color($expiringSoon > 0 ? 'warning' : 'success');
    }
}

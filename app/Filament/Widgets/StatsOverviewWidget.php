<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\User;
use App\Support\Enums\SubscriptionPlan;
use App\Support\Enums\TransactionType;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalUsers = User::count();
        $proUsers   = User::where('subscription_plan', SubscriptionPlan::Pro)->count();
        $bizUsers   = User::where('subscription_plan', SubscriptionPlan::Business)->count();

        $totalRevenue = Transaction::withoutGlobalScopes()
            ->where('type', TransactionType::Income)
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        $totalExpense = Transaction::withoutGlobalScopes()
            ->where('type', TransactionType::Expense)
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        $newUsersThisMonth = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return [
            Stat::make('إجمالي المستخدمين', $totalUsers)
                ->description("{$newUsersThisMonth} مستخدم جديد هذا الشهر")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),

            Stat::make('مستخدمو Pro + Business', $proUsers + $bizUsers)
                ->description("Pro: {$proUsers} | Business: {$bizUsers}")
                ->descriptionIcon('heroicon-m-star')
                ->color('success'),

            Stat::make('إجمالي الدخل (هذا الشهر)', number_format($totalRevenue, 2) . ' SAR')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('إجمالي المصروف (هذا الشهر)', number_format($totalExpense, 2) . ' SAR')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
        ];
    }
}

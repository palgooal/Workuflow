<?php

namespace App\Modules\Dashboard\Services;

use App\Models\Debt;
use App\Models\Project;
use App\Models\Transaction;
use App\Support\Enums\TransactionType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * جميع بيانات لوحة التحكم مع Cache 30 دقيقة
     */
    public function getData(int $userId): array
    {
        return Cache::remember("dashboard:{$userId}", 1800, function () {
            return [
                'kpis'          => $this->getKpis(),
                'chart'         => $this->getChartData(),
                'recent'        => $this->getRecentTransactions(),
                'projects'      => $this->getActiveProjects(),
                'debts_due'     => $this->getDebtsDueSoon(),
            ];
        });
    }

    /**
     * مسح cache لوحة التحكم عند تغيير البيانات
     */
    public function clearCache(int $userId): void
    {
        Cache::forget("dashboard:{$userId}");
    }

    // ==================== Private Methods ====================

    private function getKpis(): array
    {
        $now       = Carbon::now();
        $lastMonth = $now->copy()->subMonth();

        // استعلام واحد لشهرين بدل استعلامين + filter في PHP
        $rows = Transaction::dateBetween(
                $lastMonth->startOfMonth()->toDateString(),
                $now->endOfMonth()->toDateString()
            )
            ->select(
                DB::raw('YEAR(transaction_date) as yr'),
                DB::raw('MONTH(transaction_date) as mo'),
                'type',
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('yr', 'mo', 'type')
            ->get();

        $now       = Carbon::now();
        $lastMonth = $now->copy()->subMonth();

        $thisIncome   = $rows->where('yr', $now->year)->where('mo', $now->month)->where('type', TransactionType::Income->value)->sum('total');
        $thisExpenses = $rows->where('yr', $now->year)->where('mo', $now->month)->where('type', TransactionType::Expense->value)->sum('total');
        $lastIncome   = $rows->where('yr', $lastMonth->year)->where('mo', $lastMonth->month)->where('type', TransactionType::Income->value)->sum('total');
        $lastExpenses = $rows->where('yr', $lastMonth->year)->where('mo', $lastMonth->month)->where('type', TransactionType::Expense->value)->sum('total');

        return [
            'income' => [
                'value'  => $thisIncome,
                'change' => $this->percentageChange($lastIncome, $thisIncome),
            ],
            'expenses' => [
                'value'  => $thisExpenses,
                'change' => $this->percentageChange($lastExpenses, $thisExpenses),
            ],
            'net' => [
                'value'  => $thisIncome - $thisExpenses,
                'change' => null,
            ],
            'projects_active' => [
                'value'  => Project::active()->count(),
                'change' => null,
            ],
        ];
    }

    private function getChartData(): array
    {
        $from = Carbon::now()->subMonths(5)->startOfMonth()->toDateString();
        $to   = Carbon::now()->endOfMonth()->toDateString();

        // استعلام واحد بدل 6 استعلامات
        $rows = Transaction::dateBetween($from, $to)
            ->select(
                DB::raw('YEAR(transaction_date) as yr'),
                DB::raw('MONTH(transaction_date) as mo'),
                'type',
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('yr', 'mo', 'type')
            ->get()
            ->groupBy(fn ($r) => "{$r->yr}-{$r->mo}");

        $months = $income = $expenses = [];

        for ($i = 5; $i >= 0; $i--) {
            $date   = Carbon::now()->subMonths($i);
            $key    = "{$date->year}-{$date->month}";
            $bucket = $rows->get($key, collect());

            $months[]   = $date->translatedFormat('M Y');
            $income[]   = round($bucket->where('type', TransactionType::Income->value)->sum('total'), 2);
            $expenses[] = round($bucket->where('type', TransactionType::Expense->value)->sum('total'), 2);
        }

        return compact('months', 'income', 'expenses');
    }

    private function getRecentTransactions(): \Illuminate\Database\Eloquent\Collection
    {
        return Transaction::with(['project', 'category'])
            ->latest('transaction_date')
            ->limit(8)
            ->get();
    }

    private function getActiveProjects(): \Illuminate\Database\Eloquent\Collection
    {
        return Project::active()
            ->withCount('transactions')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    private function getDebtsDueSoon(): \Illuminate\Database\Eloquent\Collection
    {
        return Debt::dueSoon(7)
            ->orderBy('due_date')
            ->limit(5)
            ->get();
    }

    private function percentageChange(float $old, float $new): ?float
    {
        if ($old == 0) return null;
        return round((($new - $old) / $old) * 100, 1);
    }
}

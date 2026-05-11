<?php

namespace App\Modules\Projects\Services;

use App\Models\Project;
use App\Support\Enums\TransactionType;

class ProjectFinancialService
{
    /**
     * ملخّص مالي شامل للمشروع
     */
    public function getSummary(Project $project): array
    {
        $transactions = $project->transactions()->get();

        $income   = $transactions->where('type', TransactionType::Income)->sum('amount');
        $expenses = $transactions->where('type', TransactionType::Expense)->sum('amount');
        $net      = $income - $expenses;

        $count        = $transactions->count();
        $lastActivity = $transactions->sortByDesc('transaction_date')->first()?->transaction_date;

        return [
            'income'        => $income,
            'expenses'      => $expenses,
            'net_profit'    => $net,
            'is_profitable' => $net >= 0,
            'margin'        => $income > 0 ? round(($net / $income) * 100, 1) : 0,
            'tx_count'      => $count,
            'last_activity' => $lastActivity,
        ];
    }

    /**
     * ملخص مالي لجميع مشاريع المستخدم
     */
    public function getPortfolioSummary(): array
    {
        $projects = Project::with('transactions')->get();

        $totalIncome   = 0;
        $totalExpenses = 0;
        $activeCount   = 0;

        foreach ($projects as $project) {
            $totalIncome   += $project->totalIncome();
            $totalExpenses += $project->totalExpenses();
            if ($project->is_active) {
                $activeCount++;
            }
        }

        return [
            'total_income'    => $totalIncome,
            'total_expenses'  => $totalExpenses,
            'total_net'       => $totalIncome - $totalExpenses,
            'projects_count'  => $projects->count(),
            'active_count'    => $activeCount,
        ];
    }
}

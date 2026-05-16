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

        // قيمة العقد — حساب نسبة الاستلام
        $contractValue        = (float) ($project->contract_value ?? 0);
        $contractCollected    = $contractValue > 0 ? min(round(($income / $contractValue) * 100, 1), 100) : null;
        $contractRemaining    = $contractValue > 0 ? max($contractValue - $income, 0) : null;

        // ميزانية التكاليف — حساب نسبة الإنفاق
        $expenseBudget        = (float) ($project->expense_budget ?? 0);
        $budgetUsedPercent    = $expenseBudget > 0 ? round(($expenses / $expenseBudget) * 100, 1) : null;
        $budgetRemaining      = $expenseBudget > 0 ? max($expenseBudget - $expenses, 0) : null;
        $budgetOverrun        = $expenseBudget > 0 && $expenses > $expenseBudget;

        return [
            'income'               => $income,
            'expenses'             => $expenses,
            'net_profit'           => $net,
            'is_profitable'        => $net >= 0,
            'margin'               => $income > 0 ? round(($net / $income) * 100, 1) : 0,
            'tx_count'             => $count,
            'last_activity'        => $lastActivity,
            // قيمة العقد
            'contract_value'       => $contractValue ?: null,
            'contract_collected'   => $contractCollected,
            'contract_remaining'   => $contractRemaining,
            // ميزانية التكاليف
            'expense_budget'       => $expenseBudget ?: null,
            'budget_used_percent'  => $budgetUsedPercent,
            'budget_remaining'     => $budgetRemaining,
            'budget_overrun'       => $budgetOverrun,
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

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
        $transactions  = $project->transactions()->get();
        $projectCur    = $project->currency ?? 'ILS';
        $count         = $transactions->count();
        $lastActivity  = $transactions->sortByDesc('transaction_date')->first()?->transaction_date;

        // ── تجميع حسب العملة ──────────────────────────────────────────
        $byCurrency = [];
        foreach ($transactions->groupBy('currency') as $cur => $txs) {
            $inc = $txs->where('type', TransactionType::Income)->sum('amount');
            $exp = $txs->where('type', TransactionType::Expense)->sum('amount');
            $byCurrency[$cur] = [
                'income'   => $inc,
                'expenses' => $exp,
                'net'      => $inc - $exp,
                'margin'   => $inc > 0 ? round((($inc - $exp) / $inc) * 100, 1) : 0,
            ];
        }

        $multiCurrency = count($byCurrency) > 1;

        // المجاميع بعملة المشروع فقط (للمقارنة بقيمة العقد والميزانية)
        $primaryIncome   = $byCurrency[$projectCur]['income']   ?? 0;
        $primaryExpenses = $byCurrency[$projectCur]['expenses'] ?? 0;
        $primaryNet      = $primaryIncome - $primaryExpenses;

        // للتوافق مع الكود القديم: مجاميع عملة المشروع
        $income   = $primaryIncome;
        $expenses = $primaryExpenses;
        $net      = $primaryNet;

        // قيمة العقد — بعملة المشروع فقط
        $contractValue     = (float) ($project->contract_value ?? 0);
        $contractCollected = $contractValue > 0 ? min(round(($income / $contractValue) * 100, 1), 100) : null;
        $contractRemaining = $contractValue > 0 ? max($contractValue - $income, 0) : null;

        // ميزانية التكاليف — بعملة المشروع فقط
        $expenseBudget    = (float) ($project->expense_budget ?? 0);
        $budgetUsedPercent = $expenseBudget > 0 ? round(($expenses / $expenseBudget) * 100, 1) : null;
        $budgetRemaining   = $expenseBudget > 0 ? max($expenseBudget - $expenses, 0) : null;
        $budgetOverrun     = $expenseBudget > 0 && $expenses > $expenseBudget;

        return [
            // بيانات per-currency
            'by_currency'          => $byCurrency,
            'multi_currency'       => $multiCurrency,
            'project_currency'     => $projectCur,

            // للتوافق مع الكود القديم (عملة المشروع)
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
        $projects    = Project::with('transactions')->get();
        $activeCount = 0;
        $byCurrency  = [];

        foreach ($projects as $project) {
            if ($project->is_active) {
                $activeCount++;
            }

            foreach ($project->transactions as $tx) {
                $cur = $tx->currency ?? 'ILS';
                if (!isset($byCurrency[$cur])) {
                    $byCurrency[$cur] = ['income' => 0, 'expenses' => 0];
                }
                if ($tx->type->value === 'income') {
                    $byCurrency[$cur]['income'] += $tx->amount;
                } else {
                    $byCurrency[$cur]['expenses'] += $tx->amount;
                }
            }
        }

        // إضافة الصافي لكل عملة
        foreach ($byCurrency as $cur => &$vals) {
            $vals['net'] = $vals['income'] - $vals['expenses'];
        }
        unset($vals);

        // ترتيب: العملات الأكثر دخلاً أولاً
        arsort($byCurrency);

        $multiCurrency = count($byCurrency) > 1;
        $firstCur      = array_key_first($byCurrency);

        return [
            'by_currency'    => $byCurrency,
            'multi_currency' => $multiCurrency,
            // للتوافق مع الكود القديم
            'total_income'   => $firstCur ? $byCurrency[$firstCur]['income']   : 0,
            'total_expenses' => $firstCur ? $byCurrency[$firstCur]['expenses'] : 0,
            'total_net'      => $firstCur ? $byCurrency[$firstCur]['net']      : 0,
            'projects_count' => $projects->count(),
            'active_count'   => $activeCount,
        ];
    }
}

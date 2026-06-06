<?php

namespace App\Modules\Projects\Services;

use App\Models\Project;
use App\Models\ProjectServiceMember;
use App\Support\Enums\ProjectStatus;
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

        // ميزانية التكاليف (محفوظة للتوافق مع البيانات القديمة)
        $expenseBudget     = (float) ($project->expense_budget ?? 0);
        $budgetUsedPercent = $expenseBudget > 0 ? round(($expenses / $expenseBudget) * 100, 1) : null;
        $budgetRemaining   = $expenseBudget > 0 ? max($expenseBudget - $expenses, 0) : null;
        $budgetOverrun     = $expenseBudget > 0 && $expenses > $expenseBudget;

        // ── هامش الخدمات — per-service margin ────────────────────────────
        $project->loadMissing('services');
        $servicesMargin = $this->calcServicesMargin($project);

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
            // ميزانية التكاليف (legacy)
            'expense_budget'       => $expenseBudget ?: null,
            'budget_used_percent'  => $budgetUsedPercent,
            'budget_remaining'     => $budgetRemaining,
            'budget_overrun'       => $budgetOverrun,
            // هامش الخدمات
            'services_margin'         => $servicesMargin,
            'total_members_cost'      => collect($servicesMargin)->sum('members_cost'),
            'total_services_revenue'  => collect($servicesMargin)->sum('revenue'),
            'total_services_margin'   => collect($servicesMargin)->sum('margin'),
        ];
    }

    /**
     * حساب هامش كل خدمة بناءً على إيرادها وتكاليف منفذيها
     *
     * @return array<int, array{
     *   service_id: int,
     *   name: string,
     *   revenue: float,
     *   members_cost: float,
     *   margin: float,
     *   margin_pct: float|null,
     *   is_loss: bool,
     *   members: array
     * }>
     */
    public function calcServicesMargin(Project $project): array
    {
        $result = [];

        foreach ($project->services as $service) {
            $pivotId = $service->pivot->id;
            $revenue = (float) ($service->pivot->amount ?? 0);

            // جلب المنفذين من الجدول الجديد
            $members = ProjectServiceMember::with('teamMember')
                ->where('project_service_id', $pivotId)
                ->get();

            $membersCost = $members->sum(fn ($m) => (float) ($m->team_cost ?? 0));
            $margin      = $revenue - $membersCost;
            $marginPct   = $revenue > 0 ? round(($margin / $revenue) * 100, 1) : null;

            $result[] = [
                'service_id'   => $service->id,
                'pivot_id'     => $pivotId,
                'name'         => $service->name_ar ?? $service->name ?? '',
                'revenue'      => $revenue,
                'members_cost' => $membersCost,
                'margin'       => $margin,
                'margin_pct'   => $marginPct,
                'is_loss'      => $margin < 0,
                'members'      => $members->map(fn ($m) => [
                    'id'             => $m->id,
                    'name'           => $m->teamMember?->name ?? '—',
                    'team_cost'      => (float) ($m->team_cost ?? 0),
                    'team_cost_paid' => $m->team_cost_paid,
                ])->toArray(),
            ];
        }

        return $result;
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
            if ($project->status === ProjectStatus::Active) {
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

<?php

namespace App\Modules\Budgets\Services;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BudgetService
{
    /**
     * جلب الميزانيات مع بيانات الإنفاق الفعلي — استعلام واحد محسّن
     */
    public function getBudgetsWithProgress(int $month, int $year): Collection
    {
        $budgets = Budget::with(['category', 'project'])
            ->where(function ($q) use ($month, $year) {
                // ميزانيات شهرية للشهر/السنة المحددة
                $q->where(function ($q2) use ($month, $year) {
                    $q2->where('period', 'monthly')
                       ->where('month', $month)
                       ->where('year', $year);
                })
                // ميزانيات سنوية للسنة المحددة
                ->orWhere(function ($q2) use ($year) {
                    $q2->where('period', 'yearly')
                       ->where('year', $year);
                });
            })
            ->orderBy('period')
            ->get();

        // حساب الإنفاق الفعلي لكل ميزانية (N+1 مقبول هنا لأن العدد صغير)
        return $budgets->map(function (Budget $budget) {
            $spent     = $budget->spentAmount();
            $usage     = $budget->amount > 0
                ? round(($spent / $budget->amount) * 100, 1)
                : 0;
            $remaining = max(0, $budget->amount - $spent);

            $budget->spent_amount     = $spent;
            $budget->usage_percentage = $usage;
            $budget->remaining_amount = $remaining;
            $budget->status           = match (true) {
                $usage > 100 => 'over',
                $usage >= 80 => 'warning',
                default      => 'ok',
            };

            return $budget;
        });
    }

    /**
     * ملخص سريع للـ Dashboard
     */
    public function getSummary(int $month, int $year): array
    {
        $budgets = $this->getBudgetsWithProgress($month, $year);

        return [
            'total'      => $budgets->count(),
            'over'       => $budgets->where('status', 'over')->count(),
            'warning'    => $budgets->where('status', 'warning')->count(),
            'ok'         => $budgets->where('status', 'ok')->count(),
            'total_allocated' => $budgets->sum('amount'),
            'total_spent'     => $budgets->sum('spent_amount'),
        ];
    }

    /**
     * الميزانيات المتجاوزة أو القريبة من الحد — للتنبيهات
     */
    public function getAlertBudgets(User $user, int $month, int $year): Collection
    {
        return $this->getBudgetsWithProgress($month, $year)
            ->filter(fn($b) => $b->status !== 'ok');
    }

    /**
     * تحقق من تكرار الميزانية (نفس الفئة/المشروع/الفترة)
     */
    public function budgetExists(
        ?string $categoryId,
        ?string $projectId,
        string  $period,
        int     $year,
        ?int    $month,
        ?string $excludeId = null
    ): bool {
        return Budget::query()
            ->where('category_id', $categoryId)
            ->where('project_id',  $projectId)
            ->where('period',      $period)
            ->where('year',        $year)
            ->where('month',       $month)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }
}

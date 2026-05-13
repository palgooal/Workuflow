<?php

namespace App\Modules\Recurring\Services;

use App\Models\RecurringTransaction;
use App\Modules\Recurring\Actions\ProcessRecurringAction;
use Illuminate\Database\Eloquent\Collection;

class RecurringService
{
    public function __construct(
        private readonly ProcessRecurringAction $processAction
    ) {}

    /**
     * جلب كل الالتزامات المتكررة للمستخدم الحالي مع العلاقات
     */
    public function getAll(bool $activeOnly = false): Collection
    {
        $query = RecurringTransaction::with(['category', 'project'])
            ->orderBy('is_active', 'desc')
            ->orderBy('next_due_date');

        if ($activeOnly) {
            $query->active();
        }

        return $query->get();
    }

    /**
     * معالجة جميع الالتزامات المستحقة لمستخدم معين
     */
    public function processDueForUser(int $userId): int
    {
        $due = RecurringTransaction::where('user_id', $userId)
            ->dueToday()
            ->get();

        foreach ($due as $recurring) {
            $this->processAction->execute($recurring);
        }

        return $due->count();
    }

    /**
     * معالجة جميع الالتزامات المستحقة لجميع المستخدمين (للـ Scheduler)
     */
    public function processDueForAll(): int
    {
        $due = RecurringTransaction::dueToday()->get();
        $count = 0;

        foreach ($due as $recurring) {
            $this->processAction->execute($recurring);
            $count++;
        }

        return $count;
    }

    /**
     * ملخص للـ Dashboard
     */
    public function getSummary(): array
    {
        $all = RecurringTransaction::get();

        return [
            'total'       => $all->count(),
            'active'      => $all->where('is_active', true)->count(),
            'due_today'   => RecurringTransaction::dueToday()->count(),
            'monthly_expense' => RecurringTransaction::active()
                ->where('type', 'expense')
                ->where('frequency', 'monthly')
                ->sum('amount'),
            'monthly_income' => RecurringTransaction::active()
                ->where('type', 'income')
                ->where('frequency', 'monthly')
                ->sum('amount'),
        ];
    }
}

<?php

namespace App\Modules\Transactions\Services;

use App\Models\Transaction;
use App\Support\Enums\TransactionType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class TransactionService
{
    /**
     * قائمة المعاملات مع الفلاتر والبحث والـ Pagination
     */
    public function getPaginated(Request $request, int $perPage = 20): LengthAwarePaginator
    {
        $query = Transaction::with(['project', 'category'])
            ->latest('transaction_date')
            ->latest('created_at');

        // فلتر النوع
        if ($request->filled('type') && in_array($request->type, ['income', 'expense'])) {
            $query->where('type', $request->type);
        }

        // فلتر المشروع
        if ($request->filled('project')) {
            $query->where('project_id', $request->project);
        }

        // فلتر الفئة
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // فلتر التاريخ من
        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        // فلتر التاريخ إلى
        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        // بحث نصي
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * ملخص مالي للفترة المفلترة الحالية
     */
    public function getSummary(Request $request): array
    {
        $query = Transaction::query();

        if ($request->filled('project')) {
            $query->where('project_id', $request->project);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        $all      = $query->get();
        $income   = $all->where('type', TransactionType::Income)->sum('amount');
        $expenses = $all->where('type', TransactionType::Expense)->sum('amount');

        return [
            'income'   => $income,
            'expenses' => $expenses,
            'net'      => $income - $expenses,
            'count'    => $all->count(),
        ];
    }
}

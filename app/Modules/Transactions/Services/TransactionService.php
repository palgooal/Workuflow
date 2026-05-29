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
        if ($request->filled('type') && in_array($request->type, ['income', 'expense'])) {
            $query->where('type', $request->type);
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $all = $query->get();

        // تجميع حسب العملة
        $currencies = $all->groupBy('currency');
        $byCurrency = [];

        foreach ($currencies as $cur => $txs) {
            $income   = $txs->where('type', TransactionType::Income)->sum('amount');
            $expenses = $txs->where('type', TransactionType::Expense)->sum('amount');
            $byCurrency[$cur] = [
                'income'   => $income,
                'expenses' => $expenses,
                'net'      => $income - $expenses,
            ];
        }

        // للتوافق مع الكود القديم: مجاميع أول عملة (أو 0 إذا لا يوجد)
        $firstCur  = array_key_first($byCurrency) ?? null;
        $multiCurrency = count($byCurrency) > 1;

        return [
            // بيانات per-currency الجديدة
            'by_currency'    => $byCurrency,
            'multi_currency' => $multiCurrency,

            // للتوافق مع الكود القديم (عملة واحدة)
            'income'         => $firstCur ? $byCurrency[$firstCur]['income']   : 0,
            'expenses'       => $firstCur ? $byCurrency[$firstCur]['expenses'] : 0,
            'net'            => $firstCur ? $byCurrency[$firstCur]['net']      : 0,
            'count'          => $all->count(),
        ];
    }
}

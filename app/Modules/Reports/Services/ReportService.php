<?php

namespace App\Modules\Reports\Services;

use App\Models\Project;
use App\Models\Transaction;
use App\Support\Enums\TransactionType;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * ملخص الفترة — استعلام واحد مع GROUP BY بدل تحميل كل البيانات
     */
    public function getSummary(string $from, string $to): array
    {
        $rows = Transaction::dateBetween($from, $to)
            ->select('type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as cnt'))
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        $income   = (float) ($rows[TransactionType::Income->value]->total  ?? 0);
        $expenses = (float) ($rows[TransactionType::Expense->value]->total ?? 0);
        $net      = $income - $expenses;
        $count    = (int) ($rows->sum('cnt'));

        $months = max(1, (int) Carbon::parse($from)->diffInMonths(Carbon::parse($to)) + 1);

        return [
            'income'        => $income,
            'expenses'      => $expenses,
            'net'           => $net,
            'count'         => $count,
            'avg_income'    => round($income / $months, 2),
            'avg_expenses'  => round($expenses / $months, 2),
            'profit_margin' => $income > 0 ? round(($net / $income) * 100, 1) : 0,
        ];
    }

    /**
     * الاتجاه الشهري — استعلام واحد مع GROUP BY بدل N استعلام
     */
    public function getMonthlyTrend(string $from, string $to): array
    {
        // استعلام واحد: اجمع حسب (سنة، شهر، نوع)
        $rows = Transaction::dateBetween($from, $to)
            ->select(
                DB::raw('YEAR(transaction_date) as yr'),
                DB::raw('MONTH(transaction_date) as mo'),
                'type',
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('yr', 'mo', 'type')
            ->orderBy('yr')
            ->orderBy('mo')
            ->get();

        // بناء خريطة (yr-mo) => [income, expense]
        $map = [];
        foreach ($rows as $row) {
            $key = "{$row->yr}-{$row->mo}";
            $map[$key] ??= ['income' => 0, 'expenses' => 0];
            if ($row->type === TransactionType::Income->value) {
                $map[$key]['income'] = round($row->total, 2);
            } else {
                $map[$key]['expenses'] = round($row->total, 2);
            }
        }

        // بناء مصفوفة مرتبة بجميع الأشهر في الفترة
        $labels   = [];
        $income   = [];
        $expenses = [];

        $cursor = Carbon::parse($from)->startOfMonth();
        $end    = Carbon::parse($to)->endOfMonth();

        while ($cursor->lte($end)) {
            $key      = "{$cursor->year}-{$cursor->month}";
            $labels[] = $cursor->translatedFormat('M Y');
            $income[]   = $map[$key]['income']   ?? 0;
            $expenses[] = $map[$key]['expenses'] ?? 0;
            $cursor->addMonth();
        }

        return compact('labels', 'income', 'expenses');
    }

    /**
     * توزيع الفئات — استعلام واحد مع GROUP BY
     */
    public function getCategoryBreakdown(string $from, string $to, string $type = 'expense'): Collection
    {
        $transactionType = $type === 'income' ? TransactionType::Income : TransactionType::Expense;

        $rows = Transaction::dateBetween($from, $to)
            ->where('type', $transactionType)
            ->select(
                'category_id',
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as cnt')
            )
            ->groupBy('category_id')
            ->with('category')
            ->get()
            ->map(function ($row) {
                $cat = $row->category;
                return [
                    'id'    => $cat?->id,
                    'name'  => $cat?->name  ?? 'غير مصنف',
                    'icon'  => $cat?->icon  ?? '📦',
                    'color' => $cat?->color ?? '#9ca3af',
                    'total' => round($row->total, 2),
                    'count' => $row->cnt,
                ];
            })
            ->sortByDesc('total')
            ->values();

        return $rows;
    }

    /**
     * ربحية المشاريع — استعلام واحد لكل المشاريع
     */
    public function getProjectProfitability(string $from, string $to): Collection
    {
        // استعلام واحد: مشاريع مع transactions مجمّعة حسب type
        $aggregates = Transaction::dateBetween($from, $to)
            ->whereNotNull('project_id')
            ->select(
                'project_id',
                'type',
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as cnt')
            )
            ->groupBy('project_id', 'type')
            ->get()
            ->groupBy('project_id');

        if ($aggregates->isEmpty()) {
            return collect();
        }

        $projects = Project::whereIn('id', $aggregates->keys())->get()->keyBy('id');

        return $aggregates->map(function ($rows, $projectId) use ($projects) {
            $project  = $projects[$projectId] ?? null;
            $income   = round($rows->where('type', TransactionType::Income->value)->sum('total'), 2);
            $expenses = round($rows->where('type', TransactionType::Expense->value)->sum('total'), 2);
            $net      = $income - $expenses;
            $count    = $rows->sum('cnt');

            return [
                'id'       => $projectId,
                'name'     => $project?->name  ?? 'مشروع محذوف',
                'color'    => $project?->color ?? '#6b7280',
                'income'   => $income,
                'expenses' => $expenses,
                'net'      => $net,
                'margin'   => $income > 0 ? round(($net / $income) * 100, 1) : 0,
                'tx_count' => $count,
            ];
        })
        ->sortByDesc('net')
        ->values();
    }

    /**
     * أفضل/أسوأ الأشهر — يستخدم trend المحسوب مسبقاً
     */
    public function getBestAndWorstMonths(array $trend): array
    {
        if (empty($trend['labels'])) {
            return ['best' => null, 'worst' => null];
        }

        $nets = [];
        foreach ($trend['labels'] as $i => $label) {
            $nets[$label] = $trend['income'][$i] - $trend['expenses'][$i];
        }

        arsort($nets);
        $bestKey  = (string) array_key_first($nets);
        $worstKey = (string) array_key_last($nets);

        return [
            'best'  => ['label' => $bestKey,  'net' => $nets[$bestKey]],
            'worst' => ['label' => $worstKey, 'net' => $nets[$worstKey]],
        ];
    }
}

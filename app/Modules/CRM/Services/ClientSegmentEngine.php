<?php

namespace App\Modules\CRM\Services;

use App\Models\Client;
use App\Modules\CRM\Models\SavedSegment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

/**
 * ClientSegmentEngine — محرك تقييم الشرائح الديناميكية
 *
 * Sprint 5 — S5.3
 *
 * يُحوِّل filters JSON الخاص بـ SavedSegment إلى Eloquent Builder.
 *
 * بنية الفلتر:
 * [
 *   ['field' => 'status',        'op' => 'equals',       'value' => 'active'],
 *   ['field' => 'health_score',  'op' => 'greater_than',  'value' => 60],
 *   ['field' => 'tag_ids',       'op' => 'in',            'value' => [1, 2, 3]],
 *   ['field' => 'has_overdue_followup', 'op' => 'equals', 'value' => true],
 * ]
 *
 * الـ operator المتاحة:
 *   equals | not_equals | contains | greater_than | less_than
 *   between | in | not_in | is_empty | is_not_empty
 *
 * الـ fields المتاحة:
 *   status | source | health_score | total_revenue | total_paid
 *   last_contact_at | created_at | tag_ids | has_overdue_followup | search
 */
class ClientSegmentEngine
{
    // ==================== Field/Operator Registry ====================

    private const FIELD_TYPES = [
        'status'               => 'scalar',
        'source'               => 'scalar',
        'health_score'         => 'numeric',
        'total_revenue'        => 'numeric',
        'total_paid'           => 'numeric',
        'invoice_count'        => 'numeric',
        'last_contact_at'      => 'date',
        'last_payment_at'      => 'date',
        'created_at'           => 'date',
        'tag_ids'              => 'tags',
        'has_overdue_followup' => 'boolean',
        'search'               => 'search',
    ];

    private const OPS_BY_TYPE = [
        'scalar'  => ['equals', 'not_equals', 'contains', 'in', 'not_in', 'is_empty', 'is_not_empty'],
        'numeric' => ['equals', 'not_equals', 'greater_than', 'less_than', 'between', 'is_empty', 'is_not_empty'],
        'date'    => ['equals', 'not_equals', 'greater_than', 'less_than', 'between',
                      'is_empty', 'is_not_empty', 'last_30_days', 'last_90_days', 'last_year'],
        'tags'    => ['in', 'not_in', 'is_empty', 'is_not_empty', 'all_of'],
        'boolean' => ['equals'],
        'search'  => ['contains'],
    ];

    // Operators that don't require a value
    private const NO_VALUE_OPS = ['is_empty', 'is_not_empty', 'last_30_days', 'last_90_days', 'last_year'];

    // ==================== Validation ====================

    /**
     * تحقق من صحة بنية مصفوفة الفلاتر قبل الحفظ أو التنفيذ.
     *
     * @return array{valid: bool, errors: string[]}
     */
    public static function validateFilters(array $filters): array
    {
        $errors = [];

        if (empty($filters)) {
            return ['valid' => true, 'errors' => []];
        }

        foreach (array_values($filters) as $i => $filter) {
            $prefix = "فلتر #" . ($i + 1);

            if (!is_array($filter)) {
                $errors[] = "{$prefix}: يجب أن يكون مصفوفة.";
                continue;
            }

            $field = $filter['field'] ?? null;
            $op    = $filter['op']    ?? null;
            $value = $filter['value'] ?? null;

            // حقل مطلوب وصحيح
            if (!$field) {
                $errors[] = "{$prefix}: الحقل (field) مطلوب.";
                continue;
            }

            if (!array_key_exists($field, self::FIELD_TYPES)) {
                $allowed = implode(', ', array_keys(self::FIELD_TYPES));
                $errors[] = "{$prefix}: الحقل '{$field}' غير معروف. المتاح: {$allowed}";
                continue;
            }

            // operator مطلوب وصحيح لنوع الحقل
            if (!$op) {
                $errors[] = "{$prefix}: العملية (op) مطلوبة.";
                continue;
            }

            $type        = self::FIELD_TYPES[$field];
            $allowedOps  = self::OPS_BY_TYPE[$type];

            if (!in_array($op, $allowedOps, true)) {
                $errors[] = "{$prefix}: العملية '{$op}' غير صالحة للحقل '{$field}'. المتاح: " . implode(', ', $allowedOps);
                continue;
            }

            // القيمة مطلوبة إلا لعمليات is_empty / is_not_empty / last_X
            if (!in_array($op, self::NO_VALUE_OPS, true) && ($value === null || $value === '')) {
                $errors[] = "{$prefix}: القيمة (value) مطلوبة للعملية '{$op}'.";
                continue;
            }

            // between يحتاج مصفوفة من عنصرين
            if ($op === 'between') {
                if (!is_array($value) || count($value) !== 2) {
                    $errors[] = "{$prefix}: العملية 'between' تحتاج مصفوفة من قيمتين [min, max].";
                }
            }

            // in / not_in / all_of تحتاج مصفوفة
            if (in_array($op, ['in', 'not_in', 'all_of'], true) && !is_array($value)) {
                $errors[] = "{$prefix}: العملية '{$op}' تحتاج مصفوفة من القيم.";
            }
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    /**
     * بناء Query من SavedSegment
     */
    public function evaluate(SavedSegment $segment): Builder
    {
        $query = Client::query()
                       ->where('clients.user_id', $segment->user_id)
                       ->where('clients.is_archived', false);

        $filters = $segment->filters ?? [];

        foreach ($filters as $filter) {
            $this->applyFilter($query, $filter);
        }

        return $query;
    }

    /**
     * بناء Query من مصفوفة فلاتر خام (للمعاينة الفورية)
     */
    public function evaluateFilters(int $userId, array $filters): Builder
    {
        $query = Client::query()
                       ->where('clients.user_id', $userId)
                       ->where('clients.is_archived', false);

        foreach ($filters as $filter) {
            $this->applyFilter($query, $filter);
        }

        return $query;
    }

    /**
     * تحديث client_count لكل الشرائح الديناميكية لمستخدم
     * @return int عدد الشرائح المحدَّثة
     */
    public function refreshCountsForUser(int $userId): int
    {
        $segments = SavedSegment::where('user_id', $userId)
                                ->where('is_dynamic', true)
                                ->get();

        $updated = 0;

        foreach ($segments as $segment) {
            try {
                $count = $this->evaluate($segment)->count();
                $segment->update([
                    'client_count'     => $count,
                    'last_executed_at' => now(),
                ]);
                $updated++;
            } catch (\Throwable $e) {
                Log::warning("SegmentEngine: failed to refresh segment {$segment->id}: {$e->getMessage()}");
            }
        }

        return $updated;
    }

    // ==================== Filter Application ====================

    private function applyFilter(Builder $query, array $filter): void
    {
        $field = $filter['field'] ?? null;
        $op    = $filter['op']    ?? null;
        $value = $filter['value'] ?? null;

        if (!$field || !$op) return;

        try {
            match($field) {
                'status'               => $this->applyScalarFilter($query, 'clients.status', $op, $value),
                'source'               => $this->applyScalarFilter($query, 'clients.source', $op, $value),
                'health_score'         => $this->applyNumericFilter($query, 'clients.health_score', $op, $value),
                'total_revenue'        => $this->applyNumericFilter($query, 'clients.total_revenue', $op, $value),
                'total_paid'           => $this->applyNumericFilter($query, 'clients.total_paid', $op, $value),
                'invoice_count'        => $this->applyNumericFilter($query, 'clients.invoice_count', $op, $value),
                'last_contact_at'      => $this->applyDateFilter($query, 'clients.last_contact_at', $op, $value),
                'last_payment_at'      => $this->applyDateFilter($query, 'clients.last_payment_at', $op, $value),
                'created_at'           => $this->applyDateFilter($query, 'clients.created_at', $op, $value),
                'tag_ids'              => $this->applyTagFilter($query, $op, $value),
                'has_overdue_followup' => $this->applyOverdueFollowUpFilter($query, $value),
                'search'               => $this->applySearchFilter($query, $value),
                default                => null,
            };
        } catch (\Throwable $e) {
            Log::warning("SegmentEngine: invalid filter field={$field} op={$op}: {$e->getMessage()}");
        }
    }

    // ==================== Operator Handlers ====================

    private function applyScalarFilter(Builder $query, string $column, string $op, mixed $value): void
    {
        match($op) {
            'equals'        => $query->where($column, $value),
            'not_equals'    => $query->where($column, '!=', $value),
            'contains'      => $query->where($column, 'LIKE', "%{$value}%"),
            'in'            => $query->whereIn($column, (array)$value),
            'not_in'        => $query->whereNotIn($column, (array)$value),
            'is_empty'      => $query->whereNull($column),
            'is_not_empty'  => $query->whereNotNull($column),
            default         => null,
        };
    }

    private function applyNumericFilter(Builder $query, string $column, string $op, mixed $value): void
    {
        match($op) {
            'equals'        => $query->where($column, (float)$value),
            'not_equals'    => $query->where($column, '!=', (float)$value),
            'greater_than'  => $query->where($column, '>', (float)$value),
            'less_than'     => $query->where($column, '<', (float)$value),
            'between'       => is_array($value) && count($value) === 2
                               ? $query->whereBetween($column, [(float)$value[0], (float)$value[1]])
                               : null,
            'is_empty'      => $query->whereNull($column),
            'is_not_empty'  => $query->whereNotNull($column),
            default         => null,
        };
    }

    private function applyDateFilter(Builder $query, string $column, string $op, mixed $value): void
    {
        match($op) {
            'equals'        => $query->whereDate($column, $value),
            'not_equals'    => $query->whereDate($column, '!=', $value),
            'greater_than'  => $query->where($column, '>', $value),
            'less_than'     => $query->where($column, '<', $value),
            'between'       => is_array($value) && count($value) === 2
                               ? $query->whereBetween($column, $value)
                               : null,
            'is_empty'      => $query->whereNull($column),
            'is_not_empty'  => $query->whereNotNull($column),
            // قيم مختصرة للـ UI (last_30_days, last_90_days, ...)
            'last_30_days'  => $query->where($column, '>=', now()->subDays(30)),
            'last_90_days'  => $query->where($column, '>=', now()->subDays(90)),
            'last_year'     => $query->where($column, '>=', now()->subYear()),
            default         => null,
        };
    }

    private function applyTagFilter(Builder $query, string $op, mixed $value): void
    {
        $tagIds = (array)$value;

        match($op) {
            'in' => $query->whereHas('tags', fn ($q) =>
                        $q->whereIn('client_tags.id', $tagIds)),
            'not_in' => $query->whereDoesntHave('tags', fn ($q) =>
                        $q->whereIn('client_tags.id', $tagIds)),
            'is_empty' => $query->whereDoesntHave('tags'),
            'is_not_empty' => $query->whereHas('tags'),
            // all_of: يجب أن يحمل العميل كل الوسوم
            'all_of' => collect($tagIds)->each(fn ($id) =>
                $query->whereHas('tags', fn ($q) => $q->where('client_tags.id', $id))),
            default => null,
        };
    }

    private function applyOverdueFollowUpFilter(Builder $query, mixed $value): void
    {
        $hasOverdue = filter_var($value, FILTER_VALIDATE_BOOLEAN);

        if ($hasOverdue) {
            $query->whereHas('followUps', fn ($q) =>
                $q->where('status', 'pending')
                  ->where('due_at', '<', now())
                  ->whereNull('deleted_at')
            );
        } else {
            $query->whereDoesntHave('followUps', fn ($q) =>
                $q->where('status', 'pending')
                  ->where('due_at', '<', now())
                  ->whereNull('deleted_at')
            );
        }
    }

    private function applySearchFilter(Builder $query, mixed $value): void
    {
        $term = trim((string)$value);
        if (empty($term)) return;

        $query->where(function ($q) use ($term) {
            $q->where('clients.name', 'LIKE', "%{$term}%")
              ->orWhere('clients.email', 'LIKE', "%{$term}%")
              ->orWhere('clients.company', 'LIKE', "%{$term}%")
              ->orWhere('clients.phone', 'LIKE', "%{$term}%");
        });
    }
}

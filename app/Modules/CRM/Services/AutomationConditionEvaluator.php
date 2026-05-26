<?php

namespace App\Modules\CRM\Services;

use App\Models\Client;
use Illuminate\Support\Facades\Log;

/**
 * AutomationConditionEvaluator — تقييم شروط قواعد الأتمتة
 *
 * Sprint 6 — S6.3
 *
 * يدعم nested AND/OR:
 * {
 *   "operator": "AND",
 *   "conditions": [
 *     {"field": "health_score", "op": "less_than", "value": 40},
 *     {
 *       "operator": "OR",
 *       "conditions": [
 *         {"field": "status", "op": "equals", "value": "inactive"},
 *         {"field": "total_revenue", "op": "greater_than", "value": 0}
 *       ]
 *     }
 *   ]
 * }
 *
 * أو مصفوفة مسطحة (implicit AND):
 * [
 *   {"field": "health_score", "op": "less_than", "value": 40},
 *   {"field": "status", "op": "equals", "value": "active"}
 * ]
 */
class AutomationConditionEvaluator
{
    // Cache نتائج field resolution لدورة واحدة (لتجنب re-computation)
    private array $cache = [];

    // ==================== Public API ====================

    /**
     * تقييم الشروط مقابل عميل
     * إذا كانت الشروط فارغة أو null → true (لا قيود)
     */
    public function evaluate(Client $client, mixed $conditions): bool
    {
        // إعادة تهيئة الـ cache لكل عميل
        $this->cache = [];

        if (empty($conditions)) return true;

        try {
            return $this->evaluateNode($client, $conditions);
        } catch (\Throwable $e) {
            Log::warning("AutomationConditionEvaluator: error — {$e->getMessage()}", [
                'client_id'  => $client->id,
                'conditions' => $conditions,
            ]);
            return false;
        }
    }

    // ==================== Node Evaluation ====================

    /**
     * يُقيِّم node واحد (قد يكون مصفوفة مسطحة، أو group بـ operator، أو condition واحدة)
     */
    private function evaluateNode(Client $client, mixed $node): bool
    {
        // مصفوفة مسطحة من الشروط → implicit AND
        if (isset($node[0]) && is_array($node[0])) {
            return $this->evaluateGroup($client, 'AND', $node);
        }

        // Group بـ operator صريح: {"operator": "AND/OR", "conditions": [...]}
        if (isset($node['operator']) && isset($node['conditions'])) {
            return $this->evaluateGroup($client, $node['operator'], $node['conditions']);
        }

        // Condition واحدة: {"field": "...", "op": "...", "value": "..."}
        if (isset($node['field']) && isset($node['op'])) {
            return $this->evaluateCondition($client, $node);
        }

        return true;
    }

    private function evaluateGroup(Client $client, string $operator, array $conditions): bool
    {
        $op = strtoupper($operator);

        foreach ($conditions as $condition) {
            $result = $this->evaluateNode($client, $condition);

            if ($op === 'OR' && $result) return true;
            if ($op === 'AND' && !$result) return false;
        }

        return $op === 'AND'; // AND: كلها صحيحة → true | OR: لا شيء صحيح → false
    }

    private function evaluateCondition(Client $client, array $condition): bool
    {
        $field = $condition['field'];
        $op    = $condition['op'];
        $value = $condition['value'] ?? null;

        $resolved = $this->resolveField($client, $field);

        return $this->compare($resolved, $op, $value);
    }

    // ==================== Field Resolvers ====================

    private function resolveField(Client $client, string $field): mixed
    {
        $cacheKey = "{$client->id}:{$field}";

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $value = match($field) {
            'status'               => $client->status?->value ?? $client->status,
            'source'               => $client->source?->value ?? $client->source,
            'health_score'         => $client->health_score,
            'total_revenue'        => (float)($client->total_revenue ?? 0),
            'total_paid'           => (float)($client->total_paid ?? 0),
            'invoice_count'        => (int)($client->invoice_count ?? 0),
            'last_contact_at'      => $client->last_contact_at,
            'last_payment_at'      => $client->last_payment_at,
            'created_at'           => $client->created_at,
            'days_since_contact'   => $this->daysSince($client->last_contact_at),
            'days_since_payment'   => $this->daysSince($client->last_payment_at),
            'days_since_created'   => (int) now()->diffInDays($client->created_at),
            'has_tag'              => fn ($tag) => $this->hasTag($client, $tag),
            'overdue_follow_ups'   => $this->countOverdueFollowUps($client),
            'payment_rate'         => $this->paymentRate($client),
            default                => null,
        };

        $this->cache[$cacheKey] = $value;

        return $value;
    }

    // ==================== Comparators ====================

    private function compare(mixed $fieldValue, string $op, mixed $conditionValue): bool
    {
        // معالجة خاصة لـ has_tag
        if (is_callable($fieldValue)) {
            return $fieldValue($conditionValue);
        }

        // معالجة التواريخ
        if ($fieldValue instanceof \Carbon\Carbon || $fieldValue instanceof \DateTime) {
            return $this->compareDates($fieldValue, $op, $conditionValue);
        }

        return match($op) {
            'equals'        => $fieldValue == $conditionValue,
            'not_equals'    => $fieldValue != $conditionValue,
            'greater_than'  => is_numeric($fieldValue) && (float)$fieldValue > (float)$conditionValue,
            'less_than'     => is_numeric($fieldValue) && (float)$fieldValue < (float)$conditionValue,
            'gte'           => is_numeric($fieldValue) && (float)$fieldValue >= (float)$conditionValue,
            'lte'           => is_numeric($fieldValue) && (float)$fieldValue <= (float)$conditionValue,
            'between'       => is_array($conditionValue) && count($conditionValue) === 2
                               && (float)$fieldValue >= (float)$conditionValue[0]
                               && (float)$fieldValue <= (float)$conditionValue[1],
            'contains'      => is_string($fieldValue) && str_contains($fieldValue, (string)$conditionValue),
            'in'            => in_array($fieldValue, (array)$conditionValue),
            'not_in'        => !in_array($fieldValue, (array)$conditionValue),
            'is_empty'      => $fieldValue === null || $fieldValue === '' || $fieldValue === 0,
            'is_not_empty'  => $fieldValue !== null && $fieldValue !== '' && $fieldValue !== 0,
            default         => false,
        };
    }

    private function compareDates(mixed $date, string $op, mixed $value): bool
    {
        if ($date === null) {
            return in_array($op, ['is_empty']) ? true : false;
        }

        $carbon = \Carbon\Carbon::parse($date);

        return match($op) {
            'equals'       => $carbon->isSameDay(\Carbon\Carbon::parse($value)),
            'greater_than' => $carbon->isAfter(\Carbon\Carbon::parse($value)),
            'less_than'    => $carbon->isBefore(\Carbon\Carbon::parse($value)),
            'between'      => is_array($value) && count($value) === 2
                              && $carbon->isBetween(\Carbon\Carbon::parse($value[0]), \Carbon\Carbon::parse($value[1])),
            'is_empty'     => false,
            'is_not_empty' => true,
            default        => false,
        };
    }

    // ==================== Field Helpers ====================

    private function daysSince(mixed $date): ?int
    {
        if (!$date) return null;
        return (int) now()->diffInDays(\Carbon\Carbon::parse($date));
    }

    private function hasTag(Client $client, mixed $tagSlug): bool
    {
        // يُحمِّل العلاقة إذا لم تكن محمّلة
        if (!$client->relationLoaded('tags')) {
            $client->load('tags:id,slug');
        }
        return $client->tags->contains('slug', (string)$tagSlug);
    }

    private function countOverdueFollowUps(Client $client): int
    {
        return $client->followUps()
                      ->where('status', 'pending')
                      ->where('due_at', '<', now())
                      ->count();
    }

    private function paymentRate(Client $client): float
    {
        $revenue = (float)($client->total_revenue ?? 0);
        if ($revenue <= 0) return 0.0;
        return round(((float)($client->total_paid ?? 0)) / $revenue, 2);
    }
}

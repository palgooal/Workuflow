<?php

namespace App\Modules\CRM\Builders;

use App\Models\Client;
use App\Modules\CRM\DTOs\ClientFiltersDTO;
use App\Modules\CRM\Enums\HealthScoreGrade;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * ClientQueryBuilder — محرك البحث والفلترة المركزي
 *
 * مرجع: docs/CLIENTS-CRM-SPEC-V2.md — Sprint 1, S1.8
 *
 * - C-05: cursorPaginate() بدل offset (يتحمّل قوائم الآلاف)
 * - LIKE-based search (لا FULLTEXT على Shared Hosting)
 * - user_id scope guard في الـ constructor — لا يمكن تجاوزه
 */
class ClientQueryBuilder
{
    private Builder $query;

    public function __construct(private readonly int $userId)
    {
        // الـ scope الأساسي — دائماً مُطبَّق
        $this->query = Client::query()
            ->where('clients.user_id', $this->userId);
    }

    // ==================== Core Filters ====================

    /**
     * تطبيق جميع فلاتر ClientFiltersDTO دفعةً واحدة
     */
    public function applyFilters(ClientFiltersDTO $filters): static
    {
        // أرشفة
        $this->query->where('clients.is_archived', $filters->isArchived ?? false);

        // حالة العميل
        if ($filters->status !== null) {
            $this->query->where('clients.status', $filters->status->value);
        }

        // المصدر
        if ($filters->source !== null) {
            $this->query->where('clients.source', $filters->source->value);
        }

        // الوسوم
        if (! empty($filters->tagIds)) {
            $this->byTags($filters->tagIds);
        }

        // Health Score
        if ($filters->healthMin !== null) {
            $this->query->where('clients.health_score', '>=', $filters->healthMin);
        }
        if ($filters->healthMax !== null) {
            $this->query->where('clients.health_score', '<=', $filters->healthMax);
        }

        // متابعات معلّقة — يُتجاهل إذا كان الجدول غير موجود بعد
        if ($filters->hasPendingFollowUp !== null
            && \Illuminate\Support\Facades\Schema::hasTable('client_follow_ups')
        ) {
            if ($filters->hasPendingFollowUp) {
                $this->query->whereHas('followUps', fn ($q) =>
                    $q->whereIn('status', ['pending', 'overdue'])
                );
            } else {
                $this->query->whereDoesntHave('followUps', fn ($q) =>
                    $q->whereIn('status', ['pending', 'overdue'])
                );
            }
        }

        // بحث نصي
        if ($filters->search !== null && $filters->search !== '') {
            $this->search($filters->search);
        }

        // ترتيب
        $this->applySort($filters->sortBy, $filters->sortDir);

        return $this;
    }

    /**
     * بحث LIKE على name + email + company + phone
     * (FULLTEXT غير متاح على Shared Hosting — C-05)
     */
    public function search(string $term): static
    {
        $like = '%' . addcslashes(trim($term), '%_\\') . '%';

        $this->query->where(function (Builder $q) use ($like) {
            $q->where('clients.name',    'LIKE', $like)
              ->orWhere('clients.email',   'LIKE', $like)
              ->orWhere('clients.company', 'LIKE', $like)
              ->orWhere('clients.phone',   'LIKE', $like);
        });

        return $this;
    }

    /**
     * فلترة بالوسوم — يجب أن يمتلك العميل جميع الوسوم المطلوبة (AND)
     * استخدم byTagsAny() إذا أردت OR
     */
    public function byTags(array $tagIds): static
    {
        foreach ($tagIds as $tagId) {
            $this->query->whereHas('tags', fn ($q) =>
                $q->where('client_tags.id', (int) $tagId)
            );
        }

        return $this;
    }

    /**
     * فلترة بالوسوم — يكفي امتلاك أي وسم (OR)
     */
    public function byTagsAny(array $tagIds): static
    {
        $this->query->whereHas('tags', fn ($q) =>
            $q->whereIn('client_tags.id', array_map('intval', $tagIds))
        );

        return $this;
    }

    /**
     * فلترة بدرجة الصحة (grade بدل رقم مباشر)
     */
    public function byHealthGrade(HealthScoreGrade $grade): static
    {
        $min = $grade->minScore();
        $max = match ($grade) {
            HealthScoreGrade::Excellent => 100,
            HealthScoreGrade::Good      => 79,
            HealthScoreGrade::Fair      => 59,
            HealthScoreGrade::Poor      => 39,
        };

        $this->query->whereBetween('clients.health_score', [$min, $max]);

        return $this;
    }

    /**
     * العملاء الذين لديهم متابعات مستحقة اليوم أو متأخرة
     */
    public function withFollowUpsDue(): static
    {
        $this->query->whereHas('followUps', fn ($q) =>
            $q->whereIn('status', ['pending', 'overdue'])
              ->where('due_at', '<=', now())
        );

        return $this;
    }

    // ==================== Relations ====================

    /**
     * تحميل العلاقات — القيم الافتراضية لقائمة العملاء
     */
    public function withRelations(array $relations = ['tags', 'latestHealthScore']): static
    {
        $this->query->with($relations);

        return $this;
    }

    /**
     * تحميل عدد المتابعات المعلّقة بدون N+1
     */
    public function withPendingFollowUpsCount(): static
    {
        $this->query->withCount([
            'followUps as pending_follow_ups_count' => fn ($q) =>
                $q->whereIn('status', ['pending', 'overdue']),
        ]);

        return $this;
    }

    // ==================== Sorting ====================

    private function applySort(string $sortBy, string $sortDir): void
    {
        $allowed = [
            'name'            => 'clients.name',
            'created_at'      => 'clients.created_at',
            'last_contact_at' => 'clients.last_contact_at',
            'health_score'    => 'clients.health_score',
            'total_revenue'   => 'clients.total_revenue',
        ];

        $column = $allowed[$sortBy] ?? 'clients.created_at';
        $dir    = $sortDir === 'asc' ? 'asc' : 'desc';

        // NULLS LAST لحقول nullable
        if (in_array($sortBy, ['health_score', 'last_contact_at', 'total_revenue'], true)) {
            $this->query->orderByRaw("CASE WHEN {$column} IS NULL THEN 1 ELSE 0 END")
                        ->orderBy($column, $dir);
        } else {
            $this->query->orderBy($column, $dir);
        }

        // ثانوي ثابت: id لضمان حتمية الـ cursor
        $this->query->orderBy('clients.id', $dir);
    }

    // ==================== Execution ====================

    /**
     * C-05: Cursor Pagination — أداء ثابت بغض النظر عن حجم البيانات
     */
    public function cursorPaginate(int $perPage = 20): CursorPaginator
    {
        return $this->query->cursorPaginate($perPage);
    }

    /**
     * للأغراض الخاصة (مثل التصدير الكامل)
     */
    public function get(): Collection
    {
        return $this->query->get();
    }

    public function count(): int
    {
        return $this->query->count();
    }

    /**
     * إرجاع الـ Builder الخام للاستخدام في Export (بدون pagination)
     */
    public function toExportQuery(): Builder
    {
        return clone $this->query;
    }

    /**
     * إرجاع الـ Builder الخام — للاستخدام في SavedSegment::buildQuery()
     */
    public function getQuery(): Builder
    {
        return $this->query;
    }
}

<?php

namespace App\Modules\CRM\Services;

use App\Models\Client;
use App\Modules\CRM\Builders\ClientQueryBuilder;
use App\Modules\CRM\DTOs\ClientFiltersDTO;
use App\Modules\CRM\Models\SavedSegment;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SavedSegmentService
{
    // ==================== CRUD ====================

    public function create(int $userId, string $name, array $filters, bool $pinned = false): SavedSegment
    {
        return DB::transaction(function () use ($userId, $name, $filters, $pinned) {
            return SavedSegment::create([
                'user_id'  => $userId,
                'name'     => $name,
                'filters'  => $filters,
                'is_pinned' => $pinned,
            ]);
        });
    }

    public function destroy(SavedSegment $segment): void
    {
        $segment->delete();
    }

    public function pin(SavedSegment $segment, bool $pinned = true): SavedSegment
    {
        $segment->update(['is_pinned' => $pinned]);
        return $segment->refresh();
    }

    // ==================== Queries ====================

    /**
     * شرائح المستخدم — المثبتة أولاً ثم بالتاريخ
     */
    public function forUser(int $userId): Collection
    {
        return SavedSegment::where('user_id', $userId)
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->get();
    }

    // ==================== Execution ====================

    /**
     * معاينة نتائج الشريحة (بدون حفظ)
     */
    public function preview(int $userId, array $filters, int $perPage = 20): CursorPaginator
    {
        $dto = ClientFiltersDTO::fromArray($filters, $userId);
        return (new ClientQueryBuilder($userId))->applyFilters($dto)->cursorPaginate($perPage);
    }

    /**
     * تنفيذ شريحة محفوظة وإرجاع نتائجها
     */
    public function execute(SavedSegment $segment, int $perPage = 20): CursorPaginator
    {
        return $this->preview($segment->user_id, $segment->filters ?? [], $perPage);
    }
}

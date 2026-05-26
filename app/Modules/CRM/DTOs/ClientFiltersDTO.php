<?php

namespace App\Modules\CRM\DTOs;

use App\Modules\CRM\Enums\ClientSource;
use App\Modules\CRM\Enums\ClientStatus;
use Illuminate\Http\Request;

final readonly class ClientFiltersDTO
{
    public function __construct(
        // فلاتر الحالة
        public ?ClientStatus $status     = null,
        public ?bool         $isArchived = false,

        // فلاتر الوسوم والمصدر
        public array         $tagIds     = [],
        public ?ClientSource $source     = null,

        // فلاتر الـ Health Score
        public ?int          $healthMin  = null,
        public ?int          $healthMax  = null,

        // فلاتر المتابعات
        public ?bool         $hasPendingFollowUp = null,

        // فلتر الشريحة المحفوظة
        public ?string       $segmentId  = null,   // ULID

        // بحث نصي
        public ?string       $search     = null,

        // ترتيب وصفحة
        public string        $sortBy     = 'created_at',
        public string        $sortDir    = 'desc',
        public int           $perPage    = 20,
    ) {}

    // ==================== Factory ====================

    public static function fromRequest(Request $request): self
    {
        // تنظيف وترتيب القيم المسموح بها
        $allowedSortBy  = ['name', 'created_at', 'last_contact_at', 'health_score', 'total_revenue'];
        $allowedSortDir = ['asc', 'desc'];

        $sortBy  = in_array($request->string('sort_by')->toString(), $allowedSortBy, true)
            ? $request->string('sort_by')->toString()
            : 'created_at';

        $sortDir = in_array($request->string('sort_dir')->toString(), $allowedSortDir, true)
            ? $request->string('sort_dir')->toString()
            : 'desc';

        $perPage = min(max((int) $request->input('per_page', 20), 5), 100);

        $status = null;
        if ($request->filled('status') && ClientStatus::tryFrom($request->string('status')->toString())) {
            $status = ClientStatus::from($request->string('status')->toString());
        }

        $source = null;
        if ($request->filled('source') && ClientSource::tryFrom($request->string('source')->toString())) {
            $source = ClientSource::from($request->string('source')->toString());
        }

        $tagIds = array_filter(
            array_map('intval', (array) $request->input('tag_ids', [])),
            fn ($id) => $id > 0
        );

        return new self(
            status:             $status,
            isArchived:         $request->filled('is_archived') ? $request->boolean('is_archived') : false,
            tagIds:             array_values($tagIds),
            source:             $source,
            healthMin:          $request->filled('health_min') ? (int) $request->input('health_min') : null,
            healthMax:          $request->filled('health_max') ? (int) $request->input('health_max') : null,
            hasPendingFollowUp: $request->filled('has_follow_up') ? $request->boolean('has_follow_up') : null,
            segmentId:          $request->filled('segment_id') ? $request->string('segment_id')->toString() : null,
            search:             $request->filled('search') ? $request->string('search')->trim()->toString() : null,
            sortBy:             $sortBy,
            sortDir:            $sortDir,
            perPage:            $perPage,
        );
    }

    /**
     * بناء DTO من مصفوفة عادية (للشرائح المحفوظة)
     */
    public static function fromArray(array $data, int $userId): self
    {
        $allowedSortBy  = ['name', 'created_at', 'last_contact_at', 'health_score', 'total_revenue'];
        $allowedSortDir = ['asc', 'desc'];

        $sortBy  = in_array($data['sort_by'] ?? '', $allowedSortBy, true) ? $data['sort_by'] : 'created_at';
        $sortDir = in_array($data['sort_dir'] ?? '', $allowedSortDir, true) ? $data['sort_dir'] : 'desc';
        $perPage = min(max((int) ($data['per_page'] ?? 20), 5), 100);

        $status = isset($data['status']) && ClientStatus::tryFrom($data['status'])
            ? ClientStatus::from($data['status']) : null;

        $source = isset($data['source']) && ClientSource::tryFrom($data['source'])
            ? ClientSource::from($data['source']) : null;

        $tagIds = array_values(array_filter(
            array_map('intval', (array) ($data['tag_ids'] ?? [])),
            fn ($id) => $id > 0
        ));

        return new self(
            status:             $status,
            isArchived:         isset($data['is_archived']) ? (bool) $data['is_archived'] : false,
            tagIds:             $tagIds,
            source:             $source,
            healthMin:          isset($data['health_min']) ? (int) $data['health_min'] : null,
            healthMax:          isset($data['health_max']) ? (int) $data['health_max'] : null,
            hasPendingFollowUp: isset($data['has_follow_up']) ? (bool) $data['has_follow_up'] : null,
            segmentId:          $data['segment_id'] ?? null,
            search:             $data['search'] ?? null,
            sortBy:             $sortBy,
            sortDir:            $sortDir,
            perPage:            $perPage,
        );
    }

    // ==================== Helpers ====================

    public function hasFilters(): bool
    {
        return $this->status !== null
            || $this->isArchived === true
            || ! empty($this->tagIds)
            || $this->source !== null
            || $this->healthMin !== null
            || $this->healthMax !== null
            || $this->hasPendingFollowUp !== null
            || $this->segmentId !== null
            || $this->search !== null;
    }
}

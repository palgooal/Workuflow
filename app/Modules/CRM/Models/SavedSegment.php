<?php

namespace App\Modules\CRM\Models;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedSegment extends Model
{
    use HasUlids;

    protected $table = 'saved_segments';

    protected $fillable = [
        'user_id',
        'name',
        'filters',
        'is_dynamic',
        'client_count',
        'last_executed_at',
        'is_pinned',
    ];

    protected function casts(): array
    {
        return [
            'filters'          => 'array',
            'is_dynamic'       => 'boolean',
            'is_pinned'        => 'boolean',
            'client_count'     => 'integer',
            'last_executed_at' => 'datetime',
        ];
    }

    // ==================== Relations ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==================== Scopes ====================

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId)
                     ->orderByDesc('is_pinned')
                     ->orderBy('name');
    }

    // ==================== Helpers ====================

    /**
     * تنفيذ الفلاتر وإرجاع Builder.
     * يُستخدم من ClientSegmentEngine (Sprint 5).
     */
    public function buildQuery(): Builder
    {
        return Client::query()
                     ->where('user_id', $this->user_id)
                     ->where('is_archived', false);
        // تطبيق الفلاتر يتم في ClientSegmentEngine
    }

    /** تحديث عدد العملاء المطابقين */
    public function refreshCount(): int
    {
        $count = $this->buildQuery()->count();

        $this->update([
            'client_count'     => $count,
            'last_executed_at' => now(),
        ]);

        return $count;
    }
}

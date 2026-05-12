<?php

namespace App\Models;

use App\Support\Enums\DebtStatus;
use App\Support\Enums\DebtType;
use App\Support\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Debt extends Model
{
    /** @use HasFactory<\Database\Factories\DebtFactory> */
    use HasFactory, HasUlids, SoftDeletes, BelongsToUser;

    protected $fillable = [
        'user_id',
        'project_id',
        'type',
        'party_name',
        'amount',
        'remaining_amount',
        'currency',
        'due_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type'             => DebtType::class,
            'status'           => DebtStatus::class,
            'amount'           => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'due_date'         => 'date',
        ];
    }

    // ==================== Relations ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // ==================== Scopes ====================

    public function scopeActive($query)
    {
        return $query->where('status', '!=', DebtStatus::Paid);
    }

    public function scopeDueSoon($query, int $days = 7)
    {
        return $query->active()
                     ->whereNotNull('due_date')
                     ->whereDate('due_date', '<=', now()->addDays($days));
    }

    public function scopeBorrowed($query)
    {
        return $query->where('type', DebtType::Borrowed);
    }

    public function scopeLent($query)
    {
        return $query->where('type', DebtType::Lent);
    }

    // ==================== Helpers ====================

    public function paidPercentage(): float
    {
        if ($this->amount == 0) return 100;
        return round((($this->amount - $this->remaining_amount) / $this->amount) * 100, 1);
    }

    public function isPaid(): bool
    {
        return $this->status === DebtStatus::Paid;
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && !$this->isPaid();
    }
}

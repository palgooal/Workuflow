<?php

namespace App\Models;

use App\Support\Enums\RecurringFrequency;
use App\Support\Enums\TransactionType;
use App\Support\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringTransaction extends Model
{
    use HasFactory, HasUlids, BelongsToUser;

    protected $fillable = [
        'user_id',
        'project_id',
        'category_id',
        'type',
        'amount',
        'currency',
        'description',
        'frequency',
        'start_date',
        'next_due_date',
        'end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type'          => TransactionType::class,
            'frequency'     => RecurringFrequency::class,
            'amount'        => 'decimal:2',
            'start_date'    => 'date',
            'next_due_date' => 'date',
            'end_date'      => 'date',
            'is_active'     => 'boolean',
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // ==================== Scopes ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDueToday($query)
    {
        return $query->active()
                     ->whereDate('next_due_date', '<=', today());
    }

    // ==================== Helpers ====================

    /**
     * تحديث تاريخ الاستحقاق القادم بعد معالجة الدورة الحالية
     */
    public function advanceToNextDue(): void
    {
        $this->update([
            'next_due_date' => $this->frequency->nextDate($this->next_due_date),
        ]);
    }

    public function isDue(): bool
    {
        return $this->is_active && $this->next_due_date->lte(today());
    }
}

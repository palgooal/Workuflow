<?php

namespace App\Models;

use App\Support\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes, BelongsToUser;

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'email',
        'company',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // ==================== Relations ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    // ==================== Scopes ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ==================== Helpers ====================

    public function getDisplayNameAttribute(): string
    {
        return $this->company
            ? "{$this->name} ({$this->company})"
            : $this->name;
    }

    public function totalRevenue(): float
    {
        return $this->projects()
            ->withSum(['transactions as income_sum' => fn ($q) => $q->where('type', 'income')], 'amount')
            ->get()
            ->sum('income_sum');
    }
}

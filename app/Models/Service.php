<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'name_ar',
        'description',
        'icon',
        'color',
        'is_global',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_global' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // ==================== Relations ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_service')
            ->withPivot(['amount', 'type', 'client_id', 'notes'])
            ->withTimestamps();
    }

    // ==================== Scopes ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('is_global', true)
              ->orWhere('user_id', $userId);
        });
    }

    // ==================== Helpers ====================

    public function getDisplayNameAttribute(): string
    {
        return $this->name_ar ?? $this->name;
    }
}

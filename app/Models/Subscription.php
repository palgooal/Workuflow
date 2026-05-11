<?php

namespace App\Models;

use App\Support\Enums\SubscriptionPlan;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'user_id',
        'plan',
        'status',
        'payment_provider',
        'provider_subscription_id',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'plan'      => SubscriptionPlan::class,
            'starts_at' => 'datetime',
            'ends_at'   => 'datetime',
        ];
    }

    // ==================== Relations ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==================== Scopes ====================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // ==================== Helpers ====================

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isExpired(): bool
    {
        return $this->ends_at && $this->ends_at->isPast();
    }
}

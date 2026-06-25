<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentOrder extends Model
{
    use HasUlids;

    protected $fillable = [
        'user_id',
        'plan',
        'cycle',
        'provider',
        'provider_order_id',
        'provider_hashed_id',
        'amount',
        'currency',
        'status',
        'paid_at',
        'failed_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount'    => 'decimal:2',
            'paid_at'   => 'datetime',
            'failed_at' => 'datetime',
            'metadata'  => 'array',
        ];
    }

    // ──────────────────────────────────────────
    // Relations
    // ──────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ──────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    // ──────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function markAsPaid(array $metadata = []): void
    {
        $this->update([
            'status'   => 'paid',
            'paid_at'  => now(),
            'metadata' => $metadata ?: $this->metadata,
        ]);
    }

    public function markAsFailed(array $metadata = []): void
    {
        $this->update([
            'status'    => 'failed',
            'failed_at' => now(),
            'metadata'  => $metadata ?: $this->metadata,
        ]);
    }

    public function markAsCancelled(): void
    {
        $this->update(['status' => 'cancelled']);
    }
}

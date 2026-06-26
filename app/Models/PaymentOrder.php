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

    // ──────────────────────────────────────────
    // Payment Timeline
    // ──────────────────────────────────────────

    /**
     * يُضيف حدثاً إلى سجل timeline داخل metadata
     */
    public function addTimelineEvent(string $event, array $extra = []): void
    {
        $current = $this->metadata ?? [];
        $events  = $current['timeline_events'] ?? [];

        $events[] = array_merge([
            'event' => $event,
            'at'    => now()->toIso8601String(),
        ], $extra);

        $current['timeline_events'] = $events;

        $this->update(['metadata' => $current]);
    }

    /**
     * يُعيد أحداث timeline مدمجة مع النقاط الجوهرية من الـ timestamps
     */
    public function getTimelineEvents(): array
    {
        $events = $this->metadata['timeline_events'] ?? [];

        // أضف نقاط النظام من timestamps لو لم تُسجَّل صراحةً
        $hasCreated = collect($events)->contains('event', 'order.created');
        if (! $hasCreated) {
            array_unshift($events, [
                'event' => 'order.created',
                'at'    => $this->created_at?->toIso8601String(),
            ]);
        }

        if ($this->paid_at) {
            $hasPaid = collect($events)->contains('event', 'payment.marked_paid');
            if (! $hasPaid) {
                $events[] = [
                    'event' => 'payment.marked_paid',
                    'at'    => $this->paid_at->toIso8601String(),
                ];
            }
        }

        if ($this->failed_at) {
            $hasFailed = collect($events)->contains('event', 'payment.failed');
            if (! $hasFailed) {
                $events[] = [
                    'event' => 'payment.failed',
                    'at'    => $this->failed_at->toIso8601String(),
                ];
            }
        }

        // رتِّب بالتاريخ
        usort($events, fn ($a, $b) => strcmp($a['at'] ?? '', $b['at'] ?? ''));

        return $events;
    }
}

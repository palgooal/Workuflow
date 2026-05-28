<?php

namespace App\Models;

use App\Support\Enums\QuoteStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Quote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ulid', 'token',
        'user_id', 'client_id', 'project_id',
        'number', 'title', 'status',
        'issue_date', 'valid_until',
        'subtotal', 'tax_rate', 'tax_amount', 'discount', 'total',
        'currency', 'notes', 'terms',
        'sent_at', 'viewed_at', 'accepted_at', 'rejected_at', 'converted_at',
        'client_ip', 'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'status'       => QuoteStatus::class,
            'issue_date'   => 'date',
            'valid_until'  => 'date',
            'subtotal'     => 'decimal:2',
            'tax_rate'     => 'decimal:2',
            'tax_amount'   => 'decimal:2',
            'discount'     => 'decimal:2',
            'total'        => 'decimal:2',
            'sent_at'      => 'datetime',
            'viewed_at'    => 'datetime',
            'accepted_at'  => 'datetime',
            'rejected_at'  => 'datetime',
            'converted_at' => 'datetime',
        ];
    }

    // ==================== Boot ====================

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $quote) {
            if (empty($quote->ulid)) {
                $quote->ulid = Str::ulid()->toString();
            }
            if (empty($quote->token)) {
                $quote->token = Str::random(48);
            }
            if (empty($quote->number)) {
                $quote->number = self::generateNumber($quote->user_id);
            }
        });
    }

    // ==================== Relations ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class)->orderBy('sort_order');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'reference', 'number');
    }

    // ==================== Scopes ====================

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ==================== Helpers ====================

    public static function generateNumber(int $userId): string
    {
        $last = self::where('user_id', $userId)->max('id') ?? 0;
        return 'QUO-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }

    public function recalculate(): void
    {
        $subtotal  = $this->items->sum(fn ($i) => $i->quantity * $i->unit_price);
        $taxAmount = round($subtotal * ($this->tax_rate / 100), 2);
        $total     = $subtotal + $taxAmount - $this->discount;

        $this->update([
            'subtotal'   => $subtotal,
            'tax_amount' => $taxAmount,
            'total'      => max(0, $total),
        ]);
    }

    public function isExpired(): bool
    {
        return $this->valid_until
            && $this->valid_until->isPast()
            && ! in_array($this->status, [
                QuoteStatus::Accepted,
                QuoteStatus::Rejected,
                QuoteStatus::Converted,
            ]);
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    /** رابط بوابة العميل */
    public function portalUrl(): string
    {
        return route('quotes.portal', $this->token);
    }
}

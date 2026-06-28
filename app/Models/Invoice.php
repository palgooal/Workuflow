<?php

namespace App\Models;

use App\Support\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ulid', 'user_id', 'client_id', 'project_id',
        'number', 'status', 'title',
        'issue_date', 'due_date',
        'subtotal', 'tax_rate', 'tax_amount', 'discount', 'discount_type', 'total',
        'currency', 'notes', 'terms',
        'sent_at', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status'     => InvoiceStatus::class,
            'issue_date' => 'date',
            'due_date'   => 'date',
            'subtotal'   => 'decimal:2',
            'tax_rate'   => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount'   => 'decimal:2',
            'total'      => 'decimal:2',
            'sent_at'    => 'datetime',
            'paid_at'    => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $invoice) {
            if (empty($invoice->ulid)) {
                $invoice->ulid = Str::ulid()->toString();
            }
            if (empty($invoice->number)) {
                $invoice->number = self::generateNumber($invoice->user_id);
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
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    // ==================== Scopes ====================

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    // ==================== Helpers ====================

    public static function generateNumber(int $userId): string
    {
        $count = self::withTrashed()->where('user_id', $userId)->count();
        $next  = $count + 1;

        while (
            self::withTrashed()
                ->where('user_id', $userId)
                ->where('number', 'INV-' . str_pad($next, 4, '0', STR_PAD_LEFT))
                ->exists()
        ) {
            $next++;
        }

        return 'INV-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function recalculate(): void
    {
        $subtotal = $this->items->sum(fn($i) => $i->quantity * $i->unit_price);
        $taxAmount = round($subtotal * ($this->tax_rate / 100), 2);

        $discountAmount = ($this->discount_type === 'percentage')
            ? round($subtotal * ($this->discount / 100), 2)
            : (float) $this->discount;

        $this->update([
            'subtotal'   => $subtotal,
            'tax_amount' => $taxAmount,
            'total'      => max(0, $subtotal + $taxAmount - $discountAmount),
        ]);
    }

    /** القيمة الفعلية للخصم بالعملة */
    public function getDiscountAmountAttribute(): float
    {
        if ($this->discount_type === 'percentage') {
            return round((float) $this->subtotal * ($this->discount / 100), 2);
        }
        return (float) $this->discount;
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && ! in_array($this->status, [InvoiceStatus::Paid, InvoiceStatus::Cancelled]);
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }
}

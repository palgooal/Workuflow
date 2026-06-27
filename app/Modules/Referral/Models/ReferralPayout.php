<?php

namespace App\Modules\Referral\Models;

use App\Modules\Referral\Enums\PayoutMethod;
use App\Modules\Referral\Enums\PayoutStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ReferralPayout — طلب صرف عمولة
 *
 * المسوّق يطلب الصرف يدوياً → الأدمن يعالجه ويُحدّث الحالة.
 *
 * @property string              $id
 * @property string              $affiliate_id
 * @property float               $amount
 * @property string              $currency
 * @property PayoutMethod        $method
 * @property PayoutStatus        $status
 * @property string|null         $admin_notes
 * @property \Carbon\Carbon      $requested_at
 * @property \Carbon\Carbon|null $processed_at
 */
class ReferralPayout extends Model
{
    use HasUlids;

    protected $table = 'referral_payouts';

    protected $fillable = [
        'affiliate_id',
        'amount',
        'currency',
        'method',
        'status',
        'admin_notes',
        'requested_at',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'method'       => PayoutMethod::class,
            'status'       => PayoutStatus::class,
            'amount'       => 'decimal:2',
            'requested_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    // ==================== Relations ====================

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    // ==================== Scopes ====================

    public function scopeRequested($query)
    {
        return $query->where('status', PayoutStatus::Requested->value);
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', PayoutStatus::Processing->value);
    }

    public function scopePaid($query)
    {
        return $query->where('status', PayoutStatus::Paid->value);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [
            PayoutStatus::Requested->value,
            PayoutStatus::Processing->value,
        ]);
    }

    // ==================== Helpers ====================

    public function isProcessed(): bool
    {
        return $this->status === PayoutStatus::Paid;
    }

    public function isFinal(): bool
    {
        return $this->status->isFinal();
    }
}

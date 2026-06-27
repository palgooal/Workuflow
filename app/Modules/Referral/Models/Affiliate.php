<?php

namespace App\Modules\Referral\Models;

use App\Models\User;
use App\Modules\Referral\Enums\AffiliateStatus;
use App\Modules\Referral\Enums\AffiliateTier;
use App\Modules\Referral\Enums\PayoutMethod;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Affiliate — حساب المسوّق
 *
 * @property string              $id                  ULID
 * @property int|null            $user_id             bigint (users.id)
 * @property string              $name
 * @property string              $email
 * @property string|null         $whatsapp
 * @property string|null         $display_code        مثل AHMED2026
 * @property float               $commission_rate      النسبة الحالية
 * @property AffiliateStatus     $status
 * @property AffiliateTier       $tier
 * @property PayoutMethod|null   $payout_method
 * @property array|null          $payout_details      JSON
 * @property int                 $total_referrals     Denormalized
 * @property int                 $total_converted     Denormalized
 * @property float               $total_earned        Denormalized
 * @property float               $total_paid          Denormalized
 * @property float               $balance             Computed: total_earned - total_paid
 * @property string|null         $notes
 * @property \Carbon\Carbon|null $approved_at
 * @property \Carbon\Carbon|null $suspended_at
 */
class Affiliate extends Model
{
    use HasUlids;

    protected $table = 'affiliates';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'whatsapp',
        'display_code',
        'commission_rate',
        'status',
        'tier',
        'payout_method',
        'payout_details',
        'total_referrals',
        'total_converted',
        'total_earned',
        'total_paid',
        'notes',
        'approved_at',
        'suspended_at',
    ];

    protected function casts(): array
    {
        return [
            'status'          => AffiliateStatus::class,
            'tier'            => AffiliateTier::class,
            'payout_method'   => PayoutMethod::class,
            'payout_details'  => 'array',
            'commission_rate' => 'decimal:2',
            'total_earned'    => 'decimal:2',
            'total_paid'      => 'decimal:2',
            'approved_at'     => 'datetime',
            'suspended_at'    => 'datetime',
        ];
    }

    // ==================== Accessors ====================

    /**
     * الرصيد المتاح للصرف = total_earned − total_paid
     * لا يُخزَّن في قاعدة البيانات تجنباً للتباين (راجع §3.1)
     */
    protected function balance(): Attribute
    {
        return Attribute::make(
            get: fn () => round(
                (float) $this->total_earned - (float) $this->total_paid,
                2
            ),
        );
    }

    // ==================== Relations ====================

    /** المستخدم صاحب حساب المسوّق (اختياري — يمكن أن يكون مسوّق خارجي) */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** سجلات النقرات على روابط هذا المسوّق */
    public function clicks(): HasMany
    {
        return $this->hasMany(ReferralClick::class);
    }

    /** العمولات المستحقة لهذا المسوّق */
    public function commissions(): HasMany
    {
        return $this->hasMany(ReferralCommission::class);
    }

    /** طلبات الصرف لهذا المسوّق */
    public function payouts(): HasMany
    {
        return $this->hasMany(ReferralPayout::class);
    }

    // ==================== Scopes ====================

    public function scopeActive($query)
    {
        return $query->where('status', AffiliateStatus::Active->value);
    }

    public function scopePending($query)
    {
        return $query->where('status', AffiliateStatus::Pending->value);
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', AffiliateStatus::Suspended->value);
    }

    public function scopeByTier($query, AffiliateTier $tier)
    {
        return $query->where('tier', $tier->value);
    }

    // ==================== Helpers ====================

    public function isActive(): bool
    {
        return $this->status === AffiliateStatus::Active;
    }

    public function isPending(): bool
    {
        return $this->status === AffiliateStatus::Pending;
    }

    public function isSuspended(): bool
    {
        return $this->status === AffiliateStatus::Suspended;
    }

    /** الرابط الأساسي للإحالة عبر ULID */
    public function referralUrl(): string
    {
        return url('/ref/' . $this->id);
    }

    /** الرابط التسويقي عبر display_code (إن وُجد) */
    public function displayCodeUrl(): ?string
    {
        return $this->display_code
            ? url('/ref/' . $this->display_code)
            : null;
    }
}

<?php

namespace App\Modules\Referral\Models;

use App\Models\Subscription;
use App\Models\User;
use App\Modules\Referral\Enums\CommissionStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ReferralCommission — عمولة إحالة
 *
 * تُنشَأ مرة واحدة لكل اشتراك أول (UNIQUE subscription_id).
 * لا عمولات على التجديد — محمي بـ isFirstActivation guard في Listener.
 *
 * @property string              $id
 * @property string              $affiliate_id
 * @property string              $subscription_id     FK → subscriptions.id (ULID)
 * @property int                 $referred_user_id    FK → users.id (bigint)
 * @property float               $amount              قيمة العمولة = subscription_amount × rate/100
 * @property string              $currency
 * @property float               $rate                النسبة وقت الإنشاء
 * @property float               $subscription_amount قيمة الاشتراك الأصلية
 * @property string              $subscription_plan
 * @property string              $subscription_cycle
 * @property CommissionStatus    $status
 * @property string              $trigger_source      togo_callback | manual_admin
 * @property bool                $fraud_flagged
 * @property \Carbon\Carbon|null $approved_at
 * @property \Carbon\Carbon|null $paid_at
 * @property string|null         $notes
 */
class ReferralCommission extends Model
{
    use HasUlids;

    protected $table = 'referral_commissions';

    protected $fillable = [
        'affiliate_id',
        'subscription_id',
        'referred_user_id',
        'amount',
        'currency',
        'rate',
        'subscription_amount',
        'subscription_plan',
        'subscription_cycle',
        'status',
        'trigger_source',
        'fraud_flagged',
        'approved_at',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status'               => CommissionStatus::class,
            'amount'               => 'decimal:2',
            'rate'                 => 'decimal:2',
            'subscription_amount'  => 'decimal:2',
            'fraud_flagged'        => 'boolean',
            'approved_at'          => 'datetime',
            'paid_at'              => 'datetime',
        ];
    }

    // ==================== Relations ====================

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /** المستخدم المُحال الذي اشترك */
    public function referredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    // ==================== Scopes ====================

    public function scopePending($query)
    {
        return $query->where('status', CommissionStatus::Pending->value);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', CommissionStatus::Approved->value);
    }

    public function scopePaid($query)
    {
        return $query->where('status', CommissionStatus::Paid->value);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', CommissionStatus::Rejected->value);
    }

    /** العمولات الموسومة بالاحتيال — تستوجب مراجعة يدوية */
    public function scopeFraud($query)
    {
        return $query->where('fraud_flagged', true);
    }

    /**
     * العمولات التي تُحتسب ضمن total_converted
     * (pending + approved + paid) — للترقية الفورية للمستوى (راجع §15)
     */
    public function scopeCountsForTier($query)
    {
        return $query->whereIn('status', [
            CommissionStatus::Pending->value,
            CommissionStatus::Approved->value,
            CommissionStatus::Paid->value,
        ]);
    }

    /**
     * العمولات التي تُحتسب ضمن total_earned
     * (approved + paid فقط) — للرصيد المالي الدقيق (راجع §15)
     */
    public function scopeCountsForEarnings($query)
    {
        return $query->whereIn('status', [
            CommissionStatus::Approved->value,
            CommissionStatus::Paid->value,
        ]);
    }

    // ==================== Helpers ====================

    public function isPending(): bool
    {
        return $this->status === CommissionStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status === CommissionStatus::Approved;
    }

    public function isFraudFlagged(): bool
    {
        return $this->fraud_flagged === true;
    }
}

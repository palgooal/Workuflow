<?php

namespace App\Modules\Referral\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * ReferralClick — سجل نقرة على رابط إحالة
 *
 * سجل ثابت — لا يُعدَّل بعد الإنشاء، لذلك لا updated_at.
 * الربط بالمستخدم يتم من الجهة الأخرى: users.referral_click_id → referral_clicks.id
 *
 * @property string              $id              ULID
 * @property string              $affiliate_id    FK → affiliates.id
 * @property string              $visitor_token   ULID من Cookie (راجع §4.1)
 * @property string|null         $ip_address      للكشف عن Fraud فقط
 * @property string|null         $user_agent
 * @property string|null         $landing_page
 * @property \Carbon\Carbon|null $converted_at    وقت التسجيل/الاشتراك
 * @property \Carbon\Carbon      $created_at
 */
class ReferralClick extends Model
{
    use HasUlids;

    protected $table = 'referral_clicks';

    // لا updated_at — سجل ثابت لا يُعدَّل
    const UPDATED_AT = null;

    protected $fillable = [
        'affiliate_id',
        'visitor_token',
        'ip_address',
        'user_agent',
        'landing_page',
        'converted_at',
    ];

    protected function casts(): array
    {
        return [
            'converted_at' => 'datetime',
            'created_at'   => 'datetime',
        ];
    }

    // ==================== Relations ====================

    /** المسوّق صاحب رابط الإحالة */
    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    /**
     * المستخدم الذي أتمّ التسجيل بعد هذه النقرة
     * العلاقة معكوسة: users.referral_click_id = referral_clicks.id
     */
    public function referredUser(): HasOne
    {
        return $this->hasOne(User::class, 'referral_click_id');
    }

    // ==================== Scopes ====================

    /** النقرات التي أفضت لتسجيل مستخدم */
    public function scopeConverted($query)
    {
        return $query->whereNotNull('converted_at');
    }

    /** النقرات التي لم تتحوّل بعد */
    public function scopePending($query)
    {
        return $query->whereNull('converted_at');
    }

    // ==================== Helpers ====================

    public function isConverted(): bool
    {
        return $this->converted_at !== null;
    }
}

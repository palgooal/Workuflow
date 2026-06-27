<?php

namespace App\Modules\Referral\Services;

use App\Models\User;
use App\Modules\Referral\Models\Affiliate;
use App\Modules\Referral\Models\ReferralPayout;
use Illuminate\Support\Facades\Log;

/**
 * ReferralService — أوركسترا نظام الإحالات
 *
 * مسؤولياته:
 *  1. حل معرّف المسوّق (ULID أو display_code)
 *  2. ربط تسجيل المستخدم بالمسوّق (Attribution: first-affiliate-wins)
 *  3. حساب العمولة ونسبتها
 *  4. التحقق من أهلية طلب الصرف
 *
 * ⚠️  لا يُنشئ العمولة مباشرة — هذا دور CreateReferralCommissionAction
 *     الذي يُستدعى من Listener بعد SubscriptionActivated (راجع §5)
 */
class ReferralService
{
    public function __construct(
        private readonly FraudDetectionService $fraudService,
    ) {}

    /**
     * حل معرّف المسوّق: ULID (26 حرف) أو display_code
     *
     * يُرجع null إن:
     * - المعرّف غير موجود في DB
     * - المسوّق غير نشط (status != 'active')
     */
    public function resolveAffiliate(string $identifier): ?Affiliate
    {
        // ULID = 26 حرف بالضبط
        if (strlen($identifier) === 26) {
            return Affiliate::where('id', $identifier)
                ->where('status', 'active')
                ->first();
        }

        // display_code: AHMED2026 وما شابه (غير حساس لحالة الأحرف)
        return Affiliate::where('display_code', strtoupper($identifier))
            ->where('status', 'active')
            ->first();
    }

    /**
     * ربط تسجيل المستخدم بالمسوّق (Attribution)
     *
     * القواعد:
     *  - First-affiliate-wins: إذا كان للمستخدم مسوّق مسبق لا يُغيَّر
     *  - Self-referral يُرفَض بصمت ويُسجَّل
     *  - يُحدَّث: users.referred_by_affiliate_id + referral_click.converted_at + affiliates.total_referrals
     *
     * @param User   $user        المستخدم الجديد
     * @param string $affiliateId ULID المسوّق
     * @param string $clickId     ULID سجل النقرة
     */
    public function attributeRegistration(
        User   $user,
        string $affiliateId,
        string $clickId,
    ): void {
        // First-affiliate-wins: لا تُعيَّن إحالة ثانية
        if ($user->referred_by_affiliate_id !== null) {
            return;
        }

        $affiliate = Affiliate::find($affiliateId);

        if (! $affiliate || ! $affiliate->isActive()) {
            return;
        }

        // Self-referral guard
        if ($this->fraudService->detectSelfReferral($affiliate, $user)) {
            return;
        }

        // ربط المستخدم بالمسوّق والنقرة
        $user->update([
            'referred_by_affiliate_id' => $affiliateId,
            'referral_click_id'        => $clickId,
            'referral_attributed_at'   => now(),
        ]);

        // تسجيل وقت التحويل في referral_clicks
        \App\Modules\Referral\Models\ReferralClick::where('id', $clickId)
            ->update(['converted_at' => now()]);

        // تحديث العداد الفوري (يُصحَّح يومياً بـ referral:reconcile)
        $affiliate->increment('total_referrals');

        Log::info('Referral: attribution set', [
            'user_id'      => $user->id,
            'affiliate_id' => $affiliateId,
            'click_id'     => $clickId,
        ]);
    }

    /**
     * تحديد نسبة العمولة الحالية للمسوّق
     *
     * يُرجع commission_rate المخزَّنة في DB (تُحدَّث عند ترقية الـ Tier)
     */
    public function resolveCommissionRate(Affiliate $affiliate): float
    {
        return (float) $affiliate->commission_rate;
    }

    /**
     * حساب قيمة العمولة
     *
     * amount = round(subscriptionAmount × rate / 100, 2)
     */
    public function calculateCommission(float $subscriptionAmount, float $rate): float
    {
        return round($subscriptionAmount * $rate / 100, 2);
    }

    /**
     * هل المسوّق مؤهَّل لطلب صرف؟
     *
     * الشروط:
     *  - الرصيد (balance) >= 20 USD
     *  - لا يوجد طلب صرف بحالة 'requested' أو 'processing'
     */
    public function canRequestPayout(Affiliate $affiliate): bool
    {
        if ((float) $affiliate->balance < 20.00) {
            return false;
        }

        return ! ReferralPayout::where('affiliate_id', $affiliate->id)
            ->whereIn('status', ['requested', 'processing'])
            ->exists();
    }
}

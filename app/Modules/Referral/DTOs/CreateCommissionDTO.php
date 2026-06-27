<?php

namespace App\Modules\Referral\DTOs;

/**
 * CreateCommissionDTO — بيانات إنشاء عمولة إحالة
 *
 * DTO = Data Transfer Object: نقل بيانات فقط، لا سلوك.
 * مصدره: CreateReferralCommission Listener (المرحلة 5)
 *
 * ⚠️  referredUserId: int (bigint) — users.id في هذا المشروع ليس ULID
 * ⚠️  affiliateId / subscriptionId: string ULID — من جداول الإحالات
 */
final readonly class CreateCommissionDTO
{
    public function __construct(
        public string  $affiliateId,          // ULID — affiliates.id
        public string  $subscriptionId,       // ULID — subscriptions.id
        public int     $referredUserId,       // bigint — users.id
        public float   $amount,               // قيمة العمولة المحسوبة
        public float   $rate,                 // النسبة وقت الإنشاء
        public string  $triggerSource,        // 'togo_callback' | 'manual_admin'
        public float   $subscriptionAmount,   // قيمة الاشتراك الأصلية
        public string  $subscriptionPlan,     // 'pro' | 'business'
        public string  $subscriptionCycle,    // 'monthly' | 'annual'
        public bool    $fraudFlagged = false,
        public string  $currency     = 'USD',
    ) {}

    public function toArray(): array
    {
        return [
            'affiliate_id'        => $this->affiliateId,
            'subscription_id'     => $this->subscriptionId,
            'referred_user_id'    => $this->referredUserId,
            'amount'              => $this->amount,
            'currency'            => $this->currency,
            'rate'                => $this->rate,
            'subscription_amount' => $this->subscriptionAmount,
            'subscription_plan'   => $this->subscriptionPlan,
            'subscription_cycle'  => $this->subscriptionCycle,
            'trigger_source'      => $this->triggerSource,
            'fraud_flagged'       => $this->fraudFlagged ? 1 : 0,
            'status'              => 'pending',  // دائماً pending عند الإنشاء — 7 أيام مراجعة
        ];
    }
}

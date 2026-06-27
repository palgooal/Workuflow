<?php

namespace App\Modules\Billing\Events;

use App\Models\Subscription;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * SubscriptionActivated — يُطلَق عند تفعيل اشتراك مدفوع جديد
 *
 * ⚠️  هذا الحدث خاص بالتفعيل الأول أو بقرار الأدمن اليدوي.
 *     التجديد (renewal) له حدث منفصل: SubscriptionRenewed — لا تُربط بهذا.
 *
 * المستمعون المتوقعون:
 *   - CreateReferralCommission (Referral Module) — يُنشئ عمولة الإحالة
 *     (يتحقق من isFirstActivation داخلياً — لا عمولة على التجديد)
 *
 * @param Subscription $subscription     سجل الاشتراك المُفعَّل
 * @param bool         $isFirstActivation true = تفعيل أول مدفوع / false = إعادة تفعيل أو أدمن
 * @param string       $triggerSource     'togo_callback' | 'manual_admin'
 * @param string       $cycle             'monthly' | 'annual'
 */
class SubscriptionActivated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Subscription $subscription,
        public readonly bool         $isFirstActivation = true,
        public readonly string       $triggerSource     = 'togo_callback',
        public readonly string       $cycle             = 'monthly',
    ) {}
}

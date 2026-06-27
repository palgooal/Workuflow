<?php

namespace App\Modules\Referral\Listeners;

use App\Modules\Billing\Events\SubscriptionActivated;
use App\Modules\Referral\Actions\Commission\CreateReferralCommissionAction;
use App\Modules\Referral\DTOs\CreateCommissionDTO;
use App\Modules\Referral\Models\ReferralCommission;
use App\Modules\Referral\Services\FraudDetectionService;
use App\Modules\Referral\Services\ReferralService;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\Referral\Notifications\FraudFlaggedNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as NotificationFacade;

/**
 * CreateReferralCommission — Listener لإنشاء عمولة الإحالة بعد الاشتراك الأول
 *
 * يُطلَق بواسطة: SubscriptionActivated Event (Billing Module)
 * Queue: 'referrals' — مستقلة عن 'billing' لمنع التأخير المتبادل (راجع §16)
 * $afterCommit = true: يضمن وجود سجل الاشتراك في DB قبل محاولة ربط العمولة
 *
 * سلسلة Guards (بالترتيب):
 *  [1] isFirstActivation    — لا عمولة على التجديد
 *  [2] referredByAffiliate  — المستخدم جاء عبر إحالة
 *  [3] commission exists    — دفاع ثانٍ فوق DB UNIQUE (حماية من retry storms)
 *
 * ثم: Fraud check → حساب العمولة → CreateReferralCommissionAction (التي تُحدّث التier)
 */
class CreateReferralCommission implements ShouldQueue
{
    /** يُنفَّذ بعد commit الـ Transaction — يضمن وجود Subscription في DB */
    public bool $afterCommit = true;

    /** Queue مستقلة — لا تُوقف billing عند بطء معالجة العمولات */
    public string $queue = 'referrals';

    /** عدد المحاولات عند الفشل */
    public int $tries = 3;

    /** ثواني الانتظار بين المحاولات (exponential: 60, 120, 240) */
    public int $backoff = 60;

    public function __construct(
        private readonly ReferralService       $referralService,
        private readonly FraudDetectionService $fraudService,
    ) {}

    public function handle(SubscriptionActivated $event): void
    {
        // ── [GUARD 1] تجديد وليس تفعيلاً أولاً ─────────────────────────
        if (! $event->isFirstActivation) {
            return;
        }

        $subscription = $event->subscription;

        // تحميل المستخدم مع العلاقات المطلوبة
        $user = $subscription->user()->with('referredByAffiliate')->first();

        if (! $user) {
            Log::warning('CreateReferralCommission: subscription has no user', [
                'subscription_id' => $subscription->id,
            ]);
            return;
        }

        // ── [GUARD 2] المستخدم غير مُحال لأي مسوّق ──────────────────────
        $affiliate = $user->referredByAffiliate;

        if (! $affiliate) {
            return;
        }

        // ── [GUARD 3] عمولة موجودة مسبقاً (حماية من Queue retry storms) ─
        // DB UNIQUE INDEX هو الدفاع الأساسي، هذا Guard ثانٍ يوفر الوقت
        if (ReferralCommission::where('subscription_id', $subscription->id)->exists()) {
            Log::info('CreateReferralCommission: commission already exists (idempotent)', [
                'subscription_id' => $subscription->id,
                'affiliate_id'    => $affiliate->id,
            ]);
            return;
        }

        // ── Fraud Check ──────────────────────────────────────────────────
        $fraudResult = $this->fraudService->detectSuspiciousConversions($affiliate, $user);

        // ── حساب قيمة الاشتراك من config (Subscription لا يخزّن amount) ─
        $planValue          = $subscription->plan->value;  // 'pro' | 'business'
        $cycle              = $event->cycle;               // 'monthly' | 'annual'
        $subscriptionAmount = (float) config(
            "billing.plans.{$planValue}.{$cycle}.price",
            0
        );

        // ── حساب العمولة ─────────────────────────────────────────────────
        $rate   = $this->referralService->resolveCommissionRate($affiliate);
        $amount = $this->referralService->calculateCommission($subscriptionAmount, $rate);

        // ── إنشاء العمولة (داخله: increment total_converted + UpgradeTier) ─
        $commission = app(CreateReferralCommissionAction::class)->execute(
            new CreateCommissionDTO(
                affiliateId:        $affiliate->id,
                subscriptionId:     $subscription->id,
                referredUserId:     $user->id,
                amount:             $amount,
                rate:               $rate,
                triggerSource:      $event->triggerSource,
                subscriptionAmount: $subscriptionAmount,
                subscriptionPlan:   $planValue,
                subscriptionCycle:  $cycle,
                fraudFlagged:       $fraudResult->isFlagged,
            )
        );

        // إشعار الأدمن عند رصد نشاط مشبوه
        if ($fraudResult->isFlagged) {
            NotificationFacade::route('mail', config('referral.admin_email'))
                ->notify(new FraudFlaggedNotification(
                    affiliate:  $affiliate,
                    commission: $commission,
                    reasons:    $fraudResult->reasons,
                ));
        }
    }

    /**
     * معالجة فشل الـ Job بعد استنفاد المحاولات
     */
    public function failed(SubscriptionActivated $event, \Throwable $exception): void
    {
        Log::error('CreateReferralCommission: all retries exhausted', [
            'subscription_id'   => $event->subscription->id,
            'is_first'          => $event->isFirstActivation,
            'trigger_source'    => $event->triggerSource,
            'error'             => $exception->getMessage(),
        ]);
    }
}

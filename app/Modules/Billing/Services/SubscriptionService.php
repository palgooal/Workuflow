<?php

namespace App\Modules\Billing\Services;

use App\Models\Subscription;
use App\Models\User;
use App\Support\Enums\SubscriptionPlan;

/**
 * SubscriptionService — إدارة خطط الاشتراك
 *
 * لا يعتمد على مزود دفع بعينه.
 * عند إضافة مزود: نفّذ PaymentProviderInterface وسجّله في AppServiceProvider،
 * ثم أضف استدعاءاته في BillingController مباشرة.
 */
class SubscriptionService
{
    /**
     * تفعيل خطة مدفوعة للمستخدم (يُستدعى بعد تأكيد الدفع من الـ Webhook)
     */
    public function activatePlan(
        User    $user,
        string  $planValue,
        ?string $providerSubscriptionId = null,
        string  $cycle = 'monthly',
    ): Subscription {
        $plan = SubscriptionPlan::tryFrom($planValue) ?? SubscriptionPlan::Pro;

        // ── تحديث خطة المستخدم ──────────────────────────────────────────
        $userUpdate = ['subscription_plan' => $plan];

        // ── CONVERSION-01: ترقية فترة السماح من 30 دقيقة (ما قبل الدفع) إلى 7 أيام (ما بعده) ──
        //
        // السياق:
        //   RegisterUserAction منح فترة سماح مؤقتة 30 دقيقة (pre-payment grace) تُتيح
        //   للمستخدم الوصول لصفحة الدفع رغم أن بريده غير موثَّق.
        //   grace_used_at ظلّ null طوال تلك الفترة — لأن الدفع لم يتأكد بعد.
        //
        // الآن (بعد تأكيد الدفع):
        //   نُرقّي grace_until من 30 دقيقة إلى 7 أيام كاملة ونضبط grace_used_at
        //   كعلامة دائمة تمنع منح فترة سماح ثانية مدى الحياة.
        //
        // الشروط:
        //   (1) البريد غير موثَّق بعد (email_verified_at = null)
        //   (2) لم يسبق ضبط grace_used_at — أي لم تُمنح فترة السماح الرسمية من قبل
        if ($user->email_verified_at === null && ! $user->hasUsedEmailVerificationGrace()) {
            $userUpdate['email_verification_grace_until']   = now()->addDays(7);
            $userUpdate['email_verification_grace_used_at'] = now(); // one-time lifetime flag
        }

        $user->update($userUpdate);

        // مدة الاشتراك تعتمد على الدورة المدفوعة
        $endsAt = match ($cycle) {
            'annual'  => now()->addYear(),
            default   => now()->addMonth(),
        };

        return Subscription::updateOrCreate(
            array_filter(['provider_subscription_id' => $providerSubscriptionId]),
            [
                'user_id'                  => $user->id,
                'plan'                     => $plan,
                'status'                   => 'active',
                'payment_provider'         => config('billing.provider', 'manual'),
                'starts_at'                => now(),
                'ends_at'                  => $endsAt,
            ]
        );
    }

    /**
     * إلغاء الاشتراك وإرجاع المستخدم للخطة المجانية
     */
    public function cancelPlan(User $user, ?string $providerSubscriptionId = null): void
    {
        $user->update(['subscription_plan' => SubscriptionPlan::Free]);

        $query = Subscription::where('user_id', $user->id)->active();

        if ($providerSubscriptionId) {
            $query->where('provider_subscription_id', $providerSubscriptionId);
        }

        $query->update(['status' => 'cancelled', 'ends_at' => now()]);
    }

    /**
     * الاشتراك النشط الحالي للمستخدم
     */
    public function getCurrentSubscription(User $user): ?Subscription
    {
        return $user->subscriptions()->active()->latest()->first();
    }

    /**
     * الأسعار المعروضة في صفحة Pricing
     */
    public function getPlanPrices(): array
    {
        return config('billing.plans', []);
    }

    /**
     * تمديد الاشتراك النشط شهراً إضافياً (يُستدعى من Admin يدوياً)
     */
    public function extendPlan(User $user, int $months = 1): ?Subscription
    {
        $subscription = $this->getCurrentSubscription($user);

        if (! $subscription) {
            // لا يوجد اشتراك نشط — أنشئ واحداً
            return $this->activatePlan($user, $user->subscription_plan?->value ?? 'pro');
        }

        $newEndsAt = ($subscription->ends_at && $subscription->ends_at->isFuture())
            ? $subscription->ends_at->addMonths($months)
            : now()->addMonths($months);

        $subscription->update(['ends_at' => $newEndsAt]);

        return $subscription->fresh();
    }

    /**
     * إعادة تفعيل اشتراك ملغى أو منتهي (يُستدعى من Admin يدوياً)
     *
     * يُعيد تفعيل آخر سجل اشتراك للمستخدم بدلاً من إنشاء سجل جديد.
     * إذا لم يوجد سجل أصلاً يُنشئ واحداً بخطة المستخدم الحالية.
     */
    public function reactivatePlan(User $user, int $months = 1): Subscription
    {
        $subscription = Subscription::where('user_id', $user->id)->latest()->first();

        if ($subscription) {
            $subscription->update([
                'status'    => 'active',
                'starts_at' => now(),
                'ends_at'   => now()->addMonths($months),
            ]);

            // تأكد أن user.subscription_plan يطابق الخطة المُعاد تفعيلها
            $user->update(['subscription_plan' => $subscription->plan]);

            return $subscription->fresh();
        }

        // لا يوجد سجل — أنشئ جديداً بخطة Pro افتراضياً
        return $this->activatePlan(
            $user,
            $user->subscription_plan?->value ?? SubscriptionPlan::Pro->value,
        );
    }

    /**
     * تخفيض المستخدم للخطة المجانية (يُستدعى من Admin يدوياً)
     *
     * يُلغي الاشتراك النشط ويُحدّث خطة المستخدم لـ Free.
     */
    public function downgradePlan(User $user): void
    {
        $user->update(['subscription_plan' => SubscriptionPlan::Free]);

        Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->update([
                'plan'    => SubscriptionPlan::Free->value,
                'status'  => 'cancelled',
                'ends_at' => now(),
            ]);
    }

    /**
     * هل مزود الدفع مفعّل؟
     */
    public function isPaymentProviderConfigured(): bool
    {
        return ! empty(config('billing.provider'));
    }
}

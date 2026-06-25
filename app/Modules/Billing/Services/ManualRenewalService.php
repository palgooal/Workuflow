<?php

namespace App\Modules\Billing\Services;

use App\Models\Subscription;
use App\Models\User;
use App\Modules\Billing\Contracts\RenewalServiceInterface;
use App\Modules\Billing\Events\SubscriptionRenewed;
use App\Notifications\SubscriptionExpiringNotification;
use Illuminate\Support\Facades\Log;

/**
 * ManualRenewalService — التجديد اليدوي للاشتراكات
 *
 * يُستخدَم من:
 *   - Admin في Filament (extend month / reactivate)
 *   - SubscriptionService::extendPlan() / reactivatePlan()
 *
 * التجديد التلقائي (AutoRenewalService) سيُنفَّذ مستقبلاً
 * عند ربط بوابة دفع تدعم الاشتراكات المتكررة (recurring billing).
 */
class ManualRenewalService implements RenewalServiceInterface
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function renew(User $user, string $cycle = 'monthly', int $periods = 1): Subscription
    {
        $months = match ($cycle) {
            'annual' => $periods * 12,
            default  => $periods,
        };

        $subscription = $this->subscriptionService->extendPlan($user, $months);

        if (! $subscription) {
            throw new \RuntimeException("لا يوجد اشتراك نشط للمستخدم [{$user->id}] لتجديده.");
        }

        // أطلق حدث التجديد الناجح
        event(new SubscriptionRenewed(
            subscription: $subscription,
            cycle:        $cycle,
            amount:       0, // يدوي — لا يوجد مبلغ محدد
            currency:     'USD',
        ));

        Log::info('ManualRenewalService: subscription renewed', [
            'user_id'         => $user->id,
            'subscription_id' => $subscription->id,
            'cycle'           => $cycle,
            'periods'         => $periods,
            'months_added'    => $months,
            'new_ends_at'     => $subscription->ends_at,
        ]);

        return $subscription;
    }

    /**
     * {@inheritdoc}
     *
     * التجديد التلقائي غير مدعوم في هذا المزوِّد.
     * يُعيد دائماً false — يُفعَّل عند دمج AutoRenewalService.
     */
    public function isEligibleForAutoRenewal(Subscription $subscription): bool
    {
        // TODO: يُفعَّل عند ربط بوابة دفع تدعم التجديد التلقائي
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function sendRenewalReminder(Subscription $subscription, int $daysLeft): void
    {
        $user = $subscription->user;

        if (! $user) {
            Log::warning('ManualRenewalService::sendRenewalReminder: no user found', [
                'subscription_id' => $subscription->id,
            ]);
            return;
        }

        $user->notify(new SubscriptionExpiringNotification($subscription, $daysLeft));

        Log::info('ManualRenewalService: renewal reminder sent', [
            'user_id'         => $user->id,
            'subscription_id' => $subscription->id,
            'days_left'       => $daysLeft,
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * Grace Period: تمديد فترة السماح دون تفعيل اشتراك جديد.
     * يُبقي على الاشتراك "نشطاً" N أيام إضافية بعد انتهائه الطبيعي.
     *
     * الاستخدام: يُستدعى من ExpireSubscriptions قبل التخفيض الفعلي
     * عند تفعيل هذه الميزة مستقبلاً.
     */
    public function applyGracePeriod(Subscription $subscription, int $graceDays = 3): Subscription
    {
        // تمديد ends_at بمقدار graceDays من اليوم (ليس من ends_at المنتهي)
        $newEndsAt = now()->addDays($graceDays);

        $subscription->update([
            'ends_at' => $newEndsAt,
            // لا تُغيِّر status — يبقى 'active' خلال فترة السماح
        ]);

        Log::info('ManualRenewalService: grace period applied', [
            'subscription_id' => $subscription->id,
            'user_id'         => $subscription->user_id,
            'grace_days'      => $graceDays,
            'new_ends_at'     => $newEndsAt,
        ]);

        return $subscription->fresh();
    }
}

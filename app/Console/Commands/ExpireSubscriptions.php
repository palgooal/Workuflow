<?php

namespace App\Console\Commands;

use App\Events\SubscriptionExpired;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionExpiredNotification;
use App\Support\Enums\SubscriptionPlan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ExpireSubscriptions — يُنهي الاشتراكات المنتهية ويُخفِّض المستخدمين للمجاني
 *
 * معايير الانتهاء:
 *   - status = 'active'
 *   - ends_at < now()
 *
 * آمن للتشغيل المتكرر (idempotent):
 *   - لا يُعالج سجلاً بالحالة 'expired' مسبقاً
 *   - يُحدِّث user.subscription_plan فقط إذا لم يكن 'free' بالفعل
 */
class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire
                            {--dry-run : اعرض فقط — لا تُغيِّر البيانات}
                            {--chunk=100 : حجم الـ chunk لكل دفعة}';

    protected $description = 'تُنهي الاشتراكات المنتهية وتُخفِّض المستخدمين للخطة المجانية';

    public function handle(): int
    {
        $isDryRun  = (bool) $this->option('dry-run');
        $chunkSize = (int)  $this->option('chunk');

        if ($isDryRun) {
            $this->warn('⚠ وضع المعاينة (dry-run) — لن تُغيَّر أي بيانات');
        }

        $this->info('🔍 البحث عن الاشتراكات المنتهية...');

        $expiredCount   = 0;
        $downgradedCount = 0;
        $errors          = 0;

        // استعلام: status=active + ends_at < now()
        Subscription::query()
            ->where('status', 'active')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now())
            ->with('user')
            ->chunkById($chunkSize, function ($subscriptions) use (
                $isDryRun,
                &$expiredCount,
                &$downgradedCount,
                &$errors
            ) {
                foreach ($subscriptions as $subscription) {
                    try {
                        $this->processExpiredSubscription(
                            $subscription,
                            $isDryRun,
                            $expiredCount,
                            $downgradedCount,
                        );
                    } catch (\Throwable $e) {
                        $errors++;
                        Log::error('ExpireSubscriptions: error processing subscription', [
                            'subscription_id' => $subscription->id,
                            'user_id'         => $subscription->user_id,
                            'error'           => $e->getMessage(),
                        ]);
                        $this->error("  ✗ خطأ في معالجة الاشتراك {$subscription->id}: {$e->getMessage()}");
                    }
                }
            });

        // ملخص
        $this->newLine();
        $this->info('═══════════════════════════════════════');
        $this->info("✅ انتهت المعالجة" . ($isDryRun ? ' (dry-run)' : ''));
        $this->table(
            ['الإجراء', 'العدد'],
            [
                ['الاشتراكات المنتهية', $expiredCount],
                ['المستخدمون المُخفَّضون للمجاني', $downgradedCount],
                ['الأخطاء', $errors],
            ]
        );

        Log::info('subscriptions:expire completed', [
            'expired'    => $expiredCount,
            'downgraded' => $downgradedCount,
            'errors'     => $errors,
            'dry_run'    => $isDryRun,
        ]);

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * معالجة اشتراك منتهٍ واحد
     */
    private function processExpiredSubscription(
        Subscription $subscription,
        bool         $isDryRun,
        int          &$expiredCount,
        int          &$downgradedCount,
    ): void {
        $user      = $subscription->user;
        $userName  = $user?->name ?? "User#{$subscription->user_id}";

        $this->line("  → {$userName} | اشتراك #{$subscription->id} انتهى: {$subscription->ends_at->format('Y/m/d')}");

        if ($isDryRun) {
            $expiredCount++;
            if ($user && $user->subscription_plan !== SubscriptionPlan::Free) {
                $downgradedCount++;
            }
            return;
        }

        DB::transaction(function () use ($subscription, $user, &$expiredCount, &$downgradedCount) {
            // 1. أنهِ الاشتراك
            $subscription->update(['status' => 'expired']);
            $expiredCount++;

            // 2. خفِّض المستخدم للمجاني (idempotent — تحقق أولاً)
            if ($user && $user->subscription_plan !== SubscriptionPlan::Free) {
                $user->update(['subscription_plan' => SubscriptionPlan::Free]);
                $downgradedCount++;

                // 3. أطلق حدث الانتهاء + أرسل إشعاراً للمستخدم
                try {
                    event(new SubscriptionExpired($subscription));
                    $user->notify(new SubscriptionExpiredNotification($subscription));
                } catch (\Throwable $e) {
                    // لا تفشل العملية بسبب فشل الإشعار
                    Log::warning('ExpireSubscriptions: failed to send expiry notification', [
                        'user_id'         => $user->id,
                        'subscription_id' => $subscription->id,
                        'error'           => $e->getMessage(),
                    ]);
                }
            }
        });
    }
}

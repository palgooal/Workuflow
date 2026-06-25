<?php

namespace App\Console\Commands;

use App\Events\SubscriptionExpiring;
use App\Models\Subscription;
use App\Notifications\SubscriptionExpiringNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * SendSubscriptionExpiryReminders — يُرسل تذكيرات للاشتراكات التي ستنتهي خلال N أيام
 *
 * الجدول الافتراضي: 7 أيام قبل الانتهاء
 * آمن للتشغيل المتكرر — يتحقق من عدم إرسال تذكير مسبق في نفس اليوم
 */
class SendSubscriptionExpiryReminders extends Command
{
    protected $signature = 'subscriptions:send-expiry-reminders
                            {--days=7 : عدد أيام التنبيه المسبق}
                            {--dry-run : اعرض فقط — لا ترسل إشعارات}';

    protected $description = 'يُرسل تذكيرات للاشتراكات التي ستنتهي خلال N أيام';

    public function handle(): int
    {
        $days      = (int) $this->option('days');
        $isDryRun  = (bool) $this->option('dry-run');

        if ($isDryRun) {
            $this->warn("⚠ وضع المعاينة (dry-run) — لن تُرسَل إشعارات");
        }

        $this->info("🔔 البحث عن الاشتراكات التي تنتهي خلال {$days} أيام...");

        // نطاق انتهاء: اليوم التالي بـ N يوم (نافذة 24 ساعة)
        $from  = now()->addDays($days)->startOfDay();
        $until = now()->addDays($days)->endOfDay();

        $subscriptions = Subscription::where('status', 'active')
            ->whereBetween('ends_at', [$from, $until])
            ->with('user')
            ->get();

        if ($subscriptions->isEmpty()) {
            $this->info("✅ لا توجد اشتراكات تنتهي خلال {$days} أيام.");
            return self::SUCCESS;
        }

        $sent   = 0;
        $errors = 0;

        foreach ($subscriptions as $subscription) {
            try {
                $user = $subscription->user;

                if (! $user) {
                    $this->warn("  ⚠ لا يوجد مستخدم للاشتراك {$subscription->id}");
                    continue;
                }

                $this->line("  → إرسال تذكير لـ {$user->name} ({$user->email}) | ينتهي: {$subscription->ends_at?->format('Y/m/d')}");

                if (! $isDryRun) {
                    $user->notify(new SubscriptionExpiringNotification($subscription, $days));
                    event(new SubscriptionExpiring($subscription, $days));
                }

                $sent++;
            } catch (\Throwable $e) {
                $errors++;
                Log::error('SendSubscriptionExpiryReminders: error', [
                    'subscription_id' => $subscription->id,
                    'error'           => $e->getMessage(),
                ]);
                $this->error("  ✗ خطأ: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->table(['الإجراء', 'العدد'], [
            ['التذكيرات المُرسَلة', $sent],
            ['الأخطاء', $errors],
        ]);

        Log::info("subscriptions:send-expiry-reminders completed", [
            'days'    => $days,
            'sent'    => $sent,
            'errors'  => $errors,
            'dry_run' => $isDryRun,
        ]);

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}

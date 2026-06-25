<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiringNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Subscription $subscription,
        public readonly int          $daysLeft = 7,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    // ──────────────────────────────────────────────────────────────────────
    // Email
    // ──────────────────────────────────────────────────────────────────────
    public function toMail(object $notifiable): MailMessage
    {
        $planLabel  = $this->subscription->plan?->label() ?? $this->subscription->plan;
        $expiryDate = $this->subscription->ends_at?->format('Y/m/d');

        return (new MailMessage)
            ->subject("⏰ اشتراكك ينتهي خلال {$this->daysLeft} أيام — " . config('app.name'))
            ->greeting('مرحباً ' . ($notifiable->name ?? ''))
            ->line("اشتراكك في خطة **{$planLabel}** سينتهي بتاريخ **{$expiryDate}** (خلال {$this->daysLeft} أيام).")
            ->line('لتجنب انقطاع الخدمة يرجى تجديد اشتراكك.')
            ->action('تجديد الاشتراك الآن', route('billing.upgrade'))
            ->line('بعد انتهاء الاشتراك ستتحول تلقائياً للخطة المجانية مع احتفاظك بجميع بياناتك.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // In-app (database)
    // ──────────────────────────────────────────────────────────────────────
    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'subscription_expiring',
            'title'      => "⏰ اشتراكك ينتهي خلال {$this->daysLeft} أيام",
            'message'    => "خطة {$this->subscription->plan?->label()} تنتهي بتاريخ {$this->subscription->ends_at?->format('Y/m/d')}. جدِّد الآن للاستمرار.",
            'days_left'  => $this->daysLeft,
            'ends_at'    => $this->subscription->ends_at?->toIso8601String(),
            'plan'       => $this->subscription->plan?->value,
            'link'       => route('billing.upgrade'),
            'icon'       => '⏰',
        ];
    }
}

<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiredNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Subscription $subscription,
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
        $planLabel = $this->subscription->plan?->label() ?? $this->subscription->plan;

        return (new MailMessage)
            ->subject('اشتراكك انتهى — ' . config('app.name'))
            ->greeting('مرحباً ' . ($notifiable->name ?? ''))
            ->line("انتهى اشتراكك في خطة **{$planLabel}** وتم تحويلك تلقائياً للخطة المجانية.")
            ->line('جميع بياناتك محفوظة ويمكنك الترقية في أي وقت لاستعادة الوصول الكامل.')
            ->action('ترقية الاشتراك', route('billing.upgrade'))
            ->line('نتطلع لاستمرار خدمتك معنا.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // In-app (database)
    // ──────────────────────────────────────────────────────────────────────
    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'subscription_expired',
            'title'   => 'انتهى اشتراكك 📅',
            'message' => 'تم تحويلك للخطة المجانية. ترقيتك متاحة في أي وقت.',
            'plan'    => $this->subscription->plan?->value,
            'link'    => route('billing.upgrade'),
            'icon'    => '📅',
        ];
    }
}

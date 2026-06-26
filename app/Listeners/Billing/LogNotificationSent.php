<?php

namespace App\Listeners\Billing;

use App\Models\EmailLog;
use Illuminate\Notifications\Events\NotificationSent;

/**
 * يُسجِّل كل إشعار بريدي بيلنغ في email_logs تلقائياً.
 * يُستدعى من ActivityLogServiceProvider عبر NotificationSent event.
 */
class LogNotificationSent
{
    /** الإشعارات المطلوب تتبعها فقط (billing) */
    private const TRACKED = [
        \App\Notifications\PaymentSuccessfulNotification::class,
        \App\Notifications\PaymentFailedNotification::class,
        \App\Notifications\SubscriptionExpiringNotification::class,
        \App\Notifications\SubscriptionExpiredNotification::class,
        \App\Notifications\PaymentRetryNotification::class,
    ];

    public function handle(NotificationSent $event): void
    {
        // نتتبع قناة البريد فقط
        if ($event->channel !== 'mail') {
            return;
        }

        // نتتبع إشعارات billing فقط
        if (! in_array(get_class($event->notification), self::TRACKED, true)) {
            return;
        }

        try {
            EmailLog::success(
                notificationClass: get_class($event->notification),
                userId:            $event->notifiable->id ?? null,
                recipient:         $event->notifiable->email ?? 'unknown',
            );
        } catch (\Throwable) {
            // لا يُعطَّل الإرسال إذا فشل التسجيل
        }
    }
}

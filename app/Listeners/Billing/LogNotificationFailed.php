<?php

namespace App\Listeners\Billing;

use App\Models\EmailLog;
use Illuminate\Notifications\Events\NotificationFailed;

class LogNotificationFailed
{
    private const TRACKED = [
        \App\Notifications\PaymentSuccessfulNotification::class,
        \App\Notifications\PaymentFailedNotification::class,
        \App\Notifications\SubscriptionExpiringNotification::class,
        \App\Notifications\SubscriptionExpiredNotification::class,
        \App\Notifications\PaymentRetryNotification::class,
    ];

    public function handle(NotificationFailed $event): void
    {
        if ($event->channel !== 'mail') {
            return;
        }

        if (! in_array(get_class($event->notification), self::TRACKED, true)) {
            return;
        }

        try {
            $error = $event->data['exception'] ?? 'Unknown error';
            if ($error instanceof \Throwable) {
                $error = $error->getMessage();
            }

            EmailLog::failure(
                notificationClass: get_class($event->notification),
                userId:            $event->notifiable->id ?? null,
                recipient:         $event->notifiable->email ?? 'unknown',
                error:             (string) $error,
            );
        } catch (\Throwable) {
            // لا تُعطِّل الإشعار الأصلي
        }
    }
}

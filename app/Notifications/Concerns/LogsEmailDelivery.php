<?php

namespace App\Notifications\Concerns;

use App\Models\EmailLog;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * أضف هذا الـ trait لأي Notification يُرسل بريداً لتسجيل حالة الإرسال تلقائياً.
 *
 * يُغلِّف toMail() بـ try/catch ويكتب سجلاً في email_logs بعد كل إرسال.
 * لا يُعطِّل الإرسال إذا فشل التسجيل.
 */
trait LogsEmailDelivery
{
    /**
     * استدعَه Notification عند الإرسال — نُسجِّل ثم نُعيد MailMessage.
     * يُفترض أن الـ class يُعرِّف buildMailMessage(object $notifiable): MailMessage
     */
    protected function buildAndLogMail(object $notifiable): MailMessage
    {
        $mail = $this->buildMailMessage($notifiable);

        try {
            EmailLog::success(
                notificationClass: static::class,
                userId:            $notifiable->id ?? null,
                recipient:         $notifiable->email ?? (string) $notifiable->routeNotificationForMail() ?? 'unknown',
            );
        } catch (\Throwable) {
            // لا يُعطَّل الإرسال إذا فشل تسجيل الـ log
        }

        return $mail;
    }
}

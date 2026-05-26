<?php

namespace App\Modules\CRM\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * AutomationNotification — إشعار داخل التطبيق من الأتمتة
 * يُخزَّن في جدول notifications (Laravel DB channel)
 */
class AutomationNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $message,
        public readonly int    $clientId,
        public readonly string $clientName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message'     => $this->message,
            'client_id'   => $this->clientId,
            'client_name' => $this->clientName,
            'type'        => 'automation',
            'icon'        => '🤖',
        ];
    }
}

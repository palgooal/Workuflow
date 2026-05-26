<?php

namespace App\Modules\CRM\Automation;

use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

/**
 * SendNotificationAutomationAction — إرسال إشعار داخل التطبيق
 *
 * params: {
 *   "message": "تحذير: عميل خطر على الخروج",
 *   "icon":    "⚠️"   (اختياري)
 * }
 */
class SendNotificationAutomationAction extends BaseAutomationAction
{
    public static function type(): string  { return 'send_notification'; }
    public static function label(): string { return 'إرسال إشعار'; }

    public function execute(Client $client, int $userId, array $params = []): void
    {
        $user = User::find($userId);
        if (!$user) return;

        $message = $params['message'] ?? "تنبيه تلقائي بشأن العميل: {$client->name}";
        $icon    = $params['icon'] ?? '🔔';

        // استخدام Laravel Database Notifications
        $user->notify(new \App\Modules\CRM\Notifications\AutomationNotification(
            message: "{$icon} {$message}",
            clientId: $client->id,
            clientName: $client->name,
        ));

        Log::info("SendNotificationAutomationAction: notified user {$userId} for client {$client->id}");
    }
}

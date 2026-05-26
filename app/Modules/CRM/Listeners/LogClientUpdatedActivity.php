<?php

namespace App\Modules\CRM\Listeners;

use App\Modules\CRM\Actions\Activity\LogClientActivityAction;
use App\Modules\CRM\Enums\ActivityType;
use App\Modules\CRM\Events\ClientUpdated;

class LogClientUpdatedActivity
{
    public bool $afterCommit = true;

    public function __construct(
        private readonly LogClientActivityAction $logger,
    ) {}

    public function handle(ClientUpdated $event): void
    {
        $fields   = $event->changedFields();
        $clientId = $event->client->id;
        $userId   = $event->dto->userId ?? $event->client->user_id;

        // تسجيل تغيير الحالة بشكل مستقل إذا وُجد
        if (in_array('status', $fields, true)) {
            $from = $event->before['status'] ?? '—';
            $to   = $event->after['status']  ?? '—';

            $fromStr = $from instanceof \BackedEnum ? $from->value : (string) $from;
            $toStr   = $to   instanceof \BackedEnum ? $to->value   : (string) $to;

            $this->logger->statusChanged($clientId, $userId, $fromStr, $toStr);
        }

        // تسجيل الحقول الأخرى كـ field_updated
        $otherFields = array_diff($fields, ['status']);
        if (! empty($otherFields)) {
            $this->logger->execute(
                clientId:    $clientId,
                userId:      $userId,
                type:        ActivityType::FieldUpdated,
                description: 'تم تحديث: ' . implode(', ', $otherFields),
                metadata:    ['fields' => $otherFields],
            );
        }
    }
}

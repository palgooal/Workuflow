<?php

namespace App\Notifications;

use App\Modules\CRM\Models\ClientFollowUp;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FollowUpReminderNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly ClientFollowUp $followUp) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $typeLabels = [
            'call'    => 'مكالمة',
            'email'   => 'بريد إلكتروني',
            'meeting' => 'اجتماع',
            'task'    => 'مهمة',
            'other'   => 'متابعة',
        ];

        $typeLabel  = $typeLabels[$this->followUp->type ?? 'other'] ?? 'متابعة';
        $clientName = $this->followUp->client->name ?? 'عميل';
        $dueAt      = $this->followUp->due_at->translatedFormat('j M H:i');

        return [
            'type'          => 'follow_up_reminder',
            'title'         => "تذكير: {$typeLabel} مع {$clientName}",
            'message'       => "\"{$this->followUp->title}\" — مستحق في {$dueAt}",
            'follow_up_id'  => $this->followUp->id,
            'client_id'     => $this->followUp->client->public_id ?? null,
            'link'          => route('clients.follow-ups.index'),
            'icon'          => '📋',
        ];
    }
}

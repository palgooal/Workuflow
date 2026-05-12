<?php

namespace App\Notifications;

use App\Models\Debt;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DebtDueSoonNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Debt $debt) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $daysLeft = now()->diffInDays($this->debt->due_date, false);
        $daysText = $daysLeft === 0 ? 'اليوم' : "خلال {$daysLeft} " . ($daysLeft === 1 ? 'يوم' : 'أيام');

        return [
            'type'     => 'debt_due_soon',
            'title'    => 'دين يستحق قريباً',
            'message'  => "دين {$this->debt->type->label()} مع {$this->debt->party_name} يستحق {$daysText}",
            'amount'   => $this->debt->remaining_amount,
            'currency' => $this->debt->currency,
            'debt_id'  => $this->debt->id,
            'link'     => route('debts.index'),
            'icon'     => '⏰',
        ];
    }
}

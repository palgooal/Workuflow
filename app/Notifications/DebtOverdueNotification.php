<?php

namespace App\Notifications;

use App\Models\Debt;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DebtOverdueNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Debt $debt) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $daysLate = now()->diffInDays($this->debt->due_date);

        return [
            'type'     => 'debt_overdue',
            'title'    => 'دين متأخر عن السداد',
            'message'  => "دين {$this->debt->type->label()} مع {$this->debt->party_name} متأخر بـ {$daysLate} " . ($daysLate === 1 ? 'يوم' : 'أيام'),
            'amount'   => $this->debt->remaining_amount,
            'currency' => $this->debt->currency,
            'debt_id'  => $this->debt->id,
            'link'     => route('debts.index'),
            'icon'     => '🚨',
        ];
    }
}

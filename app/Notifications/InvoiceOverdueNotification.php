<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InvoiceOverdueNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Invoice $invoice) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $daysLate = now()->diffInDays($this->invoice->due_date);

        return [
            'type'       => 'invoice_overdue',
            'title'      => 'فاتورة متأخرة عن السداد',
            'message'    => "فاتورة {$this->invoice->number} للعميل {$this->invoice->client->name} متأخرة بـ {$daysLate} " . ($daysLate === 1 ? 'يوم' : 'أيام'),
            'amount'     => $this->invoice->total,
            'currency'   => $this->invoice->currency,
            'invoice_id' => $this->invoice->id,
            'link'       => route('invoices.show', $this->invoice->ulid),
            'icon'       => '🚨',
        ];
    }
}

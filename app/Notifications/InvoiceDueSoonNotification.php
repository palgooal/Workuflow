<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InvoiceDueSoonNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Invoice $invoice) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'invoice_due_soon',
            'title'      => 'فاتورة تستحق قريباً',
            'message'    => "فاتورة {$this->invoice->number} للعميل {$this->invoice->client->name} تستحق بتاريخ {$this->invoice->due_date?->format('Y/m/d')}",
            'amount'     => $this->invoice->total,
            'currency'   => $this->invoice->currency,
            'invoice_id' => $this->invoice->id,
            'link'       => route('invoices.show', $this->invoice->ulid),
            'icon'       => '⏰',
        ];
    }
}

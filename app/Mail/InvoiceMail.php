<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Invoice $invoice,
        public readonly string  $senderName,
    ) {}

    public function envelope(): Envelope
    {
        $tpl     = $this->resolveTemplate();
        $subject = $tpl['subject'] ?? 'فاتورة ' . $this->invoice->number;

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $tpl = $this->resolveTemplate();

        return new Content(
            view: 'emails.template',
            with: ['body' => $tpl['body']],
        );
    }

    // ── استبدال المتغيرات ─────────────────────────────────────────────

    private function resolveTemplate(): array
    {
        $invoice    = $this->invoice;
        $dueDate    = $invoice->due_date?->format('d/m/Y') ?? '—';
        $isOverdue  = $invoice->isOverdue();
        $dueColor   = $isOverdue ? '#dc2626' : '#374151';

        $vars = [
            '{{client_name}}'      => $invoice->client->name,
            '{{invoice_number}}'   => $invoice->number,
            '{{invoice_total}}'    => number_format($invoice->total, 2),
            '{{invoice_currency}}' => $invoice->currency,
            '{{invoice_due_date}}' => $dueDate . ($isOverdue ? ' ⚠️ متأخرة' : ''),
            '{{invoice_notes}}'    => $invoice->notes ?? '',
            '{{invoice_url}}'      => url('/invoices'),   // مستقبلاً: بوابة فاتورة العميل
            '{{from_name}}'        => $this->senderName,
            '{{due_color}}'        => $dueColor,
        ];

        $tpl = EmailTemplate::render('invoice_send', $vars);

        // fallback إذا لم يوجد القالب في DB
        if (! $tpl) {
            $body = view('emails.invoice-fallback', ['invoice' => $invoice, 'senderName' => $this->senderName])->render();
            return ['subject' => 'فاتورة ' . $invoice->number, 'body' => $body];
        }

        // معالجة بسيطة لـ {{#notes}}...{{/notes}} (conditional block)
        if (empty($invoice->notes)) {
            $tpl['body'] = preg_replace('/\{\{#invoice_notes\}\}.*?\{\{\/invoice_notes\}\}/s', '', $tpl['body']);
        } else {
            $tpl['body'] = str_replace(['{{#invoice_notes}}', '{{/invoice_notes}}'], '', $tpl['body']);
        }

        return $tpl;
    }
}

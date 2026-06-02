<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Invoice $invoice,
        public readonly string  $type,  // before_due | overdue
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->type === 'before_due'
            ? "تذكير: فاتورة {$this->invoice->number} تستحق قريباً"
            : "تنبيه: فاتورة {$this->invoice->number} متأخرة السداد";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $invoice = $this->invoice;
        $type    = $this->type;

        if ($type === 'before_due') {
            $body = "
                <p>عزيزي {$invoice->client->name}،</p>
                <p>نودّ تذكيرك بأن الفاتورة رقم <strong>{$invoice->number}</strong>
                بمبلغ <strong>" . number_format($invoice->total, 2) . " {$invoice->currency}</strong>
                تستحق السداد بتاريخ <strong>{$invoice->due_date?->format('Y/m/d')}</strong>.</p>
                <p>نرجو تسوية المبلغ في الوقت المحدد.</p>
                <p>شكراً لتعاملك معنا.</p>
            ";
        } else {
            $daysOverdue = now()->diffInDays($invoice->due_date);
            $body = "
                <p>عزيزي {$invoice->client->name}،</p>
                <p>نودّ تنبيهك بأن الفاتورة رقم <strong>{$invoice->number}</strong>
                بمبلغ <strong>" . number_format($invoice->total, 2) . " {$invoice->currency}</strong>
                كان موعد سدادها <strong>{$invoice->due_date?->format('Y/m/d')}</strong>
                وقد تأخّر السداد {$daysOverdue} يوم/أيام.</p>
                <p>نرجو التواصل معنا لتسوية المبلغ في أقرب وقت.</p>
            ";
        }

        return new Content(
            view: 'emails.template',
            with: ['body' => $body],
        );
    }
}

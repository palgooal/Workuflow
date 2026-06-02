<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;

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

    // ── إرفاق PDF ───────────────────────────────────────────────────────
    public function attachments(): array
    {
        try {
            $pdfContent = $this->generatePdf();
            $filename   = 'invoice-' . $this->invoice->number . '.pdf';

            return [
                Attachment::fromData(fn () => $pdfContent, $filename)
                    ->withMime('application/pdf'),
            ];
        } catch (\Throwable $e) {
            // إذا فشل توليد PDF لا تمنع الإرسال
            return [];
        }
    }

    // ── توليد PDF ────────────────────────────────────────────────────────
    private function generatePdf(): string
    {
        $invoice = $this->invoice->load(['client', 'project', 'items', 'user']);

        $defaultConfig     = (new ConfigVariables())->getDefaults();
        $defaultFontConfig = (new FontVariables())->getDefaults();

        $mpdf = new Mpdf([
            'mode'             => 'utf-8',
            'format'           => 'A4',
            'orientation'      => 'P',
            'margin_top'       => 15,
            'margin_bottom'    => 15,
            'margin_left'      => 15,
            'margin_right'     => 15,
            'fontDir'          => array_merge($defaultConfig['fontDir'], [base_path('resources/fonts')]),
            'fontdata'         => $defaultFontConfig['fontdata'],
            'default_font'     => 'dejavusans',
            'autoScriptToLang' => true,
            'autoLangToFont'   => true,
            'direction'        => 'rtl',
        ]);

        $mpdf->SetDirectionality('rtl');
        $html = view('invoices.pdf', compact('invoice'))->render();
        $mpdf->WriteHTML($html);

        return $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
    }

    // ── استبدال المتغيرات ─────────────────────────────────────────────
    private function resolveTemplate(): array
    {
        $invoice   = $this->invoice;
        $dueDate   = $invoice->due_date?->format('d/m/Y') ?? '—';
        $isOverdue = $invoice->isOverdue();
        $dueColor  = $isOverdue ? '#dc2626' : '#374151';

        // رابط موقَّع للعميل — صالح 30 يوماً
        $invoiceUrl = URL::signedRoute(
            'invoices.public-view',
            ['ulid' => $invoice->ulid],
            now()->addDays(30)
        );

        // بناء جدول البنود
        $itemsHtml = $this->buildItemsTable($invoice);

        $vars = [
            '{{client_name}}'      => $invoice->client->name,
            '{{invoice_number}}'   => $invoice->number,
            '{{invoice_total}}'    => number_format($invoice->total, 2),
            '{{invoice_currency}}' => $invoice->currency,
            '{{invoice_due_date}}' => $dueDate . ($isOverdue ? ' ⚠️ متأخرة' : ''),
            '{{invoice_notes}}'    => $invoice->notes ?? '',
            '{{invoice_url}}'      => $invoiceUrl,
            '{{invoice_items}}'    => $itemsHtml,
            '{{from_name}}'        => $this->senderName,
            '{{due_color}}'        => $dueColor,
        ];

        $tpl = EmailTemplate::render('invoice_send', $vars);

        if (! $tpl) {
            $body = $this->buildFallbackBody($invoice, $invoiceUrl, $itemsHtml);
            return ['subject' => 'فاتورة ' . $invoice->number . ' من ' . $this->senderName, 'body' => $body];
        }

        // conditional block للملاحظات
        if (empty($invoice->notes)) {
            $tpl['body'] = preg_replace('/\{\{#invoice_notes\}\}.*?\{\{\/invoice_notes\}\}/s', '', $tpl['body']);
        } else {
            $tpl['body'] = str_replace(['{{#invoice_notes}}', '{{/invoice_notes}}'], '', $tpl['body']);
        }

        return $tpl;
    }

    private function buildItemsTable(Invoice $invoice): string
    {
        $rows = '';
        foreach ($invoice->items as $index => $item) {
            $bg   = $index % 2 === 0 ? '#ffffff' : '#f9fafb';
            $rows .= sprintf(
                '<tr style="background:%s">
                    <td style="padding:8px 12px;border-bottom:1px solid #f1f5f9;">%s</td>
                    <td style="padding:8px 12px;border-bottom:1px solid #f1f5f9;text-align:center;">%s</td>
                    <td style="padding:8px 12px;border-bottom:1px solid #f1f5f9;text-align:center;">%s %s</td>
                    <td style="padding:8px 12px;border-bottom:1px solid #f1f5f9;text-align:center;font-weight:600;">%s %s</td>
                </tr>',
                $bg,
                htmlspecialchars($item->description),
                number_format($item->quantity, 2),
                number_format($item->unit_price, 2), $invoice->currency,
                number_format($item->quantity * $item->unit_price, 2), $invoice->currency
            );
        }

        return '<table style="width:100%;border-collapse:collapse;font-size:13px;margin:12px 0;">
            <thead>
                <tr style="background:#f1f5f9;">
                    <th style="padding:8px 12px;text-align:right;color:#6b7280;font-weight:600;">الوصف</th>
                    <th style="padding:8px 12px;text-align:center;color:#6b7280;font-weight:600;width:80px;">الكمية</th>
                    <th style="padding:8px 12px;text-align:center;color:#6b7280;font-weight:600;width:120px;">سعر الوحدة</th>
                    <th style="padding:8px 12px;text-align:center;color:#6b7280;font-weight:600;width:130px;">الإجمالي</th>
                </tr>
            </thead>
            <tbody>' . $rows . '</tbody>
        </table>';
    }

    private function buildFallbackBody(Invoice $invoice, string $url, string $itemsHtml): string
    {
        $dueDate  = $invoice->due_date?->format('d/m/Y') ?? '—';
        $color    = '#4f46e5';

        return "
            <p>مرحباً {$invoice->client->name}،</p>
            <p>يسعدنا إرسال الفاتورة رقم <strong>{$invoice->number}</strong> من <strong>{$this->senderName}</strong>.</p>

            {$itemsHtml}

            <table style='width:100%;font-size:14px;margin-top:4px;'>
                " . ($invoice->discount > 0 ? "<tr><td style='padding:4px 12px;color:#6b7280;'>المجموع الفرعي</td><td style='padding:4px 12px;text-align:left;'>" . number_format($invoice->subtotal, 2) . " {$invoice->currency}</td></tr>
                <tr><td style='padding:4px 12px;color:#6b7280;'>الخصم</td><td style='padding:4px 12px;text-align:left;'>- " . number_format($invoice->discount, 2) . " {$invoice->currency}</td></tr>" : '') . "
                " . ($invoice->tax > 0 ? "<tr><td style='padding:4px 12px;color:#6b7280;'>الضريبة</td><td style='padding:4px 12px;text-align:left;'>" . number_format($invoice->tax, 2) . " {$invoice->currency}</td></tr>" : '') . "
                <tr style='border-top:2px solid {$color};'>
                    <td style='padding:10px 12px;font-weight:700;font-size:16px;color:{$color};'>الإجمالي</td>
                    <td style='padding:10px 12px;font-weight:700;font-size:16px;color:{$color};text-align:left;'>" . number_format($invoice->total, 2) . " {$invoice->currency}</td>
                </tr>
            </table>

            <p style='color:#6b7280;'>تاريخ الاستحقاق: <strong>{$dueDate}</strong></p>

            " . ($invoice->notes ? "<p style='color:#6b7280;font-size:13px;background:#f9fafb;padding:10px;border-right:3px solid {$color};'>ملاحظات: {$invoice->notes}</p>" : '') . "

            <p style='text-align:center;margin:24px 0;'>
                <a href='{$url}' style='background:{$color};color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:bold;'>
                    عرض الفاتورة
                </a>
            </p>

            <p>مرفق ملف الفاتورة PDF للمراجعة.</p>
            <p>مع التقدير،<br><strong>{$this->senderName}</strong></p>
        ";
    }
}

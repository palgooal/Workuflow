<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('email_templates')->insertOrIgnore([
            'key'       => 'invoice_send',
            'name'      => 'إرسال فاتورة للعميل',
            'subject'   => 'فاتورة {{invoice_number}} من {{from_name}}',
            'body'      => '<p>مرحباً {{client_name}}،</p>
<p>يسعدنا إرسال الفاتورة رقم <strong>{{invoice_number}}</strong> إليك.</p>

<table style="width:100%;border-collapse:collapse;margin:16px 0;font-size:14px;">
  <tr>
    <td style="padding:8px;color:#6b7280;">رقم الفاتورة</td>
    <td style="padding:8px;font-weight:600;">{{invoice_number}}</td>
  </tr>
  <tr style="background:#f9fafb;">
    <td style="padding:8px;color:#6b7280;">الإجمالي</td>
    <td style="padding:8px;font-weight:700;color:#059669;">{{invoice_total}} {{invoice_currency}}</td>
  </tr>
  <tr>
    <td style="padding:8px;color:#6b7280;">تاريخ الاستحقاق</td>
    <td style="padding:8px;color:{{due_color}};">{{invoice_due_date}}</td>
  </tr>
</table>

{{#invoice_notes}}
<p style="color:#6b7280;font-size:13px;">ملاحظات: {{invoice_notes}}</p>
{{/invoice_notes}}

<p style="text-align:center;margin:24px 0;">
  <a href="{{invoice_url}}" style="background:#6366f1;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:bold;">
    عرض الفاتورة
  </a>
</p>

<p>لأي استفسار لا تتردد في التواصل معنا.</p>
<p>مع التقدير،<br><strong>{{from_name}}</strong></p>',
            'variables' => json_encode([
                '{{client_name}}'       => 'اسم العميل',
                '{{invoice_number}}'    => 'رقم الفاتورة',
                '{{invoice_total}}'     => 'المبلغ الإجمالي',
                '{{invoice_currency}}'  => 'العملة',
                '{{invoice_due_date}}'  => 'تاريخ الاستحقاق',
                '{{invoice_notes}}'     => 'ملاحظات الفاتورة',
                '{{invoice_url}}'       => 'رابط الفاتورة (مستقبلاً)',
                '{{from_name}}'         => 'اسم المُرسِل',
                '{{due_color}}'         => 'لون تاريخ الاستحقاق',
            ]),
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('email_templates')->where('key', 'invoice_send')->delete();
    }
};

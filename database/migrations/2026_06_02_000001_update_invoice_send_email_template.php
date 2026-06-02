<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('email_templates')
            ->where('key', 'invoice_send')
            ->update([
                'body' => '<p>مرحباً {{client_name}}،</p>
<p>يسعدنا إرسال الفاتورة رقم <strong>{{invoice_number}}</strong> من <strong>{{from_name}}</strong>.</p>

{{invoice_items}}

<table style="width:100%;font-size:14px;margin-top:4px;">
  <tr style="border-top:2px solid #4f46e5;">
    <td style="padding:10px 12px;font-weight:700;font-size:16px;color:#4f46e5;">الإجمالي</td>
    <td style="padding:10px 12px;font-weight:700;font-size:16px;color:#4f46e5;text-align:left;">{{invoice_total}} {{invoice_currency}}</td>
  </tr>
</table>

<p style="color:#6b7280;">تاريخ الاستحقاق: <strong style="color:{{due_color}};">{{invoice_due_date}}</strong></p>

{{#invoice_notes}}
<p style="color:#6b7280;font-size:13px;background:#f9fafb;padding:10px;border-right:3px solid #4f46e5;">ملاحظات: {{invoice_notes}}</p>
{{/invoice_notes}}

<p style="text-align:center;margin:24px 0;">
  <a href="{{invoice_url}}" style="background:#4f46e5;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:bold;">
    عرض الفاتورة
  </a>
</p>

<p>مرفق ملف الفاتورة PDF للمراجعة.</p>
<p>مع التقدير،<br><strong>{{from_name}}</strong></p>',
                'variables' => json_encode([
                    '{{client_name}}'       => 'اسم العميل',
                    '{{invoice_number}}'    => 'رقم الفاتورة',
                    '{{invoice_items}}'     => 'جدول بنود الفاتورة',
                    '{{invoice_total}}'     => 'المبلغ الإجمالي',
                    '{{invoice_currency}}'  => 'العملة',
                    '{{invoice_due_date}}'  => 'تاريخ الاستحقاق',
                    '{{invoice_notes}}'     => 'ملاحظات الفاتورة',
                    '{{invoice_url}}'       => 'رابط عرض الفاتورة (صالح 30 يوماً)',
                    '{{from_name}}'         => 'اسم المُرسِل',
                    '{{due_color}}'         => 'لون تاريخ الاستحقاق',
                ]),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // لا حاجة للـ rollback
    }
};

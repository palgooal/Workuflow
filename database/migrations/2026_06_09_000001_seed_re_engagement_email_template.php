<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('email_templates')->updateOrInsert(
            ['key' => 're_engagement'],
            [
                'name'    => 'إعادة تفعيل — مستخدمون غير نشطين',
                'subject' => 'مشروعك الأول ينتظرك في دراهم 🚀',
                'body'    => '<p>مرحباً {{name}}،</p>

<p>لاحظنا أنك سجّلت في <strong>دراهم</strong> لكن لم تبدأ بعد — ونحن هنا لنساعدك.</p>

<p>دراهم يساعدك على:</p>
<ul>
  <li>📁 تتبع دخلك ومصروفاتك لكل مشروع</li>
  <li>🧾 إصدار فواتير احترافية وإرسالها بالواتساب</li>
  <li>👥 إدارة عملائك في مكان واحد</li>
  <li>📊 رؤية تقارير أرباحك وخسائرك لحظة بلحظة</li>
</ul>

<p>يمكنك البدء في أقل من دقيقتين — أنشئ مشروعك الأول الآن:</p>

<p style="text-align:center; margin:28px 0;">
  <a href="{{dashboard_url}}"
     style="display:inline-block; background:#6366f1; color:#fff; padding:14px 32px;
            border-radius:10px; text-decoration:none; font-weight:700; font-size:15px;">
    ابدأ الآن ←
  </a>
</p>

<p style="color:#6b7280; font-size:13px;">
  إذا كنت تواجه أي صعوبة أو لديك سؤال — تواصل معنا مباشرة على واتساب:
  <a href="https://wa.me/{{owner_whatsapp}}">واتساب الدعم</a>
</p>',
                'variables' => json_encode([
                    '{{name}}'           => 'اسم المستخدم',
                    '{{dashboard_url}}'  => 'رابط لوحة التحكم',
                    '{{owner_whatsapp}}' => 'رقم واتساب الدعم',
                ]),
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('email_templates')->where('key', 're_engagement')->delete();
    }
};

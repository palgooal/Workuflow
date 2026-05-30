<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->string('key')->primary();       // مفتاح فريد للقالب
            $table->string('name');                  // اسم عربي للعرض
            $table->string('subject');               // موضوع الرسالة
            $table->longText('body');                // محتوى الرسالة (HTML)
            $table->json('variables')->nullable();   // متغيرات متاحة
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── القوالب الافتراضية ────────────────────────────────────────────
        DB::table('email_templates')->insert([

            // 1. إعادة تعيين كلمة المرور
            [
                'key'       => 'password_reset',
                'name'      => 'إعادة تعيين كلمة المرور',
                'subject'   => 'إعادة تعيين كلمة المرور — دراهم',
                'body'      => '<p>مرحباً {{name}}،</p>
<p>تلقّينا طلباً لإعادة تعيين كلمة المرور الخاصة بحسابك.</p>
<p style="text-align:center; margin:24px 0;">
  <a href="{{reset_url}}" style="background:#6366f1;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:bold;">
    إعادة تعيين كلمة المرور
  </a>
</p>
<p>ينتهي هذا الرابط خلال <strong>60 دقيقة</strong>.</p>
<p>إذا لم تطلب إعادة التعيين، يمكنك تجاهل هذا البريد بأمان.</p>
<p>مع التقدير،<br><strong>فريق دراهم</strong></p>',
                'variables' => json_encode(['{{name}}' => 'اسم المستخدم', '{{reset_url}}' => 'رابط إعادة التعيين']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 2. بريد الترحيب
            [
                'key'       => 'welcome',
                'name'      => 'بريد الترحيب',
                'subject'   => 'مرحباً بك في دراهم 🎉',
                'body'      => '<p>مرحباً {{name}}،</p>
<p>يسعدنا انضمامك إلى <strong>دراهم</strong> — منصتك المالية الذكية.</p>
<p>ستتمكن من:</p>
<ul>
  <li>تتبع إيراداتك ومصروفاتك بسهولة</li>
  <li>إدارة مشاريعك وعملائك من مكان واحد</li>
  <li>إصدار الفواتير وعروض الأسعار باحترافية</li>
</ul>
<p style="text-align:center; margin:24px 0;">
  <a href="{{login_url}}" style="background:#6366f1;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:bold;">
    ابدأ الآن
  </a>
</p>
<p>مع التقدير،<br><strong>فريق دراهم</strong></p>',
                'variables' => json_encode(['{{name}}' => 'اسم المستخدم', '{{login_url}}' => 'رابط تسجيل الدخول']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 3. تأكيد البريد الإلكتروني
            [
                'key'       => 'email_verification',
                'name'      => 'تأكيد البريد الإلكتروني',
                'subject'   => 'تأكيد بريدك الإلكتروني — دراهم',
                'body'      => '<p>مرحباً {{name}}،</p>
<p>شكراً لتسجيلك في دراهم. يرجى تأكيد بريدك الإلكتروني بالضغط على الزر أدناه.</p>
<p style="text-align:center; margin:24px 0;">
  <a href="{{verify_url}}" style="background:#6366f1;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:bold;">
    تأكيد البريد الإلكتروني
  </a>
</p>
<p>إذا لم تنشئ حساباً، يمكنك تجاهل هذا البريد.</p>
<p>مع التقدير،<br><strong>فريق دراهم</strong></p>',
                'variables' => json_encode(['{{name}}' => 'اسم المستخدم', '{{verify_url}}' => 'رابط التأكيد']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};

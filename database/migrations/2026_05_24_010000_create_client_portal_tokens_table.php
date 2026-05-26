<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Client Portal Tokens — رموز بوابة العميل
 *
 * ⚠️ أمان حرج — C-04 Fix من docs/CLIENTS-CRM-SPEC-V2.md:
 * - عمود token يُخزِّن hash('sha256', $plaintext) — لا النص الأصلي أبداً
 * - النص الأصلي يُعرض مرة واحدة عند الإنشاء ثم يُتلف
 * - rate limiting على محاولات المصادقة (5 محاولات/ساعة)
 * - تأخير اصطناعي عند الفشل لمنع timing attacks
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_portal_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();

            // CHAR(64) = طول SHA-256 hex — لا تُخزَّن القيمة الأصلية أبداً
            $table->char('token', 64)->unique();

            // الصلاحيات كـ JSON: ["view_invoices", "download_invoices", ...]
            $table->json('permissions')->nullable();

            $table->timestamp('expires_at');
            $table->timestamp('last_used_at')->nullable();
            $table->string('last_used_ip', 45)->nullable();  // IPv6 = max 45 chars

            // من أنشأ الرمز (المستخدم صاحب الحساب)
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->timestamps();

            $table->index(['client_id', 'expires_at'], 'cpt_client_expires_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_portal_tokens');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_referral_clicks_table
 *
 * يُسجَّل كل زيارة لرابط إحالة (/ref/{ulid} أو ?ref=DISPLAY_CODE).
 * الربط بالمستخدم يتم من الجهة الأخرى: users.referral_click_id → referral_clicks.id
 * (راجع migration التالي: add_referral_columns_to_users_table)
 *
 * visitor_token: ULID ثابت مخزَّن في Cookie مؤمَّنة (secure, httpOnly, sameSite=lax)
 * ip_address: لأغراض الكشف عن الاحتيال فقط — لا تُستخدَم في الـ attribution (راجع §17)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_clicks', function (Blueprint $table) {
            $table->char('id', 26)->primary();                           // ULID

            // ── المسوّق المُحيل ───────────────────────────────────────────
            $table->char('affiliate_id', 26);

            // ── تتبع الزائر ──────────────────────────────────────────────
            // visitor_token: ULID من Cookie — مستقل عن IP/UA/Date (راجع §4.1 و§3.2)
            $table->char('visitor_token', 26);
            $table->string('ip_address', 45)->nullable();               // IPv4/IPv6
            $table->text('user_agent')->nullable();
            $table->string('landing_page', 500)->nullable();            // URL كامل عند الزيارة

            // ── وقت التحويل ───────────────────────────────────────────────
            // يُملأ عند تسجيل الدخول أو الاشتراك المرتبط بهذه الزيارة
            $table->timestamp('converted_at')->nullable();

            $table->timestamp('created_at');                            // لا updated_at — سجل ثابت

            // ── Indexes ──────────────────────────────────────────────────
            $table->index('affiliate_id', 'idx_clicks_affiliate');
            $table->index('visitor_token', 'idx_clicks_visitor');
            $table->index('ip_address', 'idx_clicks_ip');               // للكشف عن Click Spam (§17)
            $table->index('converted_at', 'idx_clicks_converted');

            // ── Foreign Keys ─────────────────────────────────────────────
            $table->foreign('affiliate_id', 'fk_clicks_affiliate')
                  ->references('id')->on('affiliates')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_clicks');
    }
};

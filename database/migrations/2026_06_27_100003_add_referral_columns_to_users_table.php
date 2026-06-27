<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: add_referral_columns_to_users_table
 *
 * يضيف 3 أعمدة لجدول users لتتبع مصدر الإحالة:
 *
 *   referred_by_affiliate_id  → المسوّق الذي جلب هذا المستخدم (Attribution: first-affiliate-wins)
 *   referral_click_id         → السجل المحدد في referral_clicks المرتبط بالتسجيل
 *   referral_attributed_at    → توقيت إتمام Attribution (وقت التسجيل)
 *
 * القاعدة: Attribution دائمة — first-affiliate-wins — لا تتغيّر بعد الضبط (راجع §4.1)
 *
 * ⚠️  كلا العمودين CHAR(26) لأنهما FK إلى جداول ذات ULID IDs (affiliates, referral_clicks)
 *     (بعكس user_id في affiliates الذي هو unsignedBigInteger)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // CHAR(26): FK إلى affiliates.id (ULID)
            $table->char('referred_by_affiliate_id', 26)
                  ->nullable()
                  ->after('remember_token');

            // CHAR(26): FK إلى referral_clicks.id (ULID) — السجل المحدد للزيارة
            $table->char('referral_click_id', 26)
                  ->nullable()
                  ->after('referred_by_affiliate_id');

            // وقت إتمام الـ Attribution (= وقت التسجيل عند وجود إحالة)
            $table->timestamp('referral_attributed_at')
                  ->nullable()
                  ->after('referral_click_id');

            // ── Foreign Keys ─────────────────────────────────────────────
            $table->foreign('referred_by_affiliate_id', 'fk_users_affiliate')
                  ->references('id')->on('affiliates')
                  ->nullOnDelete();

            $table->foreign('referral_click_id', 'fk_users_click')
                  ->references('id')->on('referral_clicks')
                  ->nullOnDelete();

            // ── Index للبحث السريع عن مستخدمي مسوّق معين ──────────────
            $table->index('referred_by_affiliate_id', 'idx_users_affiliate');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('fk_users_affiliate');
            $table->dropForeign('fk_users_click');
            $table->dropIndex('idx_users_affiliate');
            $table->dropColumn([
                'referred_by_affiliate_id',
                'referral_click_id',
                'referral_attributed_at',
            ]);
        });
    }
};

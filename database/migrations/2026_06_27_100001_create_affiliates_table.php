<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_affiliates_table
 *
 * ⚠️  لا MySQL ENUMs — جميع الحالات كـ VARCHAR + CHECK constraints + PHP Backed Enums
 * ⚠️  user_id → unsignedBigInteger لأن users.id في هذا المشروع bigint (auto-increment)
 *      (الوثيقة §3.1 تذكر CHAR(26) لكن تم تعديله في المرحلة 0 بعد مراجعة بنية المشروع)
 * ⚠️  يتطلب MySQL 8.0.16+ أو MariaDB 10.4+ لتفعيل CHECK constraints — راجع §18
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliates', function (Blueprint $table) {
            $table->char('id', 26)->primary();                           // ULID

            // ── ربط بحساب دراهم ─────────────────────────────────────────
            // unsignedBigInteger لأن users.id = bigint (auto-increment)
            // UNIQUE: مستخدم واحد → حساب مسوّق واحد فقط (راجع §10)
            $table->unsignedBigInteger('user_id')->nullable()->unique('uidx_affiliates_user');

            // ── بيانات المسوّق ───────────────────────────────────────────
            $table->string('name', 100);
            $table->string('email', 150)->unique();
            $table->string('whatsapp', 20)->nullable();
            $table->string('display_code', 50)->nullable()->unique();    // مثل AHMED2026

            // ── العمولة والمستوى ─────────────────────────────────────────
            $table->decimal('commission_rate', 5, 2)->default(30.00);
            $table->string('status', 20)->default('pending');            // CHECK أدناه
            $table->string('tier', 20)->default('standard');             // CHECK أدناه
            $table->string('payout_method', 20)->nullable();             // CHECK أدناه
            $table->json('payout_details')->nullable();                  // بيانات حساب الصرف

            // ── مجاميع مُعادة الحساب (Denormalized Aggregates) ──────────
            // تُحسَب يومياً عبر: php artisan referral:reconcile (راجع §15)
            $table->unsignedInteger('total_referrals')->default(0);
            $table->unsignedInteger('total_converted')->default(0);
            $table->decimal('total_earned', 10, 2)->default(0.00);
            $table->decimal('total_paid', 10, 2)->default(0.00);
            // balance = total_earned - total_paid — لا تُخزَّن، تُحسَب في Model

            // ── بيانات إدارية ────────────────────────────────────────────
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamps();

            // ── Indexes ──────────────────────────────────────────────────
            $table->index('status', 'idx_affiliates_status');
            $table->index('display_code', 'idx_affiliates_display_code');

            // ── Foreign Keys ─────────────────────────────────────────────
            $table->foreign('user_id', 'fk_affiliates_user')
                  ->references('id')->on('users')
                  ->nullOnDelete();
        });

        // ── CHECK constraints (MySQL 8.0.16+ / MariaDB 10.4+) ────────────
        // يُنفَّذ بعد إنشاء الجدول لتجنب أي تعارض مع Blueprint
        DB::statement("
            ALTER TABLE affiliates
                ADD CONSTRAINT chk_affiliates_status
                    CHECK (status IN ('pending','active','suspended')),
                ADD CONSTRAINT chk_affiliates_tier
                    CHECK (tier IN ('standard','silver','gold','platinum')),
                ADD CONSTRAINT chk_affiliates_payout_method
                    CHECK (payout_method IS NULL OR payout_method IN ('bank','whatsapp','credit'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliates');
    }
};

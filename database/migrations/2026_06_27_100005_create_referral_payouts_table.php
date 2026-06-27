<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_referral_payouts_table
 *
 * طلبات صرف العمولات للمسوّقين.
 * المسوّق يطلب الصرف يدوياً → الأدمن يعالجه ويُحدّث الحالة.
 *
 * ⚠️  يتطلب MySQL 8.0.16+ أو MariaDB 10.4+ لـ CHECK constraints — راجع §18
 *
 * دورة الحياة:
 *   requested → processing → paid
 *   requested/processing → rejected
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_payouts', function (Blueprint $table) {
            $table->char('id', 26)->primary();                           // ULID

            // ── المسوّق ────────────────────────────────────────────────────
            $table->char('affiliate_id', 26);                           // FK → affiliates.id

            // ── بيانات الصرف ───────────────────────────────────────────────
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3)->default('USD');
            $table->string('method', 20);                               // CHECK أدناه

            // ── الحالة ────────────────────────────────────────────────────
            $table->string('status', 20)->default('requested');         // CHECK أدناه

            // ── ملاحظات ───────────────────────────────────────────────────
            $table->text('admin_notes')->nullable();                    // ملاحظات الأدمن عند المعالجة

            // ── التوقيتات ─────────────────────────────────────────────────
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // ── Indexes ──────────────────────────────────────────────────
            $table->index('affiliate_id', 'idx_payouts_affiliate');
            $table->index('status', 'idx_payouts_status');

            // ── Foreign Keys ─────────────────────────────────────────────
            $table->foreign('affiliate_id', 'fk_payouts_affiliate')
                  ->references('id')->on('affiliates');
        });

        // ── CHECK constraints (MySQL 8.0.16+ / MariaDB 10.4+) ────────────
        DB::statement("
            ALTER TABLE referral_payouts
                ADD CONSTRAINT chk_payouts_method
                    CHECK (method IN ('bank','whatsapp','credit')),
                ADD CONSTRAINT chk_payouts_status
                    CHECK (status IN ('requested','processing','paid','rejected'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_payouts');
    }
};

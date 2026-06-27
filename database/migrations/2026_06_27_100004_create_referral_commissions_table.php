<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_referral_commissions_table
 *
 * عمولة واحدة لكل اشتراك (UNIQUE subscription_id).
 * تُنشَأ فقط عند أول تفعيل مدفوع — لا عمولات على التجديد (راجع §5 + §3.4).
 *
 * ⚠️  referred_user_id → unsignedBigInteger لأن users.id = bigint (auto-increment)
 *      (الوثيقة §3.4 تذكر CHAR(26) لكن تم تعديله في المرحلة 0)
 * ⚠️  subscription_id → CHAR(26) لأن subscriptions.id = ULID ✅
 * ⚠️  يتطلب MySQL 8.0.16+ أو MariaDB 10.4+ لـ CHECK constraints — راجع §18
 *
 * دورة الحياة:
 *   pending → approved → paid
 *   pending → rejected  (احتيال أو خطأ)
 *   pending/approved → cancelled (الاشتراك استُرد)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_commissions', function (Blueprint $table) {
            $table->char('id', 26)->primary();                           // ULID

            // ── الأطراف ───────────────────────────────────────────────────
            $table->char('affiliate_id', 26);                           // FK → affiliates.id
            $table->char('subscription_id', 26);                        // FK → subscriptions.id (ULID)
            // unsignedBigInteger: users.id = bigint (auto-increment) في هذا المشروع
            $table->unsignedBigInteger('referred_user_id');

            // ── قيم العمولة ───────────────────────────────────────────────
            $table->decimal('amount', 10, 2);                           // قيمة العمولة (amount = subscription_amount × rate/100)
            $table->char('currency', 3)->default('USD');
            $table->decimal('rate', 5, 2);                              // النسبة وقت الإنشاء (قد تختلف عن tier لاحقاً)

            // ── بيانات الاشتراك المُولِّد ─────────────────────────────────
            $table->decimal('subscription_amount', 10, 2);              // قيمة الاشتراك الأصلية
            $table->string('subscription_plan', 20);                    // pro / business
            $table->string('subscription_cycle', 10);                   // CHECK أدناه

            // ── الحالة والمصدر ────────────────────────────────────────────
            $table->string('status', 20)->default('pending');           // CHECK أدناه
            $table->string('trigger_source', 30);                       // CHECK أدناه

            // ── مكافحة الاحتيال (راجع §17) ──────────────────────────────
            // 0 = نظيف، 1 = موسوم (يستوجب مراجعة يدوية)
            $table->tinyInteger('fraud_flagged')->default(0);

            // ── بيانات زمنية ──────────────────────────────────────────────
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // ── Indexes ──────────────────────────────────────────────────
            // UNIQUE: عمولة واحدة فقط لكل اشتراك — يمنع التكرار إن أُعيد الاستدعاء
            $table->unique('subscription_id', 'uidx_commissions_subscription');
            $table->index('affiliate_id', 'idx_commissions_affiliate');
            $table->index('status', 'idx_commissions_status');
            $table->index('fraud_flagged', 'idx_commissions_fraud');

            // ── Foreign Keys ─────────────────────────────────────────────
            $table->foreign('affiliate_id', 'fk_commissions_affiliate')
                  ->references('id')->on('affiliates');

            $table->foreign('subscription_id', 'fk_commissions_subscription')
                  ->references('id')->on('subscriptions');

            $table->foreign('referred_user_id', 'fk_commissions_user')
                  ->references('id')->on('users');
        });

        // ── CHECK constraints (MySQL 8.0.16+ / MariaDB 10.4+) ────────────
        DB::statement("
            ALTER TABLE referral_commissions
                ADD CONSTRAINT chk_commissions_status
                    CHECK (status IN ('pending','approved','paid','rejected','cancelled')),
                ADD CONSTRAINT chk_commissions_trigger
                    CHECK (trigger_source IN ('togo_callback','manual_admin')),
                ADD CONSTRAINT chk_commissions_cycle
                    CHECK (subscription_cycle IN ('monthly','annual'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_commissions');
    }
};

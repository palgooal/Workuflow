<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * نظام عروض الأسعار — Quotes System
 *
 * جدولان:
 *   quotes      — رأس العرض (بيانات العميل، الإجماليات، الحالة، التوكن)
 *   quote_items — بنود العرض (خدمات، كميات، أسعار)
 *
 * ملاحظة: project_id يجب char(26) لأن projects.id هو ULID.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->char('ulid', 26)->unique();                    // معرّف خارجي للـ URL
            $table->string('token', 64)->unique();                 // توكن بوابة العميل /q/{token}

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->char('project_id', 26)->nullable();            // ULID FK → projects.id

            $table->string('number', 50)->unique();                // QUO-0001
            $table->string('title', 255)->nullable();
            $table->string('status', 20)->default('draft');        // VARCHAR للمرونة

            // التواريخ
            $table->date('issue_date');
            $table->date('valid_until')->nullable();               // تاريخ انتهاء الصلاحية

            // الأرقام المالية
            $table->decimal('subtotal',  12, 2)->default(0);
            $table->decimal('tax_rate',   5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount',   12, 2)->default(0);
            $table->decimal('total',      12, 2)->default(0);
            $table->string('currency', 3)->default('ILS');

            // المحتوى الحر
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();

            // أوقات تتبع الحالة
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('viewed_at')->nullable();            // أول فتح من العميل
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('converted_at')->nullable();

            // بيانات إضافية عند الرد (مستقبل: Digital Signature)
            $table->string('client_ip', 45)->nullable();           // IP عند القبول/الرفض
            $table->string('rejection_reason', 500)->nullable();   // سبب الرفض (اختياري)

            $table->timestamps();
            $table->softDeletes();

            // مؤشرات
            $table->index(['user_id', 'status'],     'quotes_user_status_idx');
            $table->index(['user_id', 'client_id'],  'quotes_user_client_idx');
            $table->index('valid_until',              'quotes_valid_until_idx');
        });

        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->string('description', 500);
            $table->decimal('quantity',   10, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total',      12, 2)->default(0);
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_items');
        Schema::dropIfExists('quotes');
    }
};

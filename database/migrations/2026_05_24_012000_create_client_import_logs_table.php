<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Client Import Logs — سجل عمليات الاستيراد
 *
 * يتتبع كل عملية استيراد: الحالة، عدد الصفوف، الأخطاء.
 *
 * Idempotency:
 * - عمود idempotency_key UNIQUE يمنع تشغيل نفس الاستيراد مرتين.
 * - المستخدم يُرسل X-Idempotency-Key header مع كل طلب استيراد.
 * - إذا وُجد المفتاح مسبقاً → يُعيد النتيجة المخزنة بدون إعادة المعالجة.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_import_logs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('filename', 255);

            // مفتاح Idempotency — CHAR(64) للسرعة في البحث
            $table->char('idempotency_key', 64)->unique();

            // الحالة — VARCHAR (C-03 Fix)
            // pending | processing | completed | failed | partial
            $table->string('status', 20)->default('pending');

            // إحصاءات الاستيراد
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('error_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);  // صفوف مكررة مُتجاهَلة

            // تفاصيل الأخطاء (أول 100 خطأ)
            $table->json('errors')->nullable();

            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at'], 'cil_user_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_import_logs');
    }
};

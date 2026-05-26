<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Client Activities — سجل نشاط العميل
 *
 * يسجّل كل حدث يتعلق بالعميل: دفع فاتورة، إضافة وسم، تغيير حالة، ملاحظة...
 *
 * ملاحظة حول Partitioning:
 * الـ Spec الأصلي يوصي بـ MySQL RANGE Partitioning لتحمّل 270M+ صف.
 * على Shared Hosting يُستخدم جدول عادي مع indexes محسّنة.
 * يُضاف Partitioning لاحقاً عند الانتقال لـ VPS/Dedicated.
 *
 * المرجع: docs/CLIENTS-CRM-SPEC-V2.md — ملاحظة الـ Shared Hosting
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_activities', function (Blueprint $table) {
            $table->id();   // BIGINT AUTO_INCREMENT
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();  // المستخدم الذي نفّذ الإجراء

            // نوع النشاط — VARCHAR بدل ENUM (C-03 Fix)
            $table->string('type', 50);

            $table->text('description')->nullable();
            $table->json('metadata')->nullable();  // بيانات إضافية حسب نوع النشاط

            // وقت الحدث الفعلي (قد يختلف عن created_at للأحداث التاريخية)
            $table->timestamp('occurred_at')->useCurrent();

            // Indexes مُركَّبة لتسريع timeline queries
            $table->index(['client_id', 'occurred_at'], 'ca_client_occurred_idx');
            $table->index(['user_id', 'occurred_at'],   'ca_user_occurred_idx');
            $table->index(['client_id', 'type'],        'ca_client_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_activities');
    }
};

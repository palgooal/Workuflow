<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Client Follow-ups — جدول المتابعات
 *
 * يتتبع المواعيد والمهام المرتبطة بالعملاء.
 * الحالات: pending | completed | overdue | cancelled
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_follow_ups', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // نوع المتابعة: call | email | meeting | task | other
            $table->string('type', 20)->default('task');
            $table->string('title', 255);

            // الحالة — VARCHAR (C-03 Fix)
            $table->string('status', 20)->default('pending');

            // التواريخ
            $table->timestamp('due_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('reminder_at')->nullable();   // لإرسال تذكير مسبق

            // الأولوية 1 (عالية) → 5 (منخفضة)
            $table->unsignedTinyInteger('priority')->default(3);

            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status', 'due_at'], 'cfu_user_status_due_idx');
            $table->index(['client_id', 'status'],          'cfu_client_status_idx');
            $table->index('reminder_at',                    'cfu_reminder_idx');  // للـ Scheduler
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_follow_ups');
    }
};

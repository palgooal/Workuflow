<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * automation_rules — قواعد الأتمتة
 *
 * Sprint 6 — S6.1
 *
 * كل قاعدة تربط: Trigger → Conditions → Actions
 * تُقيَّم عند وقوع الـ Trigger وتُنفَّذ Actions إذا طابقت الـ Conditions.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('name');                          // اسم القاعدة (للعرض)
            $table->string('trigger');                       // client_created | status_changed | ...
            $table->json('conditions')->nullable();          // [{field, op, value}] مع AND/OR
            $table->json('actions');                         // [{type, params}]

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('priority')->default(10); // تنفيذ بالأولوية (أصغر = أول)
            $table->unsignedInteger('run_count')->default(0); // عدد مرات التنفيذ
            $table->timestamp('last_run_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // الاستعلام الأكثر شيوعاً: نشط + trigger + مستخدم
            $table->index(['user_id', 'trigger', 'is_active'], 'automation_rules_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_rules');
    }
};

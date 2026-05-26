<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Client Health Scores — سجل مؤشرات صحة العملاء
 *
 * يُخزَّن تاريخ الدرجات للتتبع الزمني.
 * أحدث سجل لكل عميل = الدرجة الحالية.
 * يُحسب ليلياً بـ RecalculateHealthScoresCommand (02:00).
 *
 * خوارزمية 5 عوامل — أوزانها في config/crm.php
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_health_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();

            // الدرجة الإجمالية 0-100
            $table->unsignedTinyInteger('score')->default(0);

            // تفصيل العوامل الخمسة (JSON) للشرح في الـ UI
            // مثال: {"payment_rate": 0.8, "work_frequency": 0.6, ...}
            $table->json('factors')->nullable();

            // وقت الحساب
            $table->timestamp('scored_at')->useCurrent();

            // Index لجلب أحدث درجة لكل عميل بكفاءة
            $table->index(['client_id', 'scored_at'], 'chs_client_scored_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_health_scores');
    }
};

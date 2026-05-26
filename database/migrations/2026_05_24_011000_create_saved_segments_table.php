<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Saved Segments — الشرائح المحفوظة
 *
 * تتيح للمستخدمين حفظ مجموعة فلاتر وإعادة تنفيذها بنقرة.
 * مثال: "عملاء VIP لم يدفعوا منذ 3 أشهر"
 *        "عملاء جدد من الإحالات هذا الشهر"
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_segments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('name', 100);

            // تعريف الفلاتر كـ JSON
            // مثال: [{"field":"status","op":"equals","value":"active"},...]
            $table->json('filters');

            // هل يُعاد حساب العدد تلقائياً؟
            $table->boolean('is_dynamic')->default(true);

            // عدد العملاء المطابقين (يُحدَّث عند التنفيذ)
            $table->unsignedInteger('client_count')->default(0);

            $table->timestamp('last_executed_at')->nullable();

            // هل مُثبَّت في الشريط الجانبي؟
            $table->boolean('is_pinned')->default(false);

            $table->timestamps();

            $table->index(['user_id', 'is_pinned'], 'ss_user_pinned_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_segments');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * إصلاح: تغيير unique على quotes.number من عالمي إلى per-user
 * السبب: كل مستخدم يبدأ ترقيمه من QUO-0001، فالـ unique العالمي يتسبب
 *        في Duplicate entry عند إنشاء أول عرض لمستخدم ثانٍ.
 * الحل: composite unique على (user_id, number)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            // حذف الـ unique العالمي
            $table->dropUnique('quotes_number_unique');

            // إضافة unique مركّب per-user
            $table->unique(['user_id', 'number'], 'quotes_user_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropUnique('quotes_user_number_unique');
            $table->unique('number', 'quotes_number_unique');
        });
    }
};

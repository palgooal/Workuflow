<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Client Field Definitions — تعريفات الحقول المخصصة
 *
 * تتيح للمستخدمين (Business plan) إضافة حقول مخصصة لملفات عملائهم.
 * مثال: "رقم السجل التجاري"، "موعد تجديد العقد"، "تصنيف داخلي"
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_field_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('name', 100);       // الاسم المعروض للمستخدم
            $table->string('key', 60);         // المفتاح البرمجي (snake_case)

            // أنواع الحقول المدعومة
            $table->string('type', 20)->default('text');  // text|number|date|boolean|select|url

            $table->boolean('is_required')->default(false);
            $table->json('options')->nullable();    // للحقول من نوع select: قائمة الخيارات
            $table->unsignedSmallInteger('display_order')->default(0);

            // الخطة المطلوبة لهذا الحقل (null = متاح للجميع)
            $table->string('plan_required', 20)->nullable()->default('business');

            $table->timestamps();

            // كل مستخدم لا يمكنه إنشاء حقلين بنفس الـ key
            $table->unique(['user_id', 'key'], 'cfd_user_key_unique');
            $table->index(['user_id', 'display_order'], 'cfd_user_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_field_definitions');
    }
};

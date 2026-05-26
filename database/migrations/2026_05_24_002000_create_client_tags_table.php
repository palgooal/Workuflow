<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Client Tags — وسوم العملاء
 *
 * نوعان: system (ثابتة، مُنشأة بالـ Seeder، لا تُحذف)
 *        custom (مُنشأة بالمستخدم، مرتبطة بـ user_id)
 *
 * system tags: user_id = NULL (مشتركة لكل المستخدمين)
 * custom tags: user_id = ID المستخدم
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_tags', function (Blueprint $table) {
            $table->id();

            // NULL للوسوم الافتراضية (system), مُعيَّن للوسوم المخصصة
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name', 50);
            $table->string('slug', 60)->index();
            $table->char('color', 7)->default('#6B7280');   // HEX
            $table->string('type', 20)->default('custom');  // system | custom
            $table->string('icon', 10)->nullable();         // Emoji أو أيقونة
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('priority')->default(99);

            $table->timestamps();

            // كل مستخدم لا يمكنه إنشاء وسمين بنفس الـ slug
            // (NULL = system tags مشتركة، لا تتعارض مع user tags)
            $table->unique(['user_id', 'slug'], 'client_tags_user_slug_unique');
            $table->index(['user_id', 'type'], 'client_tags_user_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_tags');
    }
};

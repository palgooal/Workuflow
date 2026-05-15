<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->text('description')->nullable();
            $table->string('icon')->nullable()->default('briefcase');
            $table->string('color', 7)->nullable()->default('#6366f1');
            $table->boolean('is_global')->default(false); // خدمات افتراضية للجميع
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index('is_global');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};

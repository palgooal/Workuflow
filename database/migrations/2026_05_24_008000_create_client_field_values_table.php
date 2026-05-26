<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Client Field Values — قيم الحقول المخصصة
 *
 * يخزّن قيمة كل حقل مخصص لكل عميل.
 * يستخدم TEXT لتخزين أي نوع كقيمة نصية (الـ cast يتم في الـ Model).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('field_definition_id')
                ->references('id')
                ->on('client_field_definitions')
                ->cascadeOnDelete();

            $table->text('value')->nullable();
            $table->timestamps();

            // عميل واحد، قيمة واحدة لكل حقل
            $table->unique(
                ['client_id', 'field_definition_id'],
                'cfv_client_field_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_field_values');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Client Attachments — مرفقات العميل
 *
 * يخزّن metadata الملفات المرفوعة (العقود، الوثائق، الصور...)
 * الملف الفعلي مخزون على disk (local/s3) ويُشار إليه بـ path.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_attachments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();  // من رفع الملف

            $table->string('filename', 255);       // الاسم الأصلي
            $table->string('disk', 20)->default('local');  // local | s3
            $table->string('path', 500);           // المسار الكامل على الـ disk
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->text('description')->nullable();

            $table->timestamps();

            $table->index('client_id', 'ca_client_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_attachments');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * data_export_requests — تتبّع طلبات "تنزيل نسخة من بياناتي" (لوحة حساب المستخدم).
 *
 * هذه ليست نسخة نظام كاملة ولا أداة Restore — فقط سجل تصدير بيانات المستخدم
 * نفسه وفق عزل user_id. راجع docs/DATA-EXPORT.md.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_export_requests', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // pending / processing / completed / failed / expired
            $table->string('status', 20)->default('pending');

            // مسار الملف داخل disk التصدير (exports) — لا يُعرَض للمستخدم مباشرة أبداً
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            $table->timestamp('requested_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('failure_reason')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_export_requests');
    }
};

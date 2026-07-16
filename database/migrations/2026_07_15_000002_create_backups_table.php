<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * backups — سجل النسخ الاحتياطية التشغيلية الكاملة (لوحة Filament — super_admin فقط).
 *
 * منفصل تماماً عن data_export_requests: هذا الجدول يتتبّع نسخ النظام (قاعدة بيانات
 * كاملة أو نسخة كاملة db+storage)، وليس بيانات مستخدم واحد. راجع docs/BACKUP-SYSTEM.md.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->string('name');

            // database / full
            $table->string('type', 20);

            // pending / running / completed / failed
            $table->string('status', 20)->default('pending');

            // من أطلق النسخة: null = جدولة تلقائية (Scheduler)
            $table->foreignId('triggered_by_user_id')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->string('disk', 40)->nullable();
            $table->string('path')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('checksum', 128)->nullable();
            $table->boolean('encrypted')->default(false);

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();

            $table->text('error_message')->nullable();

            // آخر نتيجة فحص سلامة (checksum) — null إن لم يُفحص بعد
            $table->boolean('integrity_verified')->nullable();
            $table->timestamp('integrity_checked_at')->nullable();

            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};

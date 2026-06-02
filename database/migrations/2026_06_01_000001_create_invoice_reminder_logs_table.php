<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // نوع التذكير: before_due | overdue
            $table->string('type', 20);
            // قناة الإرسال: email | whatsapp
            $table->string('channel', 20);
            $table->timestamp('sent_at')->useCurrent();

            // منع تكرار نفس التذكير لنفس الفاتورة
            $table->unique(['invoice_id', 'type', 'channel'], 'reminder_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_reminder_logs');
    }
};

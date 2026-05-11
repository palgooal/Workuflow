<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->enum('type', ['borrowed', 'lent']); // borrowed=دين عليك | lent=دين لك
            $table->string('party_name');               // اسم الطرف الآخر
            $table->decimal('amount', 15, 2);           // المبلغ الأصلي
            $table->decimal('remaining_amount', 15, 2); // المبلغ المتبقي
            $table->string('currency', 3)->default('SAR');
            $table->date('due_date')->nullable();
            $table->enum('status', ['active', 'partially_paid', 'paid'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'due_date']);
            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};

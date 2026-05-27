<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();         // رقم مرجعي داخلي
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('client_id');
            $table->char('project_id', 26)->nullable(); // ulid FK → projects.id (char 26)

            $table->string('number', 50)->unique();        // رقم الفاتورة INV-0001
            $table->string('status', 20)->default('draft'); // draft|sent|paid|overdue|cancelled
            $table->string('title')->nullable();           // عنوان اختياري

            $table->date('issue_date');                    // تاريخ الإصدار
            $table->date('due_date')->nullable();          // تاريخ الاستحقاق

            $table->decimal('subtotal', 12, 2)->default(0); // المجموع قبل الضريبة
            $table->decimal('tax_rate', 5, 2)->default(0);  // نسبة الضريبة %
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0); // خصم بالقيمة
            $table->decimal('total', 12, 2)->default(0);    // الإجمالي
            $table->string('currency', 3)->default('ILS');

            $table->text('notes')->nullable();             // ملاحظات للعميل
            $table->text('terms')->nullable();             // الشروط والأحكام

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
            $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();

            $table->index(['user_id', 'status']);
            $table->index(['client_id', 'status']);
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->string('description');                 // وصف البند
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);  // quantity * unit_price
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};

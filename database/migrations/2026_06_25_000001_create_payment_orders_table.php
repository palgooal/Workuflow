<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_orders', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('plan', ['pro', 'business'])->index();

            // مزود الدفع
            $table->string('provider', 50)->default('togo');

            // معرّفات الطلب عند مزود الدفع
            $table->string('provider_order_id')->nullable()->index();   // Togo: data.id
            $table->string('provider_hashed_id')->nullable();           // Togo: data.hashed_id (used in redirect URL)

            // المبلغ والعملة
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3)->default('ILS');

            // الحالة
            $table->enum('status', ['pending', 'paid', 'failed', 'cancelled'])
                ->default('pending')
                ->index();

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            // الاستجابة الخام من Togo
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['provider', 'provider_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_orders');
    }
};

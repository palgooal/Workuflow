<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * settlement_requests — طلب يُنشئه المشترك ليطلب من دراهم تحويل صافي
 * التحصيلات الجاهزة (settlement_net_amount) إليه. المراجعة والدفع الفعلي
 * يدويان بالكامل من الأدمن. راجع docs/PAYMENT-COLLECTION.md.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settlement_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('total_amount', 12, 2);
            $table->char('currency', 3)->default('ILS');

            // pending → approved|rejected → (approved →) paid
            $table->string('status', 20)->default('pending')->index();

            $table->timestamp('requested_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            // سبب الرفض (إلزامي عند status=rejected) أو أي ملاحظة إدارية
            $table->text('admin_notes')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlement_requests');
    }
};

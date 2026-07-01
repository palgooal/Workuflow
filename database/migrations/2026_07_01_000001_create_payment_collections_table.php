<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_collections', function (Blueprint $table) {
            $table->id();

            // المستخدم (المشترك) الذي تُحصَّل الفاتورة نيابة عنه
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('invoice_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('client_id')
                ->constrained()
                ->cascadeOnDelete();

            // مزود بوابة الدفع (togo حالياً)
            $table->string('provider', 50)->default('togo');
            $table->string('provider_payment_id')->nullable()->index();

            $table->decimal('amount', 12, 2);
            $table->char('currency', 3)->default('ILS');

            // رسوم المنصة مقابل التحصيل نيابة عن المشترك + المبلغ الصافي المستحق له
            $table->decimal('platform_fee', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2);

            // pending: طلب دفع أُنشئ ولم يُحصَّل بعد
            // collected: تم تحصيل المبلغ من العميل عبر البوابة (لدى دراهم)
            // settled: تمت تسوية المبلغ يدوياً مع المشترك
            // failed: فشلت عملية الدفع
            // refunded: تم استرجاع المبلغ للعميل
            $table->string('status', 20)->default('pending')->index();

            $table->timestamp('collected_at')->nullable();
            $table->timestamp('settled_at')->nullable();

            // الاستجابة الخام من مزود الدفع + أي بيانات إضافية (timeline إلخ)
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['invoice_id', 'status']);
            $table->index(['provider', 'provider_payment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_collections');
    }
};

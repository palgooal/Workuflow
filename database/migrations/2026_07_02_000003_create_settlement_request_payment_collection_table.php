<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * settlement_request_payment_collection — جدول pivot يربط كل SettlementRequest
 * بكل PaymentCollection التي شملها وقت إنشاء الطلب (belongsToMany).
 *
 * لماذا belongsToMany لا hasMany: نفس PaymentCollection قد يظهر في أكثر من
 * SettlementRequest عبر الزمن (مثلاً إن رُفض طلب أول، يبقى التحصيل مؤهلاً
 * ويمكن إدراجه في طلب لاحق) — راجع SettlementRequestController للمنطق
 * الذي يستبعد أي تحصيل مرتبط بطلب لا يزال "مفتوحاً" (pending/approved).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settlement_request_payment_collection', function (Blueprint $table) {
            // ⚠️ اسم الجدول طويل — اسم الـ constraint الافتراضي الذي يولّده Laravel
            // (settlement_request_payment_collection_settlement_request_id_foreign)
            // يتجاوز حد MySQL البالغ 64 حرفاً لأسماء المُعرِّفات (Identifier). لذلك
            // نُمرِّر اسم constraint مختصراً صراحةً لكل مفتاح أجنبي هنا.
            $table->foreignId('settlement_request_id')
                ->constrained(indexName: 'srpc_settlement_request_id_fk')
                ->cascadeOnDelete();

            $table->foreignId('payment_collection_id')
                ->constrained(indexName: 'srpc_payment_collection_id_fk')
                ->cascadeOnDelete();

            $table->primary(['settlement_request_id', 'payment_collection_id'], 'srpc_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlement_request_payment_collection');
    }
};

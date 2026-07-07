<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add Togo Payment Fields to Clients Table
 *
 * يُضيف حقلين مطلوبين لإنشاء receiver_address خاص بكل عميل عند Togo
 * (بدل استخدام receiver_address ثابت للمشترك في كل عمليات الدفع — راجع
 * docs/PAYMENT-COLLECTION.md قسم "receiver_address لكل عميل"):
 *
 * - payment_name: اسم بديل بالإنجليزية (ASCII) يُرسَل لـ Togo عندما يكون
 *   اسم العميل الأساسي (name) بالعربية — Togo يرفض أي حرف غير ASCII.
 * - togo_receiver_address_id: يُخزَّن بعد أول إنشاء ناجح لـ receiver_address
 *   لهذا العميل تحديداً، ويُعاد استخدامه في كل عملية دفع تالية بدل إنشاء
 *   سجل جديد في كل مرة.
 *
 * address/city/country موجودة أصلاً منذ 2026_05_25_000001.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (! Schema::hasColumn('clients', 'payment_name')) {
                $table->string('payment_name', 100)->nullable()->after('name');
            }
            if (! Schema::hasColumn('clients', 'togo_receiver_address_id')) {
                $table->string('togo_receiver_address_id', 100)->nullable()->after('country');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $cols = [];

            if (Schema::hasColumn('clients', 'payment_name'))              $cols[] = 'payment_name';
            if (Schema::hasColumn('clients', 'togo_receiver_address_id'))  $cols[] = 'togo_receiver_address_id';

            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};

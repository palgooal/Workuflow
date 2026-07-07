<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add Togo Billing Fields to Users Table
 *
 * عنوان فوترة المشترك نفسه — يُستخدَم لإنشاء receiver_address خاص به عند
 * Togo عند دفع اشتراك الباقة (Pro/Business)، بنفس فكرة receiver_address
 * الخاص بكل عميل (راجع migration الفواتير المقابلة وdocs/PAYMENT-COLLECTION.md).
 * يُملأ مرة واحدة من الإعدادات ويُعاد استخدامه في كل تجديد/ترقية.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'payment_name')) {
                $table->string('payment_name', 100)->nullable()->after('phone');
            }
            if (! Schema::hasColumn('users', 'billing_address')) {
                $table->string('billing_address', 255)->nullable()->after('payment_name');
            }
            if (! Schema::hasColumn('users', 'billing_city')) {
                $table->string('billing_city', 100)->nullable()->after('billing_address');
            }
            if (! Schema::hasColumn('users', 'billing_country')) {
                $table->string('billing_country', 2)->nullable()->default('PS')->after('billing_city');
            }
            if (! Schema::hasColumn('users', 'togo_receiver_address_id')) {
                $table->string('togo_receiver_address_id', 100)->nullable()->after('billing_country');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = [];

            foreach (['payment_name', 'billing_address', 'billing_city', 'billing_country', 'togo_receiver_address_id'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $cols[] = $col;
                }
            }

            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};

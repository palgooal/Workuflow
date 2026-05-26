<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add Contact Fields to Clients Table
 *
 * يُضيف الحقول التكميلية للعميل (المنصب، الموقع، العنوان)
 * التي كانت موجودة في النموذج لكن مفقودة من قاعدة البيانات.
 *
 * Sprint 3 — Fix #CRM-001
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // لا تُضاف إذا كانت موجودة مسبقاً (safe migration)
            if (! Schema::hasColumn('clients', 'position')) {
                $table->string('position', 100)->nullable()->after('company');
            }
            if (! Schema::hasColumn('clients', 'website')) {
                $table->string('website', 255)->nullable()->after('position');
            }
            if (! Schema::hasColumn('clients', 'address')) {
                $table->string('address', 255)->nullable()->after('website');
            }
            if (! Schema::hasColumn('clients', 'city')) {
                $table->string('city', 100)->nullable()->after('address');
            }
            if (! Schema::hasColumn('clients', 'country')) {
                $table->string('country', 2)->nullable()->default('PS')->after('city');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $cols = [];

            if (Schema::hasColumn('clients', 'position')) $cols[] = 'position';
            if (Schema::hasColumn('clients', 'website'))  $cols[] = 'website';
            if (Schema::hasColumn('clients', 'address'))  $cols[] = 'address';
            if (Schema::hasColumn('clients', 'city'))     $cols[] = 'city';
            if (Schema::hasColumn('clients', 'country'))  $cols[] = 'country';

            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};

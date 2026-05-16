<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('contract_value', 12, 2)->nullable()->after('description')
                  ->comment('قيمة العقد المتفق عليها مع العميل');
            $table->decimal('expense_budget', 12, 2)->nullable()->after('contract_value')
                  ->comment('ميزانية التكاليف المخططة');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['contract_value', 'expense_budget']);
        });
    }
};

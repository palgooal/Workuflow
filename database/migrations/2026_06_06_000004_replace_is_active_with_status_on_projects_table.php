<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->enum('status', ['active', 'completed', 'on_hold', 'cancelled'])
                  ->default('active')
                  ->after('type')
                  ->comment('حالة المشروع');
        });

        // ترحيل البيانات: is_active=1 → active, is_active=0 → on_hold
        DB::table('projects')->where('is_active', true)->update(['status' => 'active']);
        DB::table('projects')->where('is_active', false)->update(['status' => 'on_hold']);

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('type');
        });

        DB::table('projects')->where('status', 'active')->update(['is_active' => true]);
        DB::table('projects')->whereIn('status', ['completed', 'on_hold', 'cancelled'])->update(['is_active' => false]);

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};

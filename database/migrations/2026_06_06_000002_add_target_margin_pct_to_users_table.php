<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('target_margin_pct')
                  ->default(40)
                  ->after('timezone')
                  ->comment('الهامش المستهدف لكل خدمة (0-100%)');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('target_margin_pct');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_service', function (Blueprint $table) {
            $table->unsignedTinyInteger('target_margin_pct')
                  ->nullable()
                  ->after('notes')
                  ->comment('هامش مستهدف مخصص لهذه الخدمة — إذا null يُستخدم إعداد المستخدم العام');
        });
    }

    public function down(): void
    {
        Schema::table('project_service', function (Blueprint $table) {
            $table->dropColumn('target_margin_pct');
        });
    }
};

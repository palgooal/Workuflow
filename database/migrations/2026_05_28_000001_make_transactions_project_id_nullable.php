<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix: transactions.project_id must be nullable
 *
 * عمود project_id في جدول transactions كان NOT NULL بسبب schema drift.
 * هذه المايغريشن تُصحِّح العمود ليقبل NULL (للمعاملات غير المرتبطة بمشروع).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->char('project_id', 26)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->char('project_id', 26)->nullable(false)->change();
        });
    }
};

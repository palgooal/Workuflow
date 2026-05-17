<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_service', function (Blueprint $table) {
            $table->string('team_member_id')->nullable()->after('notes');
            $table->decimal('team_cost', 12, 2)->nullable()->after('team_member_id');
            $table->boolean('team_cost_paid')->default(false)->after('team_cost');

            $table->foreign('team_member_id')
                ->references('id')
                ->on('team_members')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('project_service', function (Blueprint $table) {
            $table->dropForeign(['team_member_id']);
            $table->dropColumn(['team_member_id', 'team_cost', 'team_cost_paid']);
        });
    }
};

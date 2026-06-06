<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── 1. إنشاء الجدول الجديد ───────────────────────────────────────
        Schema::create('project_service_members', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('project_service_id');
            $table->foreign('project_service_id')
                ->references('id')
                ->on('project_service')
                ->cascadeOnDelete();

            $table->string('team_member_id')->nullable();
            $table->foreign('team_member_id')
                ->references('id')
                ->on('team_members')
                ->nullOnDelete();

            $table->decimal('team_cost', 12, 2)->nullable();
            $table->boolean('team_cost_paid')->default(false);

            $table->timestamps();

            $table->index('project_service_id');
            $table->index('team_member_id');
        });

        // ─── 2. ترحيل البيانات الحالية ───────────────────────────────────
        DB::table('project_service')
            ->whereNotNull('team_member_id')
            ->orderBy('id')
            ->chunk(200, function ($rows) {
                $now = now();
                $inserts = $rows->map(fn ($row) => [
                    'project_service_id' => $row->id,
                    'team_member_id'     => $row->team_member_id,
                    'team_cost'          => $row->team_cost,
                    'team_cost_paid'     => $row->team_cost_paid ?? false,
                    'created_at'         => $row->created_at ?? $now,
                    'updated_at'         => $row->updated_at ?? $now,
                ])->toArray();

                if (! empty($inserts)) {
                    DB::table('project_service_members')->insert($inserts);
                }
            });

        // ─── 3. حذف الأعمدة القديمة من project_service ───────────────────
        Schema::table('project_service', function (Blueprint $table) {
            $table->dropForeign(['team_member_id']);
            $table->dropColumn(['team_member_id', 'team_cost', 'team_cost_paid']);
        });
    }

    public function down(): void
    {
        // استعادة الأعمدة القديمة
        Schema::table('project_service', function (Blueprint $table) {
            $table->string('team_member_id')->nullable()->after('notes');
            $table->decimal('team_cost', 12, 2)->nullable()->after('team_member_id');
            $table->boolean('team_cost_paid')->default(false)->after('team_cost');

            $table->foreign('team_member_id')
                ->references('id')
                ->on('team_members')
                ->nullOnDelete();
        });

        // ترحيل عكسي — أول منفذ لكل خدمة فقط
        DB::table('project_service_members')
            ->orderBy('id')
            ->chunk(200, function ($rows) {
                foreach ($rows as $member) {
                    DB::table('project_service')
                        ->where('id', $member->project_service_id)
                        ->whereNull('team_member_id')
                        ->update([
                            'team_member_id'  => $member->team_member_id,
                            'team_cost'       => $member->team_cost,
                            'team_cost_paid'  => $member->team_cost_paid,
                        ]);
                }
            });

        Schema::dropIfExists('project_service_members');
    }
};

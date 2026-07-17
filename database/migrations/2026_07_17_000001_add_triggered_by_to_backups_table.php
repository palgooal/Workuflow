<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * يضيف عمود triggered_by (manual / scheduled) إلى جدول backups — المرحلة
 * الخامسة (النسخ الاحتياطي التلقائي). عمداً string وليس boolean (راجع
 * App\Support\Enums\BackupTrigger).
 *
 * قبل هذه المرحلة، كان العمود الوحيد الدال على المصدر هو triggered_by_user_id
 * (تعليق الـMigration الأصلية: "من أطلق النسخة: null = جدولة تلقائية") —
 * البيانات التاريخية تُهاجَر (backfill) وفق نفس هذا التمييز الفعلي، فلا تُفقَد
 * أي معلومة قديمة عن مصدر النسخة.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('backups', function (Blueprint $table) {
            $table->string('triggered_by', 20)->default('manual')->after('triggered_by_user_id');
        });

        // Backfill: النسخ القديمة بدون triggered_by_user_id كانت بالضرورة مجدولة
        // (راجع تعليق triggered_by_user_id في الـMigration الأصلية).
        DB::table('backups')
            ->whereNull('triggered_by_user_id')
            ->update(['triggered_by' => 'scheduled']);

        Schema::table('backups', function (Blueprint $table) {
            $table->index('triggered_by');
        });
    }

    public function down(): void
    {
        Schema::table('backups', function (Blueprint $table) {
            $table->dropIndex(['triggered_by']);
            $table->dropColumn('triggered_by');
        });
    }
};

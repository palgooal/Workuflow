<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // رقم الهاتف بصيغة E.164 — مثال: +966501234567
            // nullable: المستخدمون الحاليون لن يتأثروا
            // unique: لا حسابين بنفس الرقم (null لا يُحسب في UNIQUE index)
            $table->string('phone', 30)->nullable()->unique()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['phone']);
            $table->dropColumn('phone');
        });
    }
};

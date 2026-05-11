<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('currency', 3)->default('SAR')->after('email');
            $table->string('timezone')->default('Asia/Riyadh')->after('currency');
            $table->enum('subscription_plan', ['free', 'pro', 'business'])->default('free')->after('timezone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['currency', 'timezone', 'subscription_plan']);
        });
    }
};

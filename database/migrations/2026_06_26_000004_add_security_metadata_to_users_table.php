<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('registration_ip', 45)->nullable()->after('onboarding_dismissed_at');
            $table->text('registration_user_agent')->nullable()->after('registration_ip');
            $table->timestamp('last_login_at')->nullable()->after('registration_user_agent');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'registration_ip',
                'registration_user_agent',
                'last_login_at',
                'last_login_ip',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CONVERSION-01 Phase 2 — Email Verification Grace Period
 *
 * email_verification_grace_until:
 *   إلى متى تمتد فترة السماح للمستخدم المدفوع غير الموثّق.
 *   يُضبط بعد أول دفعة ناجحة لمدة 7 أيام.
 *   NULL = لا توجد فترة سماح نشطة.
 *
 * email_verification_grace_used_at:
 *   وقت استخدام فترة السماح لأول مرة.
 *   يُضبط عند أول تفعيل مدفوع — لا يُعاد ضبطه أبداً.
 *   NULL = لم تُمنح فترة سماح بعد.
 *   NOT NULL = استُخدمت فترة السماح (حتى لو انتهت أو تم التحقق).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('email_verification_grace_until')
                ->nullable()
                ->after('email_verified_at')
                ->comment('فترة سماح الدفع — بعد هذا الوقت يُطبَّق verified middleware');

            $table->timestamp('email_verification_grace_used_at')
                ->nullable()
                ->after('email_verification_grace_until')
                ->comment('وقت أول منح لفترة السماح — لمنع إعادة الاستخدام مدى الحياة');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'email_verification_grace_until',
                'email_verification_grace_used_at',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * سجل تتبّع الاحتفاظ بالبيانات (Data Retention Ledger)
 *
 * الغرض: تسجيل تاريخ إغلاق الحساب/معالجة طلب حذف البيانات وتاريخ استحقاق
 * التطهير النهائي، بشكل قابل للتدقيق وغير معتمد على الذاكرة البشرية —
 * وفق سياسة الاحتفاظ الواردة في docs/legal/Privacy-Policy.md §8 و
 * docs/legal/Data-Deletion.md §3 (سنة واحدة افتراضية بعد إغلاق الحساب،
 * إلا لالتزام قانوني/قضائي/محاسبي أطول).
 *
 * هذا الجدول للتتبّع والتدقيق فقط — لا يوجد أي Job يحذف بيانات فعلياً
 * بناءً عليه حتى الآن (انظر ReportDueDataRetentionPurges — تقرير Dry-Run فقط).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_retention_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_email_snapshot', 255)->nullable(); // لضمان التتبّع حتى لو حُذف صف المستخدم مستقبلاً
            $table->timestamp('closed_at');                          // تاريخ إغلاق الحساب / معالجة طلب الحذف
            $table->timestamp('purge_due_at');                       // تاريخ استحقاق التطهير النهائي (افتراضياً closed_at + سنة)
            $table->boolean('legal_hold')->default(false);           // تعليق التطهير لالتزام قانوني/قضائي أطول
            $table->text('legal_hold_reason')->nullable();
            $table->string('status', 20)->default('pending');        // pending | purged | legal_hold | cancelled
            $table->timestamp('purged_at')->nullable();
            $table->unsignedBigInteger('triggered_by_admin_id')->nullable(); // من نفّذ إجراء حذف البيانات
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'purge_due_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_retention_ledger');
    }
};

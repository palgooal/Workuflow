<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Upgrade Clients Table — CRM V2
 *
 * يُوسِّع الجدول البسيط (16.2) إلى schema كامل للـ CRM المتقدم.
 * المرجع: docs/CLIENTS-CRM-SPEC-V2.md — Sprint 1, S1.1
 *
 * ملاحظة: يحتفظ بعمود is_active للتوافق مع الكود القديم.
 * status يستخدم VARCHAR بدل ENUM (C-03 Fix — zero-downtime migrations).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {

            // ── معرف عام (ULID) — للروابط الخارجية وبوابة العميل ──
            // يُضاف بعد id مباشرةً
            $table->char('public_id', 26)->nullable()->unique()->after('id');

            // ── الحالة والمصدر ──
            // VARCHAR بدل ENUM (C-03 Fix: يسمح بإضافة قيم جديدة بدون ALTER TABLE كامل)
            $table->string('status', 20)->default('active')->after('is_active');
            $table->string('source', 20)->default('direct')->after('status');

            // ── الأرشفة (منفصلة عن الحذف الناعم) ──
            $table->boolean('is_archived')->default(false)->after('source');

            // ── المقاييس المالية (Denormalized Aggregates) ──
            // تُحدَّث بـ atomic increment عند كل دفعة (C-02 Fix)
            $table->decimal('total_revenue', 14, 2)->default(0)->after('is_archived');
            $table->decimal('total_paid', 14, 2)->default(0)->after('total_revenue');
            $table->unsignedInteger('invoice_count')->default(0)->after('total_paid');

            // ── مؤشر الصحة (يُخزَّن مؤقتاً للعرض السريع) ──
            $table->unsignedTinyInteger('health_score')->nullable()->after('invoice_count');

            // ── تواريخ التتبع ──
            $table->timestamp('last_payment_at')->nullable()->after('health_score');
            $table->timestamp('last_contact_at')->nullable()->after('last_payment_at');
        });

        // ── تهيئة public_id للسجلات الموجودة ──
        DB::table('clients')->whereNull('public_id')->orderBy('id')->each(function ($client) {
            DB::table('clients')
                ->where('id', $client->id)
                ->update(['public_id' => Str::ulid()->toString()]);
        });

        // ── مؤشرات الأداء المُضافة ──
        Schema::table('clients', function (Blueprint $table) {
            $table->index(['user_id', 'status'],      'clients_user_status_idx');
            $table->index(['user_id', 'is_archived'], 'clients_user_archived_idx');
            $table->index(['user_id', 'health_score'],'clients_user_health_idx');
            $table->index('last_contact_at',           'clients_last_contact_idx');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex('clients_user_status_idx');
            $table->dropIndex('clients_user_archived_idx');
            $table->dropIndex('clients_user_health_idx');
            $table->dropIndex('clients_last_contact_idx');

            $table->dropColumn([
                'public_id', 'status', 'source', 'is_archived',
                'total_revenue', 'total_paid', 'invoice_count',
                'health_score', 'last_payment_at', 'last_contact_at',
            ]);
        });
    }
};

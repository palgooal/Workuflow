<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * سجل تحصيل واحد فقط لكل فاتورة — يمنع إنشاء PaymentCollection متعددة
     * لنفس invoice_id على مستوى قاعدة البيانات (وليس فقط منطق التطبيق)،
     * ويجعل PaymentCollection::firstOrCreate(['invoice_id' => ...]) آمناً
     * فعلياً ضد Race Conditions (طلبَي checkout متزامنَين لنفس الفاتورة).
     *
     * قبل إضافة الـ unique index، ننظّف أي تكرارات موجودة مسبقاً (لو وُجدت)
     * بنفس المنطق تماماً — حتى لا تفشل الـ migration على بيئات فيها بيانات
     * اختبار قديمة. لا يمس هذا أي صف في invoices أو transactions، ولا يغيّر
     * حالة أي فاتورة — يحذف فقط صفوف payment_collections الزائدة.
     */
    public function up(): void
    {
        $this->cleanupDuplicateCollections();

        Schema::table('payment_collections', function (Blueprint $table) {
            $table->unique('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::table('payment_collections', function (Blueprint $table) {
            $table->dropUnique(['invoice_id']);
        });
    }

    /**
     * يحتفظ بسجل واحد فقط لكل invoice_id، بالأفضلية التالية:
     *   1) status = collected
     *   2) أحدث collected_at
     *   3) أحدث updated_at
     *   4) أحدث id
     * ويحذف باقي السجلات المكررة لنفس invoice_id فقط.
     */
    private function cleanupDuplicateCollections(): void
    {
        $duplicateInvoiceIds = DB::table('payment_collections')
            ->select('invoice_id')
            ->groupBy('invoice_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('invoice_id');

        if ($duplicateInvoiceIds->isEmpty()) {
            Log::info('payment_collections: فحص التكرارات قبل unique(invoice_id) — لا توجد تكرارات، لا حاجة لتنظيف.');
            return;
        }

        $deletedTotal = 0;

        foreach ($duplicateInvoiceIds as $invoiceId) {
            $rows = DB::table('payment_collections')
                ->where('invoice_id', $invoiceId)
                ->orderByRaw("(status = 'collected') DESC")
                ->orderByDesc('collected_at')
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->get();

            // أول صف بعد الترتيب هو الفائز حسب الأفضلية — نحتفظ به ونحذف الباقي
            $winner  = $rows->first();
            $loserIds = $rows->skip(1)->pluck('id');

            if ($loserIds->isNotEmpty()) {
                DB::table('payment_collections')->whereIn('id', $loserIds)->delete();
                $deletedTotal += $loserIds->count();

                Log::info('payment_collections: تنظيف تكرار invoice_id', [
                    'invoice_id'  => $invoiceId,
                    'kept_id'     => $winner->id,
                    'kept_status' => $winner->status,
                    'deleted_ids' => $loserIds->all(),
                ]);
            }
        }

        Log::warning(
            "payment_collections: تم حذف {$deletedTotal} سجل مكرر من أصل "
            . $duplicateInvoiceIds->count() . ' فاتورة كانت متأثرة (قبل إضافة unique(invoice_id)).'
        );
    }
};

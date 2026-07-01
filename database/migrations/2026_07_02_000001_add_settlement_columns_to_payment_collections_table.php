<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * يضيف أعمدة "التسوية بالشيكل" على payment_collections.
 *
 * السبب: بوابة الدفع (Togo) تُحصِّل وتُسوِّي الأموال فعلياً بالشيكل (ILS)
 * دائماً، بغض النظر عن عملة الفاتورة (amount/currency). قبل هذا التعديل
 * كان net_amount/platform_fee يُحسبان بنفس عملة الفاتورة ظلماً — ما يجعل
 * عرضهما مضلِّلاً لأي فاتورة بعملة غير الشيكل. راجع docs/PAYMENT-COLLECTION.md
 * قسم "عملة الفاتورة مقابل عملة التسوية".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_collections', function (Blueprint $table) {
            $table->char('settlement_currency', 3)->default('ILS')->after('currency');
            $table->decimal('settlement_amount', 12, 2)->nullable()->after('settlement_currency');
            $table->decimal('settlement_platform_fee', 12, 2)->default(0)->after('settlement_amount');
            $table->decimal('settlement_net_amount', 12, 2)->nullable()->after('settlement_platform_fee');
            $table->decimal('exchange_rate', 12, 6)->nullable()->after('settlement_net_amount');
        });

        $this->backfillExistingRows();
    }

    public function down(): void
    {
        Schema::table('payment_collections', function (Blueprint $table) {
            $table->dropColumn([
                'settlement_currency',
                'settlement_amount',
                'settlement_platform_fee',
                'settlement_net_amount',
                'exchange_rate',
            ]);
        });
    }

    /**
     * توافق مع السجلات القديمة (قبل هذا الإصدار):
     *
     * - currency = ILS  → عملة الفاتورة كانت أصلاً الشيكل، فالتسوية القديمة
     *   (net_amount/platform_fee) كانت صحيحة فعلياً بالصدفة. تُنسخ 1:1.
     * - currency != ILS → القيم القديمة (net_amount/platform_fee) كانت
     *   محسوبة خطأً بعملة الفاتورة، ولا يجوز اعتمادها كمبلغ تسوية بالشيكل.
     *   تُترك settlement_amount/settlement_net_amount = NULL — أي سجل من
     *   هذا النوع بحالة "collected" يحتاج مراجعة يدوية من الأدمن (لوحة
     *   الإدارة → تحديد مبلغ التسوية يدوياً) قبل السماح بالتسوية.
     */
    private function backfillExistingRows(): void
    {
        DB::table('payment_collections')
            ->where('currency', 'ILS')
            ->update([
                'settlement_currency'     => 'ILS',
                'settlement_amount'       => DB::raw('amount'),
                'settlement_platform_fee' => DB::raw('platform_fee'),
                'settlement_net_amount'   => DB::raw('net_amount'),
                'exchange_rate'           => 1,
            ]);

        DB::table('payment_collections')
            ->where('currency', '!=', 'ILS')
            ->update([
                'settlement_currency'     => 'ILS',
                'settlement_amount'       => null,
                'settlement_platform_fee' => 0,
                'settlement_net_amount'   => null,
                'exchange_rate'           => null,
            ]);

        $needsReview = DB::table('payment_collections')
            ->where('currency', '!=', 'ILS')
            ->whereIn('status', ['collected', 'settled'])
            ->count();

        if ($needsReview > 0) {
            Log::warning('PaymentCollection settlement backfill: legacy non-ILS records need admin review before settlement', [
                'count' => $needsReview,
            ]);
        }
    }
};

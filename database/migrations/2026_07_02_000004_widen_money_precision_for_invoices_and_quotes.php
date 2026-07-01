<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * توسيع دقة أعمدة المبالغ في الفواتير والعروض من decimal(x,2) إلى decimal(x+1,3).
 *
 * السبب: عملات "الفلس" المدعومة في النظام (JOD/KWD/BHD/OMR) لها فعلياً ثلاث
 * خانات عشرية (1 دينار = 1000 فلس)، بينما كانت كل أعمدة المبالغ هنا مقيّدة
 * بخانتين فقط — ما يعني أن أي سعر/مبلغ بثلاث خانات لهذه العملات كان سيُقرَّب
 * صامتاً عند الحفظ (12.345 → 12.35) حتى لو سمحت الواجهة بإدخاله.
 *
 * ⚠️ نستخدم DB::statement بدل Schema::table()->change() لأن حزمة
 * doctrine/dbal غير مُثبَّتة في هذا المشروع (مطلوبة لـ ->change() في Laravel).
 *
 * لا تُغيَّر tax_rate (نسبة مئوية، ليست مبلغاً بعملة) ولا أي عمود آخر خارج
 * نطاق الفواتير/العروض (transactions, wallets, debts, budgets... تبقى كما هي).
 */
return new class extends Migration
{
    private array $columns = [
        'invoices' => [
            'subtotal'   => 'DECIMAL(13,3) NOT NULL DEFAULT 0',
            'tax_amount' => 'DECIMAL(13,3) NOT NULL DEFAULT 0',
            'discount'   => 'DECIMAL(13,3) NOT NULL DEFAULT 0',
            'total'      => 'DECIMAL(13,3) NOT NULL DEFAULT 0',
        ],
        'invoice_items' => [
            'quantity'   => 'DECIMAL(11,3) NOT NULL DEFAULT 1',
            'unit_price' => 'DECIMAL(13,3) NOT NULL DEFAULT 0',
            'total'      => 'DECIMAL(13,3) NOT NULL DEFAULT 0',
        ],
        'quotes' => [
            'subtotal'   => 'DECIMAL(13,3) NOT NULL DEFAULT 0',
            'tax_amount' => 'DECIMAL(13,3) NOT NULL DEFAULT 0',
            'discount'   => 'DECIMAL(13,3) NOT NULL DEFAULT 0',
            'total'      => 'DECIMAL(13,3) NOT NULL DEFAULT 0',
        ],
        'quote_items' => [
            'quantity'   => 'DECIMAL(11,3) NOT NULL DEFAULT 1',
            'unit_price' => 'DECIMAL(13,3) NOT NULL DEFAULT 0',
            'total'      => 'DECIMAL(13,3) NOT NULL DEFAULT 0',
        ],
    ];

    private array $originalColumns = [
        'invoices' => [
            'subtotal'   => 'DECIMAL(12,2) NOT NULL DEFAULT 0',
            'tax_amount' => 'DECIMAL(12,2) NOT NULL DEFAULT 0',
            'discount'   => 'DECIMAL(12,2) NOT NULL DEFAULT 0',
            'total'      => 'DECIMAL(12,2) NOT NULL DEFAULT 0',
        ],
        'invoice_items' => [
            'quantity'   => 'DECIMAL(10,2) NOT NULL DEFAULT 1',
            'unit_price' => 'DECIMAL(12,2) NOT NULL DEFAULT 0',
            'total'      => 'DECIMAL(12,2) NOT NULL DEFAULT 0',
        ],
        'quotes' => [
            'subtotal'   => 'DECIMAL(12,2) NOT NULL DEFAULT 0',
            'tax_amount' => 'DECIMAL(12,2) NOT NULL DEFAULT 0',
            'discount'   => 'DECIMAL(12,2) NOT NULL DEFAULT 0',
            'total'      => 'DECIMAL(12,2) NOT NULL DEFAULT 0',
        ],
        'quote_items' => [
            'quantity'   => 'DECIMAL(10,2) NOT NULL DEFAULT 1',
            'unit_price' => 'DECIMAL(12,2) NOT NULL DEFAULT 0',
            'total'      => 'DECIMAL(12,2) NOT NULL DEFAULT 0',
        ],
    ];

    public function up(): void
    {
        foreach ($this->columns as $table => $cols) {
            foreach ($cols as $column => $definition) {
                DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` {$definition}");
            }
        }
    }

    public function down(): void
    {
        // ⚠️ تراجع يدوي: أي قيمة بثلاث خانات أُدخلت بعد up() ستُقرَّب صامتاً
        // إلى خانتين عند التراجع — سلوك متوقع لـ down() وليس فقداناً غير مقصود.
        foreach ($this->originalColumns as $table => $cols) {
            foreach ($cols as $column => $definition) {
                DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` {$definition}");
            }
        }
    }
};

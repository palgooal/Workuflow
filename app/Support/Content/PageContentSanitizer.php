<?php

namespace App\Support\Content;

use Mews\Purifier\Facades\Purifier;

/**
 * تعقيم أي محتوى HTML قادم من محرر نصوص منسّق (Rich Text) ضد XSS، عبر
 * بروفايلات mews/purifier المعرَّفة في config/purifier.php.
 *
 * البروفايل الافتراضي "page_content" يُستخدَم لصفحات نظام إدارة المحتوى
 * (App\Models\Page::$content) — في PageResource (عند الحفظ من لوحة Filament)
 * وفي LegalPagesSeeder (عند ترحيل المحتوى الحالي). يسمح فقط بعناصر نثر
 * آمنة، ويمنع صراحة <script>/<iframe>/Event Attributes.
 *
 * بروفايل "invoice_notes" أضيق منه عمداً — لحقلَي notes/terms في الفواتير
 * (محرر Quill البسيط في resources/views/invoices/{create,edit}.blade.php).
 */
class PageContentSanitizer
{
    public static function clean(string $html, string $profile = 'page_content'): string
    {
        return Purifier::clean($html, $profile);
    }

    /**
     * تُستخدَم عند *عرض* حقلَي notes/terms في الفواتير (show/pdf/public) —
     * وليس فقط عند الحفظ. القيمة المخزَّنة في invoices.notes/terms قد تكون:
     *
     * 1) HTML مُعقَّم مسبقاً بالفعل (فاتورة أُنشئت/عُدِّلت عبر محرر Quill
     *    بعد إضافة هذه الميزة) — تُعاد كما هي فعلياً (تعقيم HTML نظيف بالفعل
     *    عملية لا تُغيّر شيئاً).
     * 2) نص عادي قديم (فاتورة من قبل هذه الميزة، بلا أي وسم HTML على
     *    الإطلاق) — يُحوَّل أولاً بأمان (escape + تحويل الأسطر لـ<br>) قبل
     *    تمريره لنفس بروفايل invoice_notes، حتى لا تُعرَض فواصل الأسطر
     *    القديمة كأنها اختفت، ولا يُعرَض أي "<" حرفي كوسم HTML خام بالخطأ.
     *
     * التعقيم هنا يُعاد تشغيله على كل عرض (وليس فقط عند الحفظ) تحديداً بسبب
     * الحالة (2) — لا يوجد Backfill/Migration لسجلات الفواتير القديمة بعد.
     */
    public static function renderInvoiceField(?string $value): string
    {
        if (blank($value)) {
            return '';
        }

        if (! str_contains($value, '<')) {
            $value = nl2br(e($value));
        }

        return static::clean($value, 'invoice_notes');
    }
}

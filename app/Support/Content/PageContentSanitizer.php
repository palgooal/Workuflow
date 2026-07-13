<?php

namespace App\Support\Content;

use Mews\Purifier\Facades\Purifier;

/**
 * تعقيم محتوى صفحات نظام إدارة المحتوى (App\Models\Page::$content) ضد XSS.
 *
 * يُستخدَم في PageResource (عند الحفظ من لوحة Filament) وفي LegalPagesSeeder
 * (عند ترحيل المحتوى الحالي) — نقطة تعقيم واحدة موحّدة، بروفايل
 * "page_content" المعرَّف في config/purifier.php: يسمح فقط بعناصر نثر
 * آمنة، ويمنع صراحة <script>/<iframe>/Event Attributes.
 */
class PageContentSanitizer
{
    public static function clean(string $html): string
    {
        return Purifier::clean($html, 'page_content');
    }
}

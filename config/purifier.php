<?php

/*
|--------------------------------------------------------------------------
| إعدادات mews/purifier — تعقيم محتوى نظام إدارة المحتوى المصغّر
|--------------------------------------------------------------------------
|
| بروفايل "page_content" هو المستخدَم فعلياً في App\Support\Content\
| PageContentSanitizer لتعقيم حقل pages.content قبل الحفظ. يسمح فقط
| بعناصر نثر آمنة (فقرات، عناوين، قوائم، جداول، روابط) ويمنع صراحة أي
| <script>، <iframe>، أو Event Attributes (onclick وما شابه) — أي عنصر
| أو خاصية غير مذكورة في القائمة تُحذف تلقائياً من قبل HTMLPurifier.
|
| هذا الملف يُغني عن أمر vendor:publish الخاص بالحزمة (كُتب يدوياً بنفس
| البنية المتوقَّعة من mews/purifier). شغّل `composer require mews/purifier`
| على الخادم الفعلي قبل الاعتماد على هذا الملف.
|
*/

return [

    'encoding'         => 'UTF-8',
    'finalize'         => true,
    'ignoreNonStrings' => false,
    'cachePath'        => storage_path('app/purifier'),
    'cacheFileMode'    => 0755,

    'settings' => [

        'default' => [
            'HTML.Doctype'             => 'HTML 4.01 Transitional',
            'HTML.Allowed'             => 'div,b,strong,i,em,u,a[href|title],ul,ol,li,p,br,span,img[width|height|alt|src]',
            'AutoFormat.AutoParagraph' => true,
            'AutoFormat.RemoveEmpty'   => true,
        ],

        // بروفايل محتوى الصفحات (App\Models\Page::$content) — الأكثر تقييداً عمداً
        //
        // ملاحظة: لا تُضِف 'HTML.AllowedAttributes' هنا بجانب 'HTML.Allowed'.
        // HTML.Allowed يحدد أصلاً الخصائص المسموحة لكل عنصر عبر الصياغة
        // element[attr|attr] (مثل a[href|title|id]) — وإضافة
        // HTML.AllowedAttributes كقائمة عامة منفصلة تجعلها تطبَّق على كل
        // عناصر الـDoctype (بما فيها عناصر غير مذكورة أصلاً في Allowed، مثل
        // img)، وأي عنصر له خاصية "مطلوبة" (مثل img/src) غير موجودة في تلك
        // القائمة العامة يُسقِط HTMLPurifier العنصر بالكامل مع تحذير
        // (E_USER_WARNING) يتحول عند Laravel إلى استثناء فعلي في الاختبارات.
        'page_content' => [
            'HTML.Doctype'             => 'HTML 4.01 Transitional',
            'HTML.Allowed'             => 'p,h2,h3,h4,ul,ol,li,table,thead,tbody,tr,th,td,strong,em,b,i,a[href|title|id],hr,br,dl,dt,dd,blockquote,span[id]',
            'HTML.TargetBlank'         => false,
            'HTML.SafeIframe'          => false,
            'Attr.AllowedFrameTargets' => [],
            'AutoFormat.RemoveEmpty'   => true,
            'AutoFormat.AutoParagraph' => false,
            'CSS.AllowedProperties'    => '',
            'URI.AllowedSchemes'       => ['http' => true, 'https' => true, 'mailto' => true],
        ],

    ],

];

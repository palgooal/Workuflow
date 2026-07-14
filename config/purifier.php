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

        // بروفايل حقلَي "ملاحظات للعميل" و"الشروط والأحكام" في الفواتير
        // (محرر Quill في resources/views/invoices/{create,edit}.blade.php).
        // أضيق من page_content عمداً — بدون جداول/صور، لكن يسمح بعناوين
        // فرعية (h2/h3) واقتباس وخط يتوسطه نص، إضافة لتنسيق نصي بسيط.
        // AutoParagraph مفعَّل لتحويل أي قيم نصية قديمة (قبل هذه الميزة،
        // مخزَّنة كنص عادي بدون أي وسم HTML) إلى فقرات آمنة بدل أن تبقى
        // بلا وسم <p> عند عرضها كـHTML خام.
        //
        // ملاحظة تحديث (أدوات محاذاة/لون/تظليل الجديدة في شريط Quill):
        // أدوات المحاذاة واللون والتظليل في Quill تُخرِج تنسيقاً عبر
        // style="..." inline (بعد ضبط محرر Quill في create/edit.blade.php
        // على attributors/style بدل الافتراضي attributors/class — راجع
        // resources/views/invoices/partials/quill-link-fix.blade.php).
        // لذلك نسمح صراحة بخاصية style على span/p/h2/h3/li/blockquote، لكن
        // نُقيِّد CSS.AllowedProperties لثلاث خصائص فقط (محاذاة/لون نص/لون
        // خلفية) — أي خاصية CSS أخرى (مثل position أو url()) تُسقَط تلقائياً
        // حتى لو أُدرِجت يدوياً في HTML خام يتجاوز واجهة Quill.
        //
        // ملاحظة تصحيح ثالثة (قيد معروف): لا نضيف [style] لعناصر
        // strong/em/b/i/u/s — هذه عناصر "formatting" منطقياً في HTMLPurifier
        // (Legacy/Presentation modules)، وتبيّن بالاختبار الحي أن خاصية style
        // تُحذَف منها دوماً بصرف النظر عن السماح بها في HTML.Allowed (على
        // الأرجح لأن تعريفها الداخلي في تلك الوحدات يتجاوز إضافة style من
        // القائمة المختصرة). العناصر الأخرى (p/h2/h3/li/blockquote/span)
        // ليست "formatting" وتحافظ على style بشكل سليم — تأكدنا من ذلك حياً
        // (محاذاة الاقتباس نجحت). النتيجة العملية: تنسيق اللون/التظليل يُحفَظ
        // بشكل موثوق للنص العادي (يُغلَّف بـspan)، لكن قد يُفقَد إن جُمع لون
        // النص مع غامق/مائل/تسطير/خط-يتوسطه على نفس الحرف بالضبط — قيد معروف
        // في HTMLPurifier نفسه، وليس خطأً في هذا الإعداد.
        'invoice_notes' => [
            'HTML.Doctype'             => 'HTML 4.01 Transitional',
            'HTML.Allowed'             => 'p[style],h2[style],h3[style],br,strong,em,b,i,u,s,ul,ol,li[style],a[href|title],blockquote[style],span[style]',
            'HTML.TargetBlank'         => true,
            'HTML.SafeIframe'          => false,
            'Attr.AllowedFrameTargets' => [],
            'AutoFormat.RemoveEmpty'   => true,
            'AutoFormat.AutoParagraph' => true,
            'CSS.AllowedProperties'    => 'text-align,color,background-color',
            'URI.AllowedSchemes'       => ['http' => true, 'https' => true, 'mailto' => true],
        ],

    ],

];

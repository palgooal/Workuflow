{{--
    إصلاح مشترك لأداة إدراج الروابط في محرر Quill (يُستخدَم في صفحتَي
    إنشاء/تعديل الفاتورة عبر @include لتفادي تكرار نفس المنطق في ملفين).

    المشكلة الجذرية: معالج الرابط الافتراضي في Quill 1.3.7 يملأ حقل
    الرابط في نافذة الإدراج بالنص المحدَّد نفسه (وليس بقيمة رابط فعلية) —
    راجع toolbar handler الافتراضي في themes/base.js:
    `preview = this.quill.getText(range); tooltip.edit('link', preview)`.
    فإن كتب المستخدم رابطاً دون تفريغ الحقل أولاً يُدمَج النص المحدَّد مع
    الرابط داخل href الناتج. حالة حقيقية واجهناها أثناء تدقيق هذه الصفحة:
    href="راجع https://darahum.com/terms". كما أن واجهة نافذة الرابط
    بالكامل بالإنجليزية (Visit URL / Edit / Save / Remove) داخل موقع عربي.

    الحل:
    1) استبدال معالج زر الرابط في toolbar Quill: لا يُملأ حقل الرابط
       بالنص المحدَّد إلا إذا كان هذا النص رابطاً صالحاً بذاته، فيبقى
       النص المحدَّد نصاً ظاهراً للرابط فقط دون أن يختلط بـhref.
    2) استبدال tooltip.save(): تحقّق وتطبيع (trim + إضافة https:// تلقائياً
       لنطاق بلا بروتوكول + رفض القيم الفارغة/غير الصالحة) بدل الحفظ
       الحرفي لقيمة الحقل.
    3) تعريب نصوص الواجهة عبر CSS (Quill 1.3.7 يعرض هذه النصوص بواسطة
       content: على ::before/::after في quill.snow.css — هذه الطريقة
       الوحيدة لتغييرها دون تعديل حزمة Quill نفسها أو استضافة نسخة معدَّلة).

    ملاحظة: لا يمسّ هذا الملف تنسيق المحرر (الحدود/القوائم/الوصولية) ولا
    منطق حفظ notes/terms في الخادم — فقط سلوك أداة الرابط داخل Quill.

    إضافة لاحقة (لا تزال ضمن نطاق "إعداد Quill مشترك بين الصفحتين"،
    فأُبقيت في نفس الملف بدل إنشاء جزئي منفصل): تسجيل Quill على استخدام
    attributors/style بدل attributors/class لأدوات المحاذاة/اللون/التظليل
    — راجع التعليق في @push('scripts') أدناه، ويقابله تحديث في
    config/purifier.php (بروفايل invoice_notes) للسماح بخاصيات CSS هذه.
--}}
@push('styles')
    <style>
        /* تعريب واجهة نافذة الرابط المنبثقة في Quill (.ql-tooltip). القيم
           الإنجليزية الأصلية موجودة في quill.snow.css كـcontent على
           ::before/::after، فلا يمكن تغييرها إلا بنفس الطريقة هنا. */
        .rich-editor-wrap .ql-tooltip::before { content: "فتح الرابط:" !important; }
        .rich-editor-wrap .ql-tooltip[data-mode="link"]::before { content: "أدخل الرابط:" !important; }
        .rich-editor-wrap .ql-tooltip a.ql-action::after { content: "تعديل" !important; }
        .rich-editor-wrap .ql-tooltip.ql-editing a.ql-action::after { content: "حفظ" !important; }
        .rich-editor-wrap .ql-tooltip a.ql-remove::before { content: "إزالة" !important; }
    </style>
@endpush

@push('scripts')
    <script>
        // تسجيل Quill على استخدام style="..." (بدل class="ql-align-center"
        // الافتراضية) لأدوات المحاذاة/لون النص/لون التظليل في الشريط.
        // السبب: مُعقِّم الخادم (config/purifier.php، بروفايل invoice_notes)
        // يسمح بخاصية CSS محدودة (text-align/color/background-color) عبر
        // style inline، ولا يسمح بأي قيمة class عشوائية — فلو تُرك Quill
        // على الوضع الافتراضي (class-based) لَحُذِف كل تنسيق محاذاة/لون
        // بصمت عند الحفظ رغم ظهوره في المحرر. Quill 1.3.7 يوفّر هذا التبديل
        // كخيار رسمي جاهز (attributors/style/*) دون الحاجة لأي كود تحويل.
        if (!window.__invoiceQuillStyleAttributorsRegistered) {
            window.__invoiceQuillStyleAttributorsRegistered = true;
            ['align', 'color', 'background'].forEach((name) => {
                Quill.register(Quill.import(`attributors/style/${name}`), true);
            });
        }

        // حارس ضد التكرار: يسمح بتضمين هذا الجزئي أكثر من مرة بأمان لو لزم.
        if (!window.setupInvoiceLinkTooltip) {
            /**
             * يطبّع نص رابط أدخله المستخدم إلى href صالح، أو يعيد null إن تعذّر.
             * - يزيل المسافات الزائدة من البداية والنهاية.
             * - يقبل mailto:/tel: كما هي (بعد التأكد من عدم الفراغ).
             * - يحوّل بريداً إلكترونياً صريحاً بلا بروتوكول إلى mailto:.
             * - يترك الروابط التي تحوي بروتوكولاً صالحاً (http://, https://, ...) كما هي.
             * - يضيف https:// تلقائياً لنطاق بلا بروتوكول (مثل example.com).
             * - أي نص آخر (جملة عادية أو نص لا يشبه رابطاً) يُعتبر غير صالح.
             */
            window.normalizeInvoiceLinkUrl = function (raw) {
                const value = (raw || '').trim();
                if (!value) return null;
                if (/^(mailto:|tel:)/i.test(value)) return value;
                if (/^\S+@\S+\.\S+$/.test(value)) return `mailto:${value}`;
                if (/^[a-z][a-z0-9+.-]*:\/\//i.test(value)) return value;
                if (/^[a-z0-9.-]+\.[a-z]{2,}([/?#][^\s]*)?$/i.test(value)) return `https://${value}`;
                return null;
            };

            /**
             * يستبدل سلوك أداة إدراج/تحرير الروابط الافتراضي في نسخة Quill
             * المُمرَّرة. يُستدعى مرة واحدة لكل محرر مباشرة بعد إنشائه
             * (بعد new Quill(...) وقبل أي استخدام آخر).
             */
            window.setupInvoiceLinkTooltip = function (quill) {
                const toolbarModule = quill.getModule('toolbar');
                const tooltip = quill.theme.tooltip;
                const normalize = window.normalizeInvoiceLinkUrl;

                // معالج زر الرابط: لا نملأ حقل الرابط بالنص المحدَّد إلا إذا
                // كان هذا النص رابطاً صالحاً بذاته — غير ذلك نترك الحقل
                // فارغاً، فيبقى النص المحدَّد نصاً ظاهراً للرابط فقط دون أن
                // يُدرَج داخل href لاحقاً.
                toolbarModule.addHandler('link', function (value) {
                    if (!value) {
                        this.quill.format('link', false);
                        return;
                    }
                    const range = this.quill.getSelection();
                    if (range == null || range.length === 0) return;
                    const selectedText = this.quill.getText(range);
                    const asUrl = normalize(selectedText);
                    tooltip.edit('link', asUrl || '');
                });

                // حفظ الرابط (زر "حفظ" أو مفتاح Enter): تحقّق وتطبيع بدل
                // الحفظ الحرفي لقيمة الحقل. رابط فارغ/غير صالح = لا إدراج.
                tooltip.save = function () {
                    const url = normalize(this.textbox.value);
                    this.textbox.value = '';
                    if (!url) {
                        this.hide();
                        return;
                    }
                    const { scrollTop } = this.quill.root;
                    if (this.linkRange) {
                        this.quill.formatText(this.linkRange, 'link', url, Quill.sources.USER);
                        delete this.linkRange;
                    } else {
                        this.restoreFocus();
                        this.quill.format('link', url, Quill.sources.USER);
                    }
                    this.quill.root.scrollTop = scrollTop;
                    this.hide();
                };
            };
        }
    </script>
@endpush

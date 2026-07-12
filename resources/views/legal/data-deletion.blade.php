{{--
    سياسة حذف البيانات — دراهم | مال وأعمال
    المرجع القانوني الوحيد لمحتوى هذه الصفحة: docs/legal/Data-Deletion.md
    هذا الملف لا يُعدِّل أي نص قانوني — فقط يحوّل تنسيق Markdown المعتمد إلى HTML دلالي
    مطابق حرفياً للوثيقة (باستثناء تحويل الروابط الداخلية لوثائق قانونية أخرى غير
    منشورة بعد إلى نص عادي بدل روابط فعلية، حفاظاً على قاعدة "لا تضف روابط لصفحات
    قانونية غير منشورة بعد").

    التنسيق البصري بالكامل عبر Tailwind utility classes + مكوّنات .legal-page-*
    المُعرَّفة في darahem-front/assets/css/app.css (مصدر Tailwind الحقيقي للموقع
    التسويقي)، بلا أي وسم Style أو خاصية style Inline محلية داخل هذا الملف.
--}}
@extends('layouts.marketing')

@section('title', 'سياسة حذف البيانات — دراهم | مال وأعمال')

@section('meta')
<meta name="description" content="كيف يمكنك طلب حذف بياناتك من منصة دراهم | مال وأعمال، وما الذي يُحذف فعلياً وما الذي قد يُحتفَظ به لأسباب قانونية ومحاسبية.">
<meta name="robots" content="index, follow">
<link rel="canonical" href="https://darahum.com/legal/data-deletion">

<meta property="og:type" content="website">
<meta property="og:title" content="سياسة حذف البيانات — دراهم | مال وأعمال">
<meta property="og:description" content="الآلية الفعلية الحالية لطلب حذف بياناتك من دراهم | مال وأعمال.">
<meta property="og:url" content="https://darahum.com/legal/data-deletion">
<meta property="og:locale" content="ar_AR">
@endsection

@section('body-class', 'bg-white text-g-body antialiased overflow-x-hidden font-sans')

@section('content')
@php
    $tocItems = [
        ['id' => 'request-deletion',           'label' => 'كيف تطلب حذف بياناتك'],
        ['id' => 'deletion-process',           'label' => 'ما الذي يحدث عند تنفيذ طلب الحذف'],
        ['id' => 'retention-after-deletion',   'label' => 'البيانات التي قد نحتفظ بها بعد الحذف'],
        ['id' => 'inactive-unverified',        'label' => 'حذف الحسابات غير النشطة وغير الموثقة'],
        ['id' => 'temp-files',                 'label' => 'حذف الملفات المؤقتة'],
        ['id' => 'policy-updates',             'label' => 'تحديثات هذه السياسة'],
        ['id' => 'contact',                    'label' => 'التواصل'],
    ];
@endphp

<main>
    <!-- Hero — نفس تصميم بنر صفحات /faq وسياسة الخصوصية وشروط الاستخدام -->
    <section class="relative overflow-hidden bg-[linear-gradient(67deg,#310e8e_0%,#13c597_95%)]">
        <div class="absolute -top-44 end-[-230px] size-[500px] rounded-full bg-g-green opacity-15 blur-[40px]"></div>
        <div class="absolute -bottom-32 start-[-140px] size-[400px] rounded-full bg-g-purple opacity-15 blur-[40px]"></div>

        <div class="relative mx-auto flex min-h-[520px] max-w-[1142px] flex-col items-center justify-center px-6 py-24 text-center sm:min-h-[595px]">
            <h1 class="max-w-[1104px] bg-gradient-to-r from-white to-g-mint-bright bg-clip-text text-[40px] font-bold leading-[1.15] text-transparent sm:text-[56px] lg:text-[63px] lg:leading-[72px]">
                سياسة حذف البيانات
            </h1>
            <p class="mt-6 text-base leading-8 text-white sm:text-lg">
                توضح هذه الصفحة كيفية طلب حذف بياناتك، وما الذي يُحذف، وما الذي قد نحتفظ به مؤقتاً وفق السياسة المعتمدة.
            </p>
            <p class="mt-4 text-sm text-white/70">آخر تحديث: 12 يوليو 2026</p>
        </div>
    </section>

    <!-- محتوى السياسة + جدول المحتويات -->
    <section class="px-5 py-16">
        <div class="max-w-6xl mx-auto legal-page-layout lg:grid-cols-[256px_minmax(0,1fr)] lg:gap-12">

            <!-- جدول المحتويات — Mobile (قابل للفتح) -->
            <details class="lg:hidden rounded-2xl border border-g-border bg-g-light px-4 py-3">
                <summary class="text-sm font-bold text-g-dark cursor-pointer select-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-g-green rounded">محتويات الصفحة</summary>
                <nav aria-label="محتويات صفحة سياسة حذف البيانات" class="mt-2">
                    <ul class="flex flex-col gap-1">
                        @foreach ($tocItems as $item)
                            <li>
                                <a href="#{{ $item['id'] }}" class="legal-page-toc-link">{{ $item['label'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            </details>

            <!-- جدول المحتويات — Desktop (شريط جانبي Sticky) -->
            <aside class="hidden lg:block lg:sticky lg:top-28 lg:self-start">
                <nav aria-label="محتويات صفحة سياسة حذف البيانات" class="rounded-xl bg-g-light p-2">
                    <ul class="flex flex-col gap-0.5">
                        @foreach ($tocItems as $item)
                            <li>
                                <a href="#{{ $item['id'] }}" class="legal-page-toc-link">{{ $item['label'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            </aside>

            <!-- محتوى الوثيقة -->
            <article class="legal-page-content">

                <p>تصف هذه الوثيقة الآلية <strong>الفعلية المُطبَّقة حالياً</strong> لحذف بيانات حسابك من منصة <strong>دراهم | مال وأعمال</strong> ("<strong>دراهم</strong>" أو "<strong>المنصة</strong>")، المملوكة والمُشغَّلة من قِبل <strong>شركة بال قوول لتكنولوجيا المعلومات والدعاية والإعلان</strong> ("<strong>الشركة</strong>" أو "<strong>نحن</strong>"). نلتزم بالشفافية الكاملة هنا: العملية حالياً <strong>يدوية جزئياً</strong>، وليست حذفاً ذاتياً فورياً وكاملاً بضغطة زر. سنُحدّث هذه الوثيقة فور تطوير أي أداة حذف ذاتي تلقائية للحساب.</p>

                <h2 id="request-deletion">1. كيف تطلب حذف بياناتك</h2>
                <p>يمكنك تقديم طلب حذف بيانات في أي وقت بالتواصل معنا مباشرة عبر البريد الإلكتروني:</p>
                <p><strong>info@darahum.com</strong></p>
                <p>يُرجى إرسال الطلب من عنوان البريد الإلكتروني المسجَّل في حسابك، لتمكيننا من التحقق من هويتك قبل تنفيذ أي إجراء حذف. سيقوم فريق الدعم بمراجعة طلبك ومعالجته خلال مدة معقولة لا تتجاوز عادة 30 يوماً من تاريخ استلامه، بما يتماشى مع الممارسات المعتمَدة دولياً في حماية البيانات.</p>
                <p>بالإضافة إلى ذلك، وقبل تنفيذ أي طلب حذف نهائي وغير قابل للتراجع (وعلى الأخص طلبات إغلاق الحساب بالكامل)، سيرسل فريق الدعم <strong>رسالة تأكيد ثانية</strong> إلى بريدك الإلكتروني المسجَّل، تتضمن رابطاً أو رمز تأكيد صريح، ولن يُنفَّذ الحذف إلا بعد تأكيدك الصريح عبر الرد على هذه الرسالة. هذه خطوة تحقق إضافية لحمايتك من تنفيذ طلب حذف لم تُقدّمه أنت فعلياً.</p>

                <h2 id="deletion-process">2. ما الذي يحدث فعلياً عند تنفيذ طلب حذف</h2>
                <p>عند معالجة طلب حذف بياناتك، تشمل العملية الحالية إزالة البيانات التالية المرتبطة بحسابك:</p>
                <ul>
                    <li>المعاملات المالية (الدخل والمصروفات).</li>
                    <li>الديون والميزانيات.</li>
                    <li>المعاملات المتكررة (Recurring Transactions).</li>
                    <li>سجل الاشتراكات والإشعارات الداخلية.</li>
                    <li>التصنيفات (Categories) والمشاريع.</li>
                </ul>
                <p><strong>بعد الحذف، تُعاد خطة حسابك إلى الخطة المجانية.</strong></p>

                <h3 id="deletion-process-excluded">2.1 ما لا تشمله عملية الحذف الحالية</h3>
                <p>بصراحة تامة: الآلية الإدارية الحالية المستخدَمة لتنفيذ طلبات الحذف <strong>لا تشمل حالياً</strong> حذف سجلات العملاء (Clients) أو الفواتير (Invoices) أو عروض الأسعار (Quotes) المرتبطة بحسابك بشكل تلقائي، ولا تحذف الحساب نفسه (بريدك الإلكتروني ورقم هاتفك المسجَّلين). إذا كنت ترغب في حذف هذه السجلات أو إغلاق حسابك بالكامل، يُرجى ذكر ذلك صراحة في طلبك، وسيقوم فريق الدعم بمراجعته وتنفيذه يدوياً بالتنسيق معك، مع مراعاة القيود الموضحة في القسم التالي.</p>

                <h3 id="deletion-process-soft-delete">2.2 الحذف الناعم (Soft Delete)</h3>
                <p>عندما تحذف بنفسك سجلاً معيَّناً من داخل المنصة (فاتورة، عميل، معاملة، عرض سعر)، فإن هذا السجل <strong>يُخفى فوراً من واجهتك ولا يظهر لك أو لأي شخص آخر بعد الآن</strong>، لكنه يبقى محفوظاً داخلياً بشكل مؤقت (وليس محذوفاً نهائياً في تلك اللحظة) لأغراض التدقيق المحاسبي واستعادة البيانات في حال الحذف بالخطأ، قبل أن يخضع لدورات تنظيف داخلية لاحقة.</p>

                <h2 id="retention-after-deletion">3. البيانات التي قد نحتفظ بها بعد الحذف</h2>
                <p>حتى بعد معالجة طلب حذف أو إغلاق حسابك، تحتفظ الشركة بالسجلات المالية (مثل الفواتير الصادرة، سجلات المعاملات، سجلات تحصيل المدفوعات) والبيانات المرتبطة بها لمدة تصل إلى <strong>سنة واحدة</strong> بعد إغلاق الحساب، وفق سياسة تشغيل داخلية اعتمدتها الشركة لهذا الغرض، <strong>ما لم يفرض القانون أو أمر قضائي أو التزام محاسبي نافذ الاحتفاظ بها لمدة أطول</strong>، وفي هذه الحالة نحتفظ بها للمدة الإضافية اللازمة للامتثال فقط. هذا الاحتفاظ يقتصر على البيانات الضرورية لهذا الغرض، ولا يشمل استمرار استخدامنا لهذه البيانات لأي غرض تسويقي أو تشغيلي آخر بعد إغلاق حسابك.</p>
                <p>بالنسبة لبيانات عملائك (CRM) تحديداً: إذا لم تكن بيانات عميل معيَّن مرتبطة بأي فاتورة أو معاملة مالية فعلية، فإنها تُحذف عند معالجة طلب حذف حسابك. أما إذا كانت مرتبطة بفاتورة أو معاملة مالية، فتخضع لمدة الاحتفاظ ذاتها الموضَّحة أعلاه، ثم نعمل على حذفها أو إزالة ما يُعرّف بهويتها متى أمكن ذلك وتوافق مع أي متطلبات قانونية سارية.</p>

                <h2 id="inactive-unverified">4. حذف الحسابات غير النشطة وغير الموثَّقة</h2>
                <p>نحتفظ بحق حذف الحسابات التي أُنشئت ببريد إلكتروني لم يُتحقَّق منه أبداً، والتي لا يظهر عليها أي نشاط فعلي (لا مشاريع، لا معاملات، لا عملاء)، باعتبارها حسابات غير جادة. هذا الحذف يكون نهائياً وشاملاً لسجل الحساب نفسه، ولا ينطبق على أي حساب استُخدم فعلياً أو تم التحقق من بريده الإلكتروني.</p>

                <h2 id="temp-files">5. حذف الملفات المؤقتة</h2>
                <p>عند استيرادك لقائمة عملاء عبر ملف (CSV/Excel)، يُخزَّن هذا الملف مؤقتاً فقط أثناء معالجته، ثم يُحذف تلقائياً من خوادمنا فور انتهاء عملية الاستيراد.</p>

                <h2 id="policy-updates">6. تحديثات هذه السياسة</h2>
                <p>نعمل على تطوير آلية أكثر مرونة وسرعة لحذف الحسابات والبيانات ذاتياً دون الحاجة للتواصل اليدوي مع الدعم. فور توفر هذه الآلية، سنُحدّث هذه الوثيقة لتعكسها بدقة.</p>

                <h2 id="contact">7. التواصل</h2>
                <p>لتقديم طلب حذف بيانات أو للاستفسار عن حالة طلب سابق:</p>
                <p><strong>info@darahum.com</strong></p>

                <hr>

                <p class="text-xs text-g-muted leading-relaxed">هذه الوثيقة جزء من الحزمة القانونية لمنصة دراهم | مال وأعمال، وتُقرأ بالتزامن مع: <a href="{{ route('legal.privacy') }}">سياسة الخصوصية</a>، <a href="{{ route('legal.terms') }}">شروط الاستخدام</a>، و<a href="{{ route('legal.cookies') }}">سياسة الكوكيز</a>.</p>

                <!-- صندوق التواصل -->
                <div class="legal-page-contact">
                    <p class="text-sm text-g-body mb-2">لتقديم طلب حذف بيانات أو للاستفسار عن حالة طلب سابق:</p>
                    <p class="font-bold text-g-dark">شركة بال قوول لتكنولوجيا المعلومات والدعاية والإعلان</p>
                    <p class="mt-2">
                        <a href="mailto:info@darahum.com"
                           class="font-semibold text-g-green hover:text-g-purple-mid focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-g-green rounded-sm"
                           dir="ltr">info@darahum.com</a>
                    </p>
                </div>

            </article>
        </div>
    </section>
</main>
@endsection

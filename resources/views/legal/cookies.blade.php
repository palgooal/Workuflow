{{--
    سياسة الكوكيز — دراهم | مال وأعمال
    المرجع القانوني الوحيد لمحتوى هذه الصفحة: docs/legal/Cookie-Policy.md
    هذا الملف لا يُعدِّل أي نص قانوني — فقط يحوّل تنسيق Markdown المعتمد (بما فيه
    الجداول) إلى HTML دلالي مطابق حرفياً للوثيقة (باستثناء تحويل الروابط الداخلية
    لوثائق قانونية أخرى غير منشورة بعد إلى نص عادي بدل روابط فعلية).

    التنسيق البصري بالكامل عبر Tailwind utility classes + مكوّنات .legal-page-*
    المُعرَّفة في darahem-front/assets/css/app.css (مصدر Tailwind الحقيقي للموقع
    التسويقي)، بلا أي وسم Style أو خاصية style Inline محلية داخل هذا الملف.
--}}
@extends('layouts.marketing')

@section('title', 'سياسة الكوكيز — دراهم | مال وأعمال')

@section('meta')
<meta name="description" content="قائمة دقيقة بكل ملفات تعريف الارتباط (Cookies) المستخدَمة فعلياً على منصة دراهم | مال وأعمال، دون أي تكبير أو ميزات غير موجودة: كوكي الجلسة وكوكيز الإحالة فقط.">
<meta name="robots" content="index, follow">
<link rel="canonical" href="https://darahum.com/legal/cookies">

<meta property="og:type" content="website">
<meta property="og:title" content="سياسة الكوكيز — دراهم | مال وأعمال">
<meta property="og:description" content="الكوكيز الفعلية المستخدَمة على دراهم | مال وأعمال، وكيفية التحكم بها.">
<meta property="og:url" content="https://darahum.com/legal/cookies">
<meta property="og:locale" content="ar_AR">
@endsection

@section('body-class', 'bg-white text-g-body antialiased overflow-x-hidden font-sans')

@section('content')
@php
    $tocItems = [
        ['id' => 'what-are-cookies',   'label' => 'ما هي الكوكيز'],
        ['id' => 'cookies-we-use',     'label' => 'الكوكيز التي نستخدمها فعلياً'],
        ['id' => 'what-we-dont-use',   'label' => 'ما لا نستخدمه'],
        ['id' => 'cookie-control',     'label' => 'كيف تتحكم بالكوكيز'],
        ['id' => 'policy-updates',     'label' => 'تحديثات هذه السياسة'],
        ['id' => 'contact',           'label' => 'التواصل'],
    ];
@endphp

<main>
    <!-- Hero — نفس تصميم بنر صفحات /faq وسياسة الخصوصية وشروط الاستخدام وسياسة حذف البيانات -->
    <section class="relative overflow-hidden bg-[linear-gradient(67deg,#310e8e_0%,#13c597_95%)]">
        <div class="absolute -top-44 end-[-230px] size-[500px] rounded-full bg-g-green opacity-15 blur-[40px]"></div>
        <div class="absolute -bottom-32 start-[-140px] size-[400px] rounded-full bg-g-purple opacity-15 blur-[40px]"></div>

        <div class="relative mx-auto flex min-h-[520px] max-w-[1142px] flex-col items-center justify-center px-6 py-24 text-center sm:min-h-[595px]">
            <h1 class="max-w-[1104px] bg-gradient-to-r from-white to-g-mint-bright bg-clip-text text-[40px] font-bold leading-[1.15] text-transparent sm:text-[56px] lg:text-[63px] lg:leading-[72px]">
                سياسة ملفات تعريف الارتباط
            </h1>
            <p class="mt-6 text-base leading-8 text-white sm:text-lg">
                توضح هذه الصفحة كيفية استخدام ملفات تعريف الارتباط وتقنيات التخزين المشابهة داخل منصة دراهم.
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
                <nav aria-label="محتويات صفحة سياسة الكوكيز" class="mt-2">
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
                <nav aria-label="محتويات صفحة سياسة الكوكيز" class="rounded-xl bg-g-light p-2">
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

                <p>تصف هذه الوثيقة <strong>فقط</strong> ملفات تعريف الارتباط (Cookies) المستخدَمة فعلياً على منصة <strong>دراهم | مال وأعمال</strong>، المملوكة والمُشغَّلة من قِبل <strong>شركة بال قوول لتكنولوجيا المعلومات والدعاية والإعلان</strong>. لا نستخدم أي كوكيز غير المذكورة هنا.</p>

                <h2 id="what-are-cookies">1. ما هي الكوكيز</h2>
                <p>الكوكيز ملفات نصية صغيرة يخزّنها متصفحك عند زيارتك لموقع إلكتروني، تُستخدَم لتذكّر حالتك أو تفضيلاتك بين الزيارات.</p>

                <h2 id="cookies-we-use">2. الكوكيز التي نستخدمها فعلياً</h2>

                <h3 id="session-cookie">2.1 كوكي الجلسة (Session Cookie) — ضروري</h3>
                <div class="overflow-x-auto">
                    <table>
                        <thead>
                            <tr>
                                <th>الخاصية</th>
                                <th>القيمة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>الغرض</strong></td>
                                <td>تسجيل دخولك والحفاظ على حالة جلستك أثناء استخدامك للمنصة (بدونه لا يمكنك تسجيل الدخول أو استخدام لوحة التحكم).</td>
                            </tr>
                            <tr>
                                <td><strong>النوع</strong></td>
                                <td>كوكي ضروري بشكل صارم لتشغيل الخدمة (Strictly Necessary).</td>
                            </tr>
                            <tr>
                                <td><strong>الخصائص</strong></td>
                                <td><code>HttpOnly</code> (غير قابل للقراءة عبر جافاسكريبت)، <code>SameSite=Lax</code>.</td>
                            </tr>
                            <tr>
                                <td><strong>مدة الصلاحية</strong></td>
                                <td>مرتبطة بمدة الجلسة النشطة (تنتهي تلقائياً بعد فترة من عدم النشاط، أو عند تسجيل الخروج).</td>
                            </tr>
                            <tr>
                                <td><strong>يتطلب موافقة مسبقة؟</strong></td>
                                <td>لا — نظراً لكونه ضرورياً بشكل صارم لتقديم الخدمة التي طلبتها بنفسك (تسجيل الدخول).</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h3 id="referral-cookies">2.2 كوكيز الإحالة (Referral Attribution Cookies) — وظيفية</h3>
                <p>إذا وصلت إلى دراهم عبر رابط إحالة (Referral Link) من أحد المسوّقين المشاركين في برنامج الإحالة لدينا، فإننا نضبط ثلاث كوكيز مرتبطة ببعضها لغرض واحد فقط: <strong>إسناد تسجيلك للمسوّق الصحيح واكتشاف محاولات إساءة استخدام برنامج الإحالة (مثل الحسابات المكرَّرة أو النقرات الوهمية)</strong>.</p>
                <div class="overflow-x-auto">
                    <table>
                        <thead>
                            <tr>
                                <th>الخاصية</th>
                                <th>القيمة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>الغرض</strong></td>
                                <td>تتبّع مصدر الزيارة (رابط الإحالة) لأغراض احتساب عمولة المسوّق، ومنع الاحتيال على برنامج الإحالة.</td>
                            </tr>
                            <tr>
                                <td><strong>متى تُضبَط</strong></td>
                                <td>فقط عند وصولك للموقع عبر رابط إحالة يحمل كوداً تسويقياً (مثل <code>darahum.com/ref/{code}</code>). لا تُضبَط لأي زائر عادي يدخل الموقع مباشرة.</td>
                            </tr>
                            <tr>
                                <td><strong>الخصائص</strong></td>
                                <td><code>Secure</code>، <code>HttpOnly</code>، <code>SameSite=Lax</code>.</td>
                            </tr>
                            <tr>
                                <td><strong>مدة الصلاحية</strong></td>
                                <td>60 يوماً من تاريخ الزيارة.</td>
                            </tr>
                            <tr>
                                <td><strong>يتطلب موافقة مسبقة؟</strong></td>
                                <td>تُستخدَم لغرض وظيفي/تشغيلي (احتساب عمولات ومنع احتيال) وليس لأغراض إعلانية موجَّهة من طرف ثالث. إن كنت مقيماً في نطاق قضائي يشترط موافقة صريحة على أي كوكي غير ضروري بشكل صارم قبل ضبطه، يمكنك حذف هذه الكوكيز أو منعها عبر إعدادات متصفحك كما هو موضَّح أدناه.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h2 id="what-we-dont-use">3. ما لا نستخدمه</h2>
                <p>للتوضيح الصريح وعدم ترك أي لبس: <strong>لا تستخدم منصة دراهم حالياً أياً مما يلي</strong>:</p>
                <ul>
                    <li><strong>لا كوكيز تحليلات (Analytics)</strong> من أي نوع (مثل Google Analytics).</li>
                    <li><strong>لا Facebook Pixel</strong> أو أي أداة تتبّع إعلاني تابعة لـ Meta.</li>
                    <li><strong>لا كوكيز إعلانية من أي شبكة طرف ثالث.</strong></li>
                    <li><strong>لا أدوات تتبّع سلوك المستخدم (Heatmaps) مثل Hotjar أو ما شابهها.</strong></li>
                </ul>

                <h2 id="cookie-control">4. كيف تتحكم بالكوكيز</h2>
                <p>يمكنك في أي وقت حذف الكوكيز المخزَّنة أو منع ضبطها مستقبلاً من خلال إعدادات متصفحك. يُرجى ملاحظة أن حذف أو منع كوكي الجلسة سيمنعك من تسجيل الدخول واستخدام المنصة، بينما حذف كوكيز الإحالة لن يؤثر على قدرتك على استخدام المنصة، لكنه قد يُفقِد المسوّق الذي أحالك إسناد عمولته إن كنت قد سجّلت عبر رابطه.</p>

                <h2 id="policy-updates">5. تحديثات هذه السياسة</h2>
                <p>سنُحدّث هذه الوثيقة فور إضافة أي كوكي جديد للمنصة (مثل أدوات تحليلات أو تتبّع مستقبلية)، وسنوضّح حينها الغرض منه ونطلب موافقتك المسبقة حيثما يقتضي الأمر ذلك.</p>

                <h2 id="contact">6. التواصل</h2>
                <p>لأي استفسار حول هذه السياسة، يُرجى التواصل معنا عبر:</p>
                <p><strong>info@darahum.com</strong></p>

                <hr>

                <p class="text-xs text-g-muted leading-relaxed">هذه الوثيقة جزء من الحزمة القانونية لمنصة دراهم | مال وأعمال، وتُقرأ بالتزامن مع: <a href="{{ route('legal.privacy') }}">سياسة الخصوصية</a>، <a href="{{ route('legal.terms') }}">شروط الاستخدام</a>، و<a href="{{ route('legal.data-deletion') }}">سياسة حذف البيانات</a>.</p>

                <!-- صندوق التواصل -->
                <div class="legal-page-contact">
                    <p class="text-sm text-g-body mb-2">لأي استفسار حول هذه السياسة:</p>
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

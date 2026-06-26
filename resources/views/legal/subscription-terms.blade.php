<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>شروط الاشتراك — دراهم</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Readex+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased" style="font-family: 'Readex Pro', sans-serif;">

<div class="max-w-3xl mx-auto px-4 py-12">

    <div class="mb-8">
        <a href="{{ route('home') }}" class="text-indigo-600 hover:underline text-sm">← العودة للرئيسية</a>
    </div>

    <h1 class="text-3xl font-bold mb-2">شروط الاشتراك</h1>
    <p class="text-gray-500 text-sm mb-8">آخر تحديث: يونيو 2026</p>

    <div class="prose prose-gray max-w-none space-y-6 text-gray-700 leading-relaxed">

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">1. خطط الاشتراك</h2>
            <p>تقدم دراهم ثلاثة مستويات:</p>
            <ul class="list-disc list-inside space-y-1">
                <li><strong>مجاني (Free):</strong> ميزات محدودة — مشروع واحد، 5 عملاء، 10 فواتير</li>
                <li><strong>Pro ⚡:</strong> ميزات موسَّعة — مشاريع وعملاء وفواتير غير محدودة، تقارير متقدمة</li>
                <li><strong>Business 🚀:</strong> الميزات الكاملة — كل ميزات Pro + التحليلات المتقدمة والتصدير</li>
            </ul>
            <p>الأسعار الحالية معروضة دائماً في <a href="{{ route('marketing.pricing') }}" class="text-indigo-600 hover:underline">صفحة الأسعار</a>.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">2. دورات الفوترة</h2>
            <p><strong>الاشتراك الشهري:</strong> يُفوتَر شهرياً. ينتهي في نهاية كل شهر تقويمي من تاريخ الاشتراك. يُجدَّد بدفعة جديدة.</p>
            <p><strong>الاشتراك السنوي:</strong> يُفوتَر مرة واحدة بالكامل لـ 12 شهراً مقدماً بسعر مخفَّض. ينتهي بعد 365 يوماً من تاريخ التفعيل.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">3. التفعيل التلقائي</h2>
            <p>عند إتمام الدفع عبر بوابة Togo، يُفعَّل اشتراكك <strong>فوراً وتلقائياً</strong> دون انتظار أو موافقة يدوية. ستصل رسالة تأكيد على بريدك الإلكتروني.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">4. التجديد</h2>
            <p>اشتراك دراهم <strong>لا يُجدَّد تلقائياً</strong> في النسخة الحالية. عند انتهاء اشتراكك:</p>
            <ul class="list-disc list-inside space-y-1">
                <li>ستصلك رسالة تذكير قبل <strong>7 أيام</strong> من انتهاء الاشتراك</li>
                <li>يوم الانتهاء: يُخفَّض حسابك تلقائياً للخطة المجانية</li>
                <li>بياناتك تبقى محفوظة — لا شيء يُحذف</li>
                <li>لتجديد الاشتراك: اذهب لـ إعدادات → الاشتراك واختر خطتك</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">5. التغيير بين الخطط</h2>
            <ul class="list-disc list-inside space-y-1">
                <li><strong>الترقية:</strong> تُفعَّل فوراً مع بداية دورة فوترة جديدة</li>
                <li><strong>التخفيض:</strong> تُطبَّق في نهاية الدورة الحالية</li>
                <li>لا رسوم إضافية على الترقية في منتصف الدورة</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">6. الدفع</h2>
            <ul class="list-disc list-inside space-y-1">
                <li>تُعالَج المدفوعات عبر بوابة <strong>Togo (togo.ps)</strong></li>
                <li>المدفوعات آمنة ومشفَّرة — نحن لا نخزّن بيانات البطاقة</li>
                <li>فشل الدفع: يُبلَّغ المستخدم فوراً عبر البريد ويمكن إعادة المحاولة</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">7. انتهاء الاشتراك</h2>
            <p>عند انتهاء الاشتراك، تستمر الميزات حتى نهاية الفترة المدفوعة. بعدها يُطبَّق حد الخطة المجانية فقط. لا تُحذف أي بيانات.</p>
        </section>

    </div>

    <div class="mt-12 pt-6 border-t border-gray-200 flex flex-wrap gap-4 text-sm text-gray-500">
        <a href="{{ route('legal.privacy') }}" class="hover:text-indigo-600">سياسة الخصوصية</a>
        <a href="{{ route('legal.terms') }}" class="hover:text-indigo-600">الشروط والأحكام</a>
        <a href="{{ route('legal.refund') }}" class="hover:text-indigo-600">سياسة الاسترداد</a>
        <a href="{{ route('legal.cancellation') }}" class="hover:text-indigo-600">سياسة الإلغاء</a>
    </div>
</div>
</body>
</html>

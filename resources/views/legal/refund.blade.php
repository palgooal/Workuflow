<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>سياسة الاسترداد — دراهم</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Readex+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased" style="font-family: 'Readex Pro', sans-serif;">

<div class="max-w-3xl mx-auto px-4 py-12">

    <div class="mb-8">
        <a href="{{ route('home') }}" class="text-indigo-600 hover:underline text-sm">← العودة للرئيسية</a>
    </div>

    <h1 class="text-3xl font-bold mb-2">سياسة الاسترداد</h1>
    <p class="text-gray-500 text-sm mb-8">آخر تحديث: يونيو 2026</p>

    <div class="prose prose-gray max-w-none space-y-6 text-gray-700 leading-relaxed">

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">1. الاسترداد — النموذج الشهري</h2>
            <p>للاشتراكات الشهرية، يمكنك طلب استرداد كامل خلال <strong>48 ساعة</strong> من تاريخ الدفع إذا لم تستخدم المنصة فعلياً. بعد هذه المدة، لا يحق استرداد رسوم الشهر الجاري.</p>
            <ul class="list-disc list-inside space-y-1 mt-2">
                <li>✅ طلب الاسترداد خلال 48 ساعة من الدفع وبدون استخدام</li>
                <li>✅ خلل تقني يحول دون استخدام المنصة</li>
                <li>❌ الاسترداد بعد 48 ساعة أو بعد الاستخدام الفعلي</li>
                <li>❌ تغيير الرأي بعد استخدام الخدمة</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">2. الاسترداد — النموذج السنوي</h2>
            <p>الاشتراكات السنوية مدفوعة مقدماً بالكامل. يحق الاسترداد وفق الآتي:</p>
            <ul class="list-disc list-inside space-y-1">
                <li>خلال <strong>7 أيام</strong> من الاشتراك: استرداد كامل</li>
                <li>من اليوم 8 إلى نهاية الشهر الأول: استرداد 75%</li>
                <li>بعد الشهر الأول: لا استرداد على الأشهر المتبقية</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">3. الاسترداد يدوي</h2>
            <p>الاسترداد في دراهم <strong>يدوي</strong> ولا يتم تلقائياً. لطلب الاسترداد:</p>
            <ol class="list-decimal list-inside space-y-1">
                <li>أرسل بريداً إلى <strong>support@darahum.com</strong></li>
                <li>أذكر: اسمك، بريدك الإلكتروني، تاريخ الدفع، سبب الطلب</li>
                <li>سنراجع الطلب خلال 3 أيام عمل</li>
                <li>يُعاد المبلغ لنفس وسيلة الدفع خلال 7-14 يوم عمل</li>
            </ol>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">4. حالات الاسترداد الاستثنائي</h2>
            <p>نتحمل مسؤولية الاسترداد الكامل في الحالات التالية:</p>
            <ul class="list-disc list-inside space-y-1">
                <li>تعطُّل المنصة لأكثر من 72 ساعة متواصلة</li>
                <li>تكرار الدفع بسبب خطأ تقني موثَّق</li>
                <li>فرض رسوم غير متفق عليها</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">5. الاحتفاظ بالبيانات بعد الاسترداد</h2>
            <p>بعد معالجة الاسترداد، يُخفَّض الاشتراك تلقائياً للخطة المجانية. بياناتك تبقى متاحة لمدة 30 يوماً، بعدها يمكنك تصديرها أو حذف الحساب.</p>
        </section>

    </div>

    <div class="mt-12 pt-6 border-t border-gray-200 flex flex-wrap gap-4 text-sm text-gray-500">
        <a href="{{ route('legal.privacy') }}" class="hover:text-indigo-600">سياسة الخصوصية</a>
        <a href="{{ route('legal.terms') }}" class="hover:text-indigo-600">الشروط والأحكام</a>
        <a href="{{ route('legal.subscription-terms') }}" class="hover:text-indigo-600">شروط الاشتراك</a>
        <a href="{{ route('legal.cancellation') }}" class="hover:text-indigo-600">سياسة الإلغاء</a>
    </div>
</div>
</body>
</html>

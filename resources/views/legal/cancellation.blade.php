<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>سياسة الإلغاء — دراهم</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Readex+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased" style="font-family: 'Readex Pro', sans-serif;">

<div class="max-w-3xl mx-auto px-4 py-12">

    <div class="mb-8">
        <a href="{{ route('home') }}" class="text-indigo-600 hover:underline text-sm">← العودة للرئيسية</a>
    </div>

    <h1 class="text-3xl font-bold mb-2">سياسة الإلغاء</h1>
    <p class="text-gray-500 text-sm mb-8">آخر تحديث: يونيو 2026</p>

    <div class="prose prose-gray max-w-none space-y-6 text-gray-700 leading-relaxed">

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">1. إلغاء الاشتراك</h2>
            <p>يمكنك إلغاء اشتراكك في أي وقت من داخل المنصة:</p>
            <ol class="list-decimal list-inside space-y-1">
                <li>اذهب إلى <strong>الإعدادات → الاشتراك</strong></li>
                <li>اضغط على "إلغاء الاشتراك"</li>
                <li>أكِّد الإلغاء</li>
            </ol>
            <p class="mt-2">بعد الإلغاء، يستمر اشتراكك حتى نهاية الفترة المدفوعة الحالية.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">2. ما يحدث بعد الإلغاء</h2>
            <ul class="list-disc list-inside space-y-1">
                <li>تبقى ميزات الخطة المدفوعة حتى نهاية الفترة</li>
                <li>لا يوجد تجديد تلقائي — لن تُفرض رسوم إضافية</li>
                <li>بعد انتهاء الفترة: يُخفَّض الحساب للخطة المجانية</li>
                <li><strong>لا تُحذف أي بيانات</strong> — مشاريعك وفواتيرك وعملاؤك يبقون</li>
                <li>بيانات الخطة المجانية متاحة بلا حد زمني ما لم تطلب حذف الحساب</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">3. إلغاء طلب دفع</h2>
            <p>إذا انقطع الاتصال أثناء عملية الدفع أو ضغطت "إلغاء" في صفحة Togo:</p>
            <ul class="list-disc list-inside space-y-1">
                <li>طلب الدفع يُعلَّم تلقائياً كـ "ملغى"</li>
                <li>لا تُفرض أي رسوم</li>
                <li>يمكنك إعادة المحاولة في أي وقت</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">4. حذف الحساب كاملاً</h2>
            <p>لحذف حسابك وكل بياناتك نهائياً، تواصل مع الدعم عبر <strong>support@darahum.com</strong>. سنُؤكد هويتك ثم ننفِّذ الطلب خلال 30 يوماً. البيانات المالية الخاضعة لمتطلبات قانونية تُحتفظ بها وفق سياسة الاحتفاظ في <a href="{{ route('legal.privacy') }}" class="text-indigo-600 hover:underline">سياسة الخصوصية</a>.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">5. تصدير بياناتك</h2>
            <p>قبل الإلغاء أو الحذف، يمكنك تصدير:</p>
            <ul class="list-disc list-inside space-y-1">
                <li>الفواتير: PDF من داخل كل فاتورة</li>
                <li>التقارير: تصدير Excel من صفحة التقارير</li>
                <li>البيانات الكاملة: أرسل طلباً لـ support@darahum.com</li>
            </ul>
        </section>

    </div>

    <div class="mt-12 pt-6 border-t border-gray-200 flex flex-wrap gap-4 text-sm text-gray-500">
        <a href="{{ route('legal.privacy') }}" class="hover:text-indigo-600">سياسة الخصوصية</a>
        <a href="{{ route('legal.terms') }}" class="hover:text-indigo-600">الشروط والأحكام</a>
        <a href="{{ route('legal.refund') }}" class="hover:text-indigo-600">سياسة الاسترداد</a>
        <a href="{{ route('legal.subscription-terms') }}" class="hover:text-indigo-600">شروط الاشتراك</a>
    </div>
</div>
</body>
</html>

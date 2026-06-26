<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>سياسة الخصوصية — دراهم</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Readex+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased" style="font-family: 'Readex Pro', sans-serif;">

<div class="max-w-3xl mx-auto px-4 py-12">

    <div class="mb-8">
        <a href="{{ route('home') }}" class="text-indigo-600 hover:underline text-sm">← العودة للرئيسية</a>
    </div>

    <h1 class="text-3xl font-bold mb-2">سياسة الخصوصية</h1>
    <p class="text-gray-500 text-sm mb-8">آخر تحديث: يونيو 2026</p>

    <div class="prose prose-gray max-w-none space-y-6 text-gray-700 leading-relaxed">

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">1. المقدمة</h2>
            <p>تُشغِّل شركة دراهم ("نحن"، "لنا"، "خاصتنا") منصة <strong>دراهم</strong> لإدارة الأعمال المالية للمستقلين. تصف هذه السياسة كيفية جمع بياناتك الشخصية، واستخدامها، وحمايتها.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">2. البيانات التي نجمعها</h2>
            <ul class="list-disc list-inside space-y-1">
                <li>معلومات الحساب: الاسم، البريد الإلكتروني، كلمة المرور (مشفَّرة)</li>
                <li>بيانات الاستخدام: المشاريع، العملاء، الفواتير، المعاملات المالية التي تُدخلها</li>
                <li>بيانات الدفع: يُعالَج الدفع عبر بوابة Togo — لا نخزّن أرقام بطاقاتك</li>
                <li>بيانات تقنية: عنوان IP، متصفح الويب، توقيت الجلسات</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">3. استخدام البيانات</h2>
            <p>نستخدم بياناتك لـ:</p>
            <ul class="list-disc list-inside space-y-1">
                <li>تقديم خدمات المنصة وتطويرها</li>
                <li>معالجة الاشتراكات والمدفوعات</li>
                <li>إرسال إشعارات خدمية (تأكيد دفع، تذكير انتهاء اشتراك)</li>
                <li>دعم العملاء</li>
                <li>الامتثال للمتطلبات القانونية</li>
            </ul>
            <p>لا نبيع بياناتك لأطراف ثالثة بأي شكل.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">4. الاحتفاظ بالبيانات</h2>
            <p>نحتفظ ببياناتك طوال فترة نشاط حسابك. بعد إلغاء الاشتراك أو الحذف:</p>
            <ul class="list-disc list-inside space-y-1">
                <li>البيانات المالية (الفواتير، طلبات الدفع): تُحتفظ بها <strong>7 سنوات</strong> للامتثال الضريبي</li>
                <li>بيانات الحساب الشخصية: تُحذف خلال <strong>30 يوماً</strong> من طلب الحذف</li>
                <li>سجلات الأنشطة التقنية: تُحتفظ بها <strong>90 يوماً</strong></li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">5. حقوقك</h2>
            <p>يحق لك في أي وقت: الوصول لبياناتك، تصحيحها، طلب حذفها، أو الاعتراض على معالجتها. تواصل معنا عبر البريد الإلكتروني للدعم.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">6. الأمان</h2>
            <p>نستخدم تشفير HTTPS، كلمات مرور مشفَّرة بـ bcrypt، وعزل بيانات كل مستخدم عن غيره. الوصول الإداري للبيانات محدود ومسجَّل.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">7. التواصل</h2>
            <p>لأي استفسار عن الخصوصية: <strong>support@darahum.com</strong></p>
        </section>

    </div>

    <div class="mt-12 pt-6 border-t border-gray-200 flex flex-wrap gap-4 text-sm text-gray-500">
        <a href="{{ route('legal.terms') }}" class="hover:text-indigo-600">الشروط والأحكام</a>
        <a href="{{ route('legal.refund') }}" class="hover:text-indigo-600">سياسة الاسترداد</a>
        <a href="{{ route('legal.subscription-terms') }}" class="hover:text-indigo-600">شروط الاشتراك</a>
        <a href="{{ route('legal.cancellation') }}" class="hover:text-indigo-600">سياسة الإلغاء</a>
    </div>
</div>
</body>
</html>

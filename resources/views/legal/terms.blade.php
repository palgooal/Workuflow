<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>الشروط والأحكام — دراهم</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Readex+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased" style="font-family: 'Readex Pro', sans-serif;">

<div class="max-w-3xl mx-auto px-4 py-12">

    <div class="mb-8">
        <a href="{{ route('home') }}" class="text-indigo-600 hover:underline text-sm">← العودة للرئيسية</a>
    </div>

    <h1 class="text-3xl font-bold mb-2">الشروط والأحكام</h1>
    <p class="text-gray-500 text-sm mb-8">آخر تحديث: يونيو 2026</p>

    <div class="prose prose-gray max-w-none space-y-6 text-gray-700 leading-relaxed">

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">1. قبول الشروط</h2>
            <p>باستخدامك لمنصة <strong>دراهم</strong>، فأنت توافق على الالتزام بهذه الشروط كاملةً. إذا كنت لا توافق، يُرجى التوقف عن استخدام المنصة.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">2. الخدمة</h2>
            <p>دراهم منصة سحابية تتيح للمستقلين وأصحاب المشاريع الصغيرة إدارة مشاريعهم، عملائهم، فواتيرهم، ومعاملاتهم المالية. الخدمة تعمل على نموذج SaaS (اشتراك شهري أو سنوي).</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">3. الحساب والمسؤولية</h2>
            <ul class="list-disc list-inside space-y-1">
                <li>أنت مسؤول عن حماية بيانات دخولك</li>
                <li>لا يُسمح بمشاركة الحساب مع أطراف أخرى</li>
                <li>البيانات التي تُدخلها هي ملكك — نحن لا ندّعي ملكيتها</li>
                <li>يجب أن تكون بياناتك المالية دقيقة ومشروعة</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">4. الاشتراكات والدفع</h2>
            <p>تفاصيل الاشتراك موثَّقة في <a href="{{ route('legal.subscription-terms') }}" class="text-indigo-600 hover:underline">شروط الاشتراك</a> و<a href="{{ route('legal.refund') }}" class="text-indigo-600 hover:underline">سياسة الاسترداد</a>.</p>
            <ul class="list-disc list-inside space-y-1">
                <li>الفوترة <strong>شهرية</strong> أو <strong>سنوية مدفوعة مقدماً</strong></li>
                <li>التفعيل فوري بعد تأكيد الدفع</li>
                <li>جميع المبالغ بالعملة المعروضة عند الشراء</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">5. الاستخدام المقبول</h2>
            <p>يُحظر استخدام المنصة لـ:</p>
            <ul class="list-disc list-inside space-y-1">
                <li>أي نشاط غير قانوني أو احتيالي</li>
                <li>غسيل الأموال أو تمويل الإرهاب</li>
                <li>انتهاك حقوق الملكية الفكرية</li>
                <li>محاولة اختراق أو تعطيل النظام</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">6. توقف الخدمة</h2>
            <p>نحتفظ بحق تعليق أو إنهاء حسابك فوراً في حالة انتهاك هذه الشروط. في حالة التوقف المجدوَل، نُخطرك قبل 7 أيام على الأقل.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">7. تحديد المسؤولية</h2>
            <p>دراهم أداة مساعدة وليست مستشاراً مالياً أو قانونياً. نحن غير مسؤولين عن القرارات المالية التي تتخذها بناءً على المنصة. الحد الأقصى لمسؤوليتنا هو مبلغ الاشتراك المدفوع في آخر شهر.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">8. التعديلات</h2>
            <p>نُعلمك بأي تغييرات جوهرية على هذه الشروط قبل 30 يوماً عبر البريد الإلكتروني. استمرارك في الاستخدام يعني قبولك للشروط المحدَّثة.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-2 text-gray-900">9. القانون المطبَّق</h2>
            <p>تخضع هذه الشروط لقوانين المملكة العربية السعودية. أي نزاع يُحسم أمام المحاكم السعودية المختصة.</p>
        </section>

    </div>

    <div class="mt-12 pt-6 border-t border-gray-200 flex flex-wrap gap-4 text-sm text-gray-500">
        <a href="{{ route('legal.privacy') }}" class="hover:text-indigo-600">سياسة الخصوصية</a>
        <a href="{{ route('legal.refund') }}" class="hover:text-indigo-600">سياسة الاسترداد</a>
        <a href="{{ route('legal.subscription-terms') }}" class="hover:text-indigo-600">شروط الاشتراك</a>
        <a href="{{ route('legal.cancellation') }}" class="hover:text-indigo-600">سياسة الإلغاء</a>
    </div>
</div>
</body>
</html>

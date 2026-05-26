@extends('portal.layouts.portal')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center">
    <div class="w-full max-w-lg text-center">

        <div class="w-16 h-16 bg-indigo-100 rounded-2xl flex items-center justify-center mx-auto mb-5">
            <svg class="w-9 h-9 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-3">كيف تحصل على رمز الوصول؟</h1>
        <p class="text-gray-500 text-sm mb-8 leading-relaxed">
            بوابة العميل تستخدم نظام رموز آمنة — لا كلمات مرور تقليدية.
            رمزك الشخصي يُنشأ ويُرسل إليك من قِبل الشخص الذي يدير حسابك.
        </p>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 text-right space-y-5">
            <div class="flex items-start gap-4">
                <div class="w-8 h-8 rounded-full bg-indigo-600 text-white text-sm font-bold flex items-center justify-center shrink-0 mt-0.5">1</div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">تواصل مع مزود الخدمة</h3>
                    <p class="text-xs text-gray-500 mt-1">أرسل لهم طلباً للوصول إلى بوابتك الإلكترونية</p>
                </div>
            </div>
            <div class="flex items-start gap-4">
                <div class="w-8 h-8 rounded-full bg-indigo-600 text-white text-sm font-bold flex items-center justify-center shrink-0 mt-0.5">2</div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">استلام الرمز بالبريد الإلكتروني</h3>
                    <p class="text-xs text-gray-500 mt-1">سيرسل لك رمز وصول خاص صالح لمدة محددة</p>
                </div>
            </div>
            <div class="flex items-start gap-4">
                <div class="w-8 h-8 rounded-full bg-indigo-600 text-white text-sm font-bold flex items-center justify-center shrink-0 mt-0.5">3</div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">أدخل الرمز في صفحة الدخول</h3>
                    <p class="text-xs text-gray-500 mt-1">انسخ الرمز الكامل وألصقه في خانة الدخول</p>
                </div>
            </div>
        </div>

        <div class="mt-8">
            <a href="{{ route('portal.auth') }}"
               class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
                لديّ رمز — سجّل دخولي
            </a>
        </div>
    </div>
</div>
@endsection

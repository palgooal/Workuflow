@extends('layouts.app')

@section('title', 'الاشتراك والفوترة')

@section('content')
<div class="max-w-5xl mx-auto space-y-8">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">الاشتراك والفوترة</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">اختر الخطة المناسبة لاحتياجاتك</p>
    </div>

    {{-- Current Plan Banner --}}
    @if($subscription && $subscription->isActive())
        <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-2xl p-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/40 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-indigo-900 dark:text-indigo-100">اشتراك {{ $currentPlan->label() }} نشط</p>
                    @if($subscription->ends_at)
                        <p class="text-sm text-indigo-600 dark:text-indigo-400">
                            يتجدد في {{ $subscription->ends_at->translatedFormat('d M Y') }}
                        </p>
                    @endif
                </div>
            </div>
            <form action="{{ route('billing.portal') }}" method="POST">
                @csrf
                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-xl hover:bg-indigo-700 transition font-medium">
                    إدارة الاشتراك
                </button>
            </form>
        </div>
    @endif

    {{-- Flash --}}
    @if(session('success'))
        <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('info'))
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-400 px-4 py-3 rounded-xl text-sm">
            {{ session('info') }}
        </div>
    @endif

    {{-- Payment Provider Not Ready Banner --}}
    @if(! $providerReady)
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl px-5 py-4 flex items-center gap-3">
            <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <p class="text-sm text-amber-700 dark:text-amber-400">
                بوابة الدفع قيد الإعداد — تواصل مع الدعم لترقية خطتك يدوياً حتى يتم تفعيلها.
            </p>
        </div>
    @endif

    {{-- Pricing Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Free Plan --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border-2 {{ $currentPlan->value === 'free' ? 'border-indigo-500' : 'border-gray-200 dark:border-gray-800' }} p-6 relative">
            @if($currentPlan->value === 'free')
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-indigo-600 text-white text-xs font-medium rounded-full">
                    خطتك الحالية
                </span>
            @endif

            <div class="mb-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">مجاني</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">للأفراد والمستقلين المبتدئين</p>
            </div>

            <div class="mb-6">
                <span class="text-3xl font-bold text-gray-900 dark:text-white">0</span>
                <span class="text-gray-500 dark:text-gray-400"> SAR / شهر</span>
            </div>

            <ul class="space-y-2 mb-6 text-sm text-gray-600 dark:text-gray-400">
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    حتى 2 مشروع
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    50 معاملة / شهر
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    إدارة الميزانية
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span class="text-gray-400">التقارير المتقدمة</span>
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span class="text-gray-400">تصدير البيانات</span>
                </li>
            </ul>

            <button disabled
                    class="w-full py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-600 rounded-xl text-sm font-medium cursor-not-allowed">
                الخطة الحالية
            </button>
        </div>

        {{-- Pro Plan --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border-2 {{ $currentPlan->value === 'pro' ? 'border-indigo-500' : 'border-gray-200 dark:border-gray-800' }} p-6 relative shadow-lg">
            @if($currentPlan->value === 'pro')
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-indigo-600 text-white text-xs font-medium rounded-full">
                    خطتك الحالية
                </span>
            @else
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-indigo-100 text-indigo-700 text-xs font-medium rounded-full">
                    الأكثر شيوعاً
                </span>
            @endif

            <div class="mb-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Pro</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">للمستقلين المحترفين</p>
            </div>

            <div class="mb-6">
                <span class="text-3xl font-bold text-gray-900 dark:text-white">99</span>
                <span class="text-gray-500 dark:text-gray-400"> SAR / شهر</span>
            </div>

            <ul class="space-y-2 mb-6 text-sm text-gray-600 dark:text-gray-400">
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    حتى 10 مشاريع
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    500 معاملة / شهر
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    التقارير المتقدمة
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    تصدير البيانات
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span class="text-gray-400">API Access</span>
                </li>
            </ul>

            @if($currentPlan->value === 'pro')
                <form action="{{ route('billing.portal') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full py-2.5 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-xl text-sm font-medium hover:bg-indigo-200 transition">
                        إدارة الاشتراك
                    </button>
                </form>
            @else
                <form action="{{ route('billing.checkout') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan" value="pro">
                    <button type="submit" class="w-full py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition">
                        الترقية إلى Pro
                    </button>
                </form>
            @endif
        </div>

        {{-- Business Plan --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border-2 {{ $currentPlan->value === 'business' ? 'border-indigo-500' : 'border-gray-200 dark:border-gray-800' }} p-6 relative">
            @if($currentPlan->value === 'business')
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-indigo-600 text-white text-xs font-medium rounded-full">
                    خطتك الحالية
                </span>
            @endif

            <div class="mb-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Business</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">للأعمال والفرق الصغيرة</p>
            </div>

            <div class="mb-6">
                <span class="text-3xl font-bold text-gray-900 dark:text-white">299</span>
                <span class="text-gray-500 dark:text-gray-400"> SAR / شهر</span>
            </div>

            <ul class="space-y-2 mb-6 text-sm text-gray-600 dark:text-gray-400">
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    مشاريع غير محدودة
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    معاملات غير محدودة
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    كل ميزات Pro
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    API Access كامل
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    دعم مخصص
                </li>
            </ul>

            @if($currentPlan->value === 'business')
                <form action="{{ route('billing.portal') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full py-2.5 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-xl text-sm font-medium hover:bg-indigo-200 transition">
                        إدارة الاشتراك
                    </button>
                </form>
            @else
                <form action="{{ route('billing.checkout') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan" value="business">
                    <button type="submit" class="w-full py-2.5 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-xl text-sm font-medium hover:bg-gray-700 dark:hover:bg-gray-100 transition">
                        الترقية إلى Business
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- FAQ --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
        <h2 class="font-semibold text-gray-900 dark:text-white mb-4">أسئلة شائعة</h2>
        <div class="space-y-4 text-sm text-gray-600 dark:text-gray-400">
            <div>
                <p class="font-medium text-gray-900 dark:text-white">هل يمكنني إلغاء الاشتراك في أي وقت؟</p>
                <p class="mt-1">نعم، يمكنك إلغاء اشتراكك من خلال "إدارة الاشتراك" وسيستمر حتى نهاية الفترة المدفوعة.</p>
            </div>
            <div>
                <p class="font-medium text-gray-900 dark:text-white">ما هي طرق الدفع المتاحة؟</p>
                <p class="mt-1">نقبل جميع بطاقات الائتمان والخصم الدولية عبر Stripe.</p>
            </div>
            <div>
                <p class="font-medium text-gray-900 dark:text-white">ماذا يحدث لبياناتي عند إلغاء الاشتراك؟</p>
                <p class="mt-1">تبقى بياناتك محفوظة وتعود لخطة المجاني مع الحدود المعتادة.</p>
            </div>
        </div>
    </div>

</div>
@endsection

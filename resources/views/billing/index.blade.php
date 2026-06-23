@extends('layouts.app')

@section('title', 'الاشتراك والفوترة')

@section('content')
<div class="max-w-5xl mx-auto space-y-8">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-ink tracking-tight">الاشتراك والفوترة</h1>
        <p class="text-sm text-muted mt-1">اختر الخطة المناسبة لاحتياجاتك</p>
    </div>

    {{-- Current Plan Banner --}}
    @if($subscription && $subscription->isActive())
        <div class="bg-brand-50 dark:bg-brand-900/20 border border-brand/30 dark:border-brand-700 rounded-2xl p-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-brand-100 dark:bg-brand-900/40 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand dark:text-brand/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-brand-900 dark:text-brand-100">اشتراك {{ $currentPlan->label() }} نشط</p>
                    @if($subscription->ends_at)
                        <p class="text-sm text-brand dark:text-brand/70">
                            يتجدد في {{ $subscription->ends_at->translatedFormat('d M Y') }}
                        </p>
                    @endif
                </div>
            </div>
            <form action="{{ route('billing.portal') }}" method="POST">
                @csrf
                <button type="submit"
                        class="px-4 py-2 bg-brand text-white text-sm rounded-xl hover:bg-brand-600 transition font-medium">
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

    {{-- Upgrade CTA (Manual Billing) --}}
    @if(! $providerReady && $currentPlan->value === 'free')
        @php $ownerWa = config('billing.owner_whatsapp'); @endphp
        @if($ownerWa)
        <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl px-5 py-4 flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/40 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-emerald-900 dark:text-emerald-100 text-sm">هل تريد الترقية إلى Pro؟</p>
                    <p class="text-xs text-emerald-700 dark:text-emerald-400 mt-0.5">تواصل معنا على واتساب وسنفعّل خطتك خلال دقائق</p>
                </div>
            </div>
            <a href="https://wa.me/{{ $ownerWa }}?text={{ urlencode('مرحباً، أريد الترقية إلى خطة Pro في دراهم') }}"
               target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-xl hover:bg-emerald-700 transition flex-shrink-0">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                تواصل على واتساب
            </a>
        </div>
        @endif
    @endif

    {{-- Pricing Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Free Plan --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-2 {{ $currentPlan->value === 'free' ? 'border-brand' : 'border-slate-200 dark:border-slate-800' }} p-6 relative">
            @if($currentPlan->value === 'free')
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-brand text-white text-xs font-medium rounded-full">
                    خطتك الحالية
                </span>
            @endif

            <div class="mb-4">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">مجاني</h3>
                <p class="text-sm text-muted mt-1">للأفراد والمستقلين المبتدئين</p>
            </div>

            <div class="mb-6">
                <span class="text-3xl font-bold text-slate-900 dark:text-white">0</span>
                <span class="text-slate-500 dark:text-slate-400"> SAR / شهر</span>
            </div>

            <ul class="space-y-2 mb-6 text-sm text-slate-600 dark:text-slate-400">
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
                    <svg class="w-4 h-4 text-slate-300 dark:text-slate-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span class="text-slate-400">التقارير المتقدمة</span>
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-300 dark:text-slate-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span class="text-slate-400">تصدير البيانات</span>
                </li>
            </ul>

            <button disabled
                    class="w-full py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600 rounded-xl text-sm font-medium cursor-not-allowed">
                الخطة الحالية
            </button>
        </div>

        {{-- Pro Plan --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-2 {{ $currentPlan->value === 'pro' ? 'border-brand' : 'border-slate-200 dark:border-slate-800' }} p-6 relative shadow-lg">
            @if($currentPlan->value === 'pro')
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-brand text-white text-xs font-medium rounded-full">
                    خطتك الحالية
                </span>
            @else
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-brand-100 text-brand-600 text-xs font-medium rounded-full">
                    الأكثر شيوعاً
                </span>
            @endif

            <div class="mb-4">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Pro</h3>
                <p class="text-sm text-muted mt-1">للمستقلين المحترفين</p>
            </div>

            <div class="mb-6">
                <span class="text-3xl font-bold text-slate-900 dark:text-white">99</span>
                <span class="text-slate-500 dark:text-slate-400"> SAR / شهر</span>
            </div>

            <ul class="space-y-2 mb-6 text-sm text-slate-600 dark:text-slate-400">
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
                    <svg class="w-4 h-4 text-slate-300 dark:text-slate-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span class="text-slate-400">API Access</span>
                </li>
            </ul>

            @if($currentPlan->value === 'pro')
                <form action="{{ route('billing.portal') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full py-2.5 bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand/50 rounded-xl text-sm font-medium hover:bg-brand-100 transition">
                        إدارة الاشتراك
                    </button>
                </form>
            @else
                <a href="{{ route('billing.upgrade') }}"
                   class="block w-full py-2.5 bg-brand text-white rounded-xl text-sm font-medium hover:bg-brand-600 transition text-center">
                    الترقية إلى Pro
                </a>
            @endif
        </div>

        {{-- Business Plan --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border-2 {{ $currentPlan->value === 'business' ? 'border-brand' : 'border-slate-200 dark:border-slate-800' }} p-6 relative">
            @if($currentPlan->value === 'business')
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-brand text-white text-xs font-medium rounded-full">
                    خطتك الحالية
                </span>
            @endif

            <div class="mb-4">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Business</h3>
                <p class="text-sm text-muted mt-1">للأعمال والفرق الصغيرة</p>
            </div>

            <div class="mb-6">
                <span class="text-3xl font-bold text-slate-900 dark:text-white">299</span>
                <span class="text-slate-500 dark:text-slate-400"> SAR / شهر</span>
            </div>

            <ul class="space-y-2 mb-6 text-sm text-slate-600 dark:text-slate-400">
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
                    <button type="submit" class="w-full py-2.5 bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand/50 rounded-xl text-sm font-medium hover:bg-brand-100 transition">
                        إدارة الاشتراك
                    </button>
                </form>
            @else
                <a href="{{ route('billing.upgrade') }}"
                   class="block w-full py-2.5 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-xl text-sm font-medium hover:bg-slate-700 dark:hover:bg-slate-100 transition text-center">
                    الترقية إلى Business
                </a>
            @endif
        </div>
    </div>

    {{-- FAQ --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
        <h2 class="font-semibold text-slate-900 dark:text-white mb-4">أسئلة شائعة</h2>
        <div class="space-y-4 text-sm text-slate-600 dark:text-slate-400">
            <div>
                <p class="font-medium text-slate-900 dark:text-white">هل يمكنني إلغاء الاشتراك في أي وقت؟</p>
                <p class="mt-1">نعم، يمكنك إلغاء اشتراكك وسيستمر حتى نهاية الفترة المدفوعة.</p>
            </div>
            <div>
                <p class="font-medium text-slate-900 dark:text-white">كيف أدفع؟</p>
                <p class="mt-1">حالياً عبر التحويل البنكي اليدوي — تواصل معنا على واتساب وسنرسل لك التعليمات.</p>
            </div>
            <div>
                <p class="font-medium text-slate-900 dark:text-white">ماذا يحدث لبياناتي عند إلغاء الاشتراك؟</p>
                <p class="mt-1">تبقى بياناتك محفوظة وتعود لخطة المجاني مع الحدود المعتادة.</p>
            </div>
        </div>
    </div>

</div>
@endsection

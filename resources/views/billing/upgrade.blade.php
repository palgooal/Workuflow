@extends('layouts.app')

@section('title', 'ترقية الخطة')

@section('content')
<div class="max-w-3xl mx-auto space-y-8">

    {{-- Header --}}
    <div class="text-center space-y-3">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-brand-100 dark:bg-brand-900/40 rounded-2xl mb-2">
            <svg class="w-8 h-8 text-brand dark:text-brand/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-ink tracking-tight">ترقية خطتك</h1>
        <p class="text-slate-500 dark:text-slate-400 text-sm max-w-md mx-auto">
            تواصل معنا مباشرة وسنفعّل خطتك خلال دقائق — بدون بطاقات ائتمان
        </p>
    </div>

    {{-- Current Plan Notice --}}
    @if($currentPlan->value === 'free')
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-2xl px-5 py-3 text-center text-sm text-blue-700 dark:text-blue-300">
        أنت حالياً على الخطة <strong>المجانية</strong> — الترقية تفتح لك جميع الميزات المتقدمة
    </div>
    @endif

    {{-- Plans --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

        {{-- Pro --}}
        @php
            $proMonthly      = $planPrices['pro']['monthly']  ?? ['price' => '17', 'currency' => 'USD', 'sar_equiv' => '64', 'jod_equiv' => '12', 'ils_equiv' => '63'];
            $proAnnual       = $planPrices['pro']['annual']   ?? ['price' => '13', 'currency' => 'USD'];
            $proEmail        = auth()->user()->email ?? '';
            $proMonthlyPrice = $proMonthly['price'] ?? '17';
            $proAnnualPrice  = $proAnnual['price']  ?? '13';
            $proAnnualTotal  = (int)$proAnnualPrice * 12;
            $proAnnualSaving = ((int)$proMonthlyPrice - (int)$proAnnualPrice) * 12;
            $proWaMsgMonthly = "مرحباً، أريد الاشتراك في خطة Pro (شهري - \${$proMonthlyPrice}/شهر) - حسابي: {$proEmail}";
            $proWaMsgAnnual  = "مرحباً، أريد الاشتراك في خطة Pro (سنوي - \${$proAnnualPrice}/شهر تُدفع \${$proAnnualTotal} سنوياً) - حسابي: {$proEmail}";
        @endphp
        <div x-data="{ cycle: 'monthly' }"
             class="bg-white dark:bg-slate-900 rounded-2xl border-2 border-brand/60 p-6 relative shadow-md">
            <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-brand text-white text-xs font-medium rounded-full">
                الأكثر شيوعاً
            </span>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1">Pro</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-3">للمستقلين المحترفين</p>

            {{-- Cycle toggle --}}
            <div class="flex bg-slate-100 dark:bg-slate-800 rounded-lg p-0.5 mb-3 text-xs">
                <button type="button"
                        @click="cycle = 'monthly'"
                        :class="cycle === 'monthly' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 dark:text-slate-400'"
                        class="flex-1 py-1.5 px-2 rounded-md transition-all font-medium">
                    شهري
                </button>
                <button type="button"
                        @click="cycle = 'annual'"
                        :class="cycle === 'annual' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 dark:text-slate-400'"
                        class="flex-1 py-1.5 px-2 rounded-md transition-all font-medium flex items-center justify-center gap-1">
                    سنوي
                    <span class="text-emerald-500">وفّر 24%</span>
                </button>
            </div>

            {{-- Reactive price --}}
            <div class="mb-1">
                <span class="text-3xl font-bold text-slate-900 dark:text-white"
                      x-text="cycle === 'monthly' ? '${{ $proMonthlyPrice }}' : '${{ $proAnnualPrice }}'"></span>
                <span class="text-slate-500 dark:text-slate-400"> {{ $proMonthly['currency'] }} / شهر</span>
            </div>
            <div class="min-h-[1.25rem] mb-1">
                <p class="text-xs text-slate-400 dark:text-slate-500" x-show="cycle === 'monthly'">
                    أو ${{ $proAnnualPrice }} / شهر عند الدفع سنوياً
                </p>
                <p class="text-xs text-emerald-600 dark:text-emerald-400" x-show="cycle === 'annual'" x-cloak>
                    تُدفع ${{ $proAnnualTotal }} سنوياً — تـوفير ${{ $proAnnualSaving }}
                </p>
            </div>
            {{-- Local equivalents --}}
            <p class="text-xs text-slate-400 dark:text-slate-500 mb-5">
                ≈ {{ $proMonthly['sar_equiv'] ?? '64' }} ريال
                &nbsp;·&nbsp; ≈ {{ $proMonthly['jod_equiv'] ?? '12' }} دينار
                &nbsp;·&nbsp; ≈ {{ $proMonthly['ils_equiv'] ?? '63' }} شيكل
            </p>

            <ul class="space-y-2 text-sm text-slate-600 dark:text-slate-400 mb-6">
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    مشاريع غير محدودة
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    1,000 معاملة / شهر
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    التقارير المتقدمة
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    تصدير البيانات
                </li>
            </ul>

            @if($providerReady)
            <form method="POST" action="{{ route('billing.checkout') }}">
                @csrf
                <input type="hidden" name="plan" value="pro">
                <input type="hidden" name="cycle" :value="cycle">
                <button type="submit"
                        class="flex items-center justify-center w-full py-3 bg-brand text-white font-semibold rounded-xl hover:bg-brand-600 transition text-sm"
                        x-text="cycle === 'monthly' ? 'الدفع الآن — Pro شهري' : 'الدفع الآن — Pro سنوي'">
                </button>
            </form>
            <p class="text-center text-xs text-slate-400 dark:text-slate-500 mt-2">
                سيتم تحويلك إلى بوابة Togo.ps الآمنة لإتمام الدفع.
            </p>
            @elseif($ownerWhatsapp)
            {{-- WhatsApp monthly --}}
            <a x-show="cycle === 'monthly'"
               href="https://wa.me/{{ $ownerWhatsapp }}?text={{ urlencode($proWaMsgMonthly) }}"
               target="_blank"
               class="flex items-center justify-center gap-2 w-full py-3 bg-emerald-600 text-white font-semibold rounded-xl hover:bg-emerald-700 transition text-sm">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                اطلب Pro شهري — واتساب
            </a>
            {{-- WhatsApp annual --}}
            <a x-show="cycle === 'annual'" x-cloak
               href="https://wa.me/{{ $ownerWhatsapp }}?text={{ urlencode($proWaMsgAnnual) }}"
               target="_blank"
               class="flex items-center justify-center gap-2 w-full py-3 bg-emerald-600 text-white font-semibold rounded-xl hover:bg-emerald-700 transition text-sm">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                اطلب Pro سنوي — واتساب
            </a>
            @else
            <a href="{{ route('billing.index') }}"
               class="flex items-center justify-center w-full py-3 bg-brand text-white font-semibold rounded-xl hover:bg-brand-600 transition text-sm">
                عرض خيارات الترقية
            </a>
            @endif
        </div>

        {{-- Business --}}
        @php
            $bizMonthly      = $planPrices['business']['monthly']  ?? ['price' => '45', 'currency' => 'USD', 'sar_equiv' => '169', 'jod_equiv' => '32', 'ils_equiv' => '167'];
            $bizAnnual       = $planPrices['business']['annual']   ?? ['price' => '34', 'currency' => 'USD'];
            $bizEmail        = auth()->user()->email ?? '';
            $bizMonthlyPrice = $bizMonthly['price'] ?? '45';
            $bizAnnualPrice  = $bizAnnual['price']  ?? '34';
            $bizAnnualTotal  = (int)$bizAnnualPrice * 12;
            $bizAnnualSaving = ((int)$bizMonthlyPrice - (int)$bizAnnualPrice) * 12;
            $bizWaMsgMonthly = "مرحباً، أريد الاشتراك في خطة Business (شهري - \${$bizMonthlyPrice}/شهر) - حسابي: {$bizEmail}";
            $bizWaMsgAnnual  = "مرحباً، أريد الاشتراك في خطة Business (سنوي - \${$bizAnnualPrice}/شهر تُدفع \${$bizAnnualTotal} سنوياً) - حسابي: {$bizEmail}";
        @endphp
        <div x-data="{ cycle: 'monthly' }"
             class="bg-white dark:bg-slate-900 rounded-2xl border-2 border-slate-200 dark:border-slate-800 p-6">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1">Business</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-3">للأعمال والفرق الصغيرة</p>

            {{-- Cycle toggle --}}
            <div class="flex bg-slate-100 dark:bg-slate-800 rounded-lg p-0.5 mb-3 text-xs">
                <button type="button"
                        @click="cycle = 'monthly'"
                        :class="cycle === 'monthly' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 dark:text-slate-400'"
                        class="flex-1 py-1.5 px-2 rounded-md transition-all font-medium">
                    شهري
                </button>
                <button type="button"
                        @click="cycle = 'annual'"
                        :class="cycle === 'annual' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 dark:text-slate-400'"
                        class="flex-1 py-1.5 px-2 rounded-md transition-all font-medium flex items-center justify-center gap-1">
                    سنوي
                    <span class="text-emerald-500">وفّر 24%</span>
                </button>
            </div>

            {{-- Reactive price --}}
            <div class="mb-1">
                <span class="text-3xl font-bold text-slate-900 dark:text-white"
                      x-text="cycle === 'monthly' ? '${{ $bizMonthlyPrice }}' : '${{ $bizAnnualPrice }}'"></span>
                <span class="text-slate-500 dark:text-slate-400"> {{ $bizMonthly['currency'] }} / شهر</span>
            </div>
            <div class="min-h-[1.25rem] mb-1">
                <p class="text-xs text-slate-400 dark:text-slate-500" x-show="cycle === 'monthly'">
                    أو ${{ $bizAnnualPrice }} / شهر عند الدفع سنوياً
                </p>
                <p class="text-xs text-emerald-600 dark:text-emerald-400" x-show="cycle === 'annual'" x-cloak>
                    تُدفع ${{ $bizAnnualTotal }} سنوياً — تـوفير ${{ $bizAnnualSaving }}
                </p>
            </div>
            {{-- Local equivalents --}}
            <p class="text-xs text-slate-400 dark:text-slate-500 mb-5">
                ≈ {{ $bizMonthly['sar_equiv'] ?? '169' }} ريال
                &nbsp;·&nbsp; ≈ {{ $bizMonthly['jod_equiv'] ?? '32' }} دينار
                &nbsp;·&nbsp; ≈ {{ $bizMonthly['ils_equiv'] ?? '167' }} شيكل
            </p>

            <ul class="space-y-2 text-sm text-slate-600 dark:text-slate-400 mb-6">
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
                    دعم مخصص + API Access
                </li>
            </ul>

            @if($providerReady)
            <form method="POST" action="{{ route('billing.checkout') }}">
                @csrf
                <input type="hidden" name="plan" value="business">
                <input type="hidden" name="cycle" :value="cycle">
                <button type="submit"
                        class="flex items-center justify-center w-full py-3 bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-semibold rounded-xl hover:bg-slate-700 dark:hover:bg-slate-100 transition text-sm"
                        x-text="cycle === 'monthly' ? 'الدفع الآن — Business شهري' : 'الدفع الآن — Business سنوي'">
                </button>
            </form>
            <p class="text-center text-xs text-slate-400 dark:text-slate-500 mt-2">
                سيتم تحويلك إلى بوابة Togo.ps الآمنة لإتمام الدفع.
            </p>
            @elseif($ownerWhatsapp)
            {{-- WhatsApp monthly --}}
            <a x-show="cycle === 'monthly'"
               href="https://wa.me/{{ $ownerWhatsapp }}?text={{ urlencode($bizWaMsgMonthly) }}"
               target="_blank"
               class="flex items-center justify-center gap-2 w-full py-3 bg-emerald-600 text-white font-semibold rounded-xl hover:bg-emerald-700 transition text-sm">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                اطلب Business شهري — واتساب
            </a>
            {{-- WhatsApp annual --}}
            <a x-show="cycle === 'annual'" x-cloak
               href="https://wa.me/{{ $ownerWhatsapp }}?text={{ urlencode($bizWaMsgAnnual) }}"
               target="_blank"
               class="flex items-center justify-center gap-2 w-full py-3 bg-emerald-600 text-white font-semibold rounded-xl hover:bg-emerald-700 transition text-sm">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                اطلب Business سنوي — واتساب
            </a>
            @else
            <a href="{{ route('billing.index') }}"
               class="flex items-center justify-center w-full py-3 bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-semibold rounded-xl hover:bg-slate-700 dark:hover:bg-slate-100 transition text-sm">
                عرض خيارات الترقية
            </a>
            @endif
        </div>
    </div>

    {{-- Currency Disclaimer --}}
    <p class="text-center text-xs text-slate-400 dark:text-slate-500">
        الفوترة بالدولار الأمريكي، والمعادلات المحلية تقديرية وقد تتفاوت حسب سعر الصرف.
    </p>

    {{-- How it works --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
        @if($providerReady)
        <h2 class="font-semibold text-slate-900 dark:text-white mb-4 text-sm">كيف تعمل الترقية عبر بوابة الدفع؟</h2>
        <ol class="space-y-3 text-sm text-slate-600 dark:text-slate-400">
            <li class="flex items-start gap-3">
                <span class="flex-shrink-0 w-6 h-6 bg-brand-100 dark:bg-brand-900/40 text-brand dark:text-brand/70 rounded-full flex items-center justify-center text-xs font-bold">١</span>
                <span>اختر الخطة المناسبة وحدد شهري أو سنوي</span>
            </li>
            <li class="flex items-start gap-3">
                <span class="flex-shrink-0 w-6 h-6 bg-brand-100 dark:bg-brand-900/40 text-brand dark:text-brand/70 rounded-full flex items-center justify-center text-xs font-bold">٢</span>
                <span>اضغط على زر "الدفع الآن" وسيتم تحويلك إلى بوابة الدفع الآمنة</span>
            </li>
            <li class="flex items-start gap-3">
                <span class="flex-shrink-0 w-6 h-6 bg-brand-100 dark:bg-brand-900/40 text-brand dark:text-brand/70 rounded-full flex items-center justify-center text-xs font-bold">٣</span>
                <span>بعد نجاح الدفع، سيتم تفعيل خطتك تلقائياً فوراً</span>
            </li>
        </ol>
        @else
        <h2 class="font-semibold text-slate-900 dark:text-white mb-4 text-sm">كيف تعمل الترقية اليدوية؟</h2>
        <ol class="space-y-3 text-sm text-slate-600 dark:text-slate-400">
            <li class="flex items-start gap-3">
                <span class="flex-shrink-0 w-6 h-6 bg-brand-100 dark:bg-brand-900/40 text-brand dark:text-brand/70 rounded-full flex items-center justify-center text-xs font-bold">١</span>
                <span>اضغط على "تواصل على واتساب" واختر الخطة المناسبة</span>
            </li>
            <li class="flex items-start gap-3">
                <span class="flex-shrink-0 w-6 h-6 bg-brand-100 dark:bg-brand-900/40 text-brand dark:text-brand/70 rounded-full flex items-center justify-center text-xs font-bold">٢</span>
                <span>نرسل لك تعليمات التحويل البنكي أو رابط الدفع</span>
            </li>
            <li class="flex items-start gap-3">
                <span class="flex-shrink-0 w-6 h-6 bg-brand-100 dark:bg-brand-900/40 text-brand dark:text-brand/70 rounded-full flex items-center justify-center text-xs font-bold">٣</span>
                <span>بعد تأكيد الدفع نفعّل خطتك فوراً — خلال دقائق</span>
            </li>
        </ol>
        @endif
    </div>

    {{-- Back link --}}
    <div class="text-center">
        <a href="{{ route('billing.index') }}" class="text-sm text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition">
            ← العودة لصفحة الاشتراك
        </a>
    </div>

</div>
@endsection

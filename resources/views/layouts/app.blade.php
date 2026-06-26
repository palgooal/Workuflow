<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="دراهم — منصة إدارة مال وأعمال للمستقلين وأصحاب المشاريع">
    <title>@yield('title', config('app.name', 'دراهم')) — {{ config('app.name', 'دراهم') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Readex+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-page text-ink antialiased">

{{-- شريط الانتحال — يظهر فقط عند دخول الأدمن كمستخدم --}}
@if(session('impersonator_id'))
<div class="bg-amber-500 text-white text-sm px-4 py-2 flex items-center justify-between gap-4 print:hidden">
    <div class="flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
        <span>أنت تتصفح كـ <strong>{{ Auth::user()->name }}</strong> — وضع المشاهدة كمستخدم</span>
    </div>
    <a href="{{ route('admin.impersonate.leave') }}"
       class="inline-flex items-center gap-1.5 bg-white/20 hover:bg-white/30 text-white
              text-xs font-semibold px-3 py-1 rounded-lg transition">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
        </svg>
        العودة للأدمن
    </a>
</div>
@endif

{{-- شريط الجوال — يظهر للمستخدمين الذين لم يضيفوا رقم جوالهم بعد --}}
@auth
@if(! auth()->user()->phone)
<div
    x-data="{ show: ! sessionStorage.getItem('phone_notice_dismissed') }"
    x-show="show"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 max-h-20"
    x-transition:leave-end="opacity-0 max-h-0"
    x-cloak
    role="status"
    aria-live="polite"
    class="bg-amber-50 border-b border-amber-200 text-amber-800 text-sm px-4 py-2.5 flex items-center justify-between gap-4 print:hidden"
>
    <div class="flex items-center gap-2 min-w-0">
        {{-- phone icon --}}
        <svg class="w-4 h-4 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
        </svg>
        <span class="truncate">أضف رقم جوالك لتسهيل التواصل معك واستلام تنبيهات الاشتراك والدعم.</span>
    </div>
    <div class="flex items-center gap-3 shrink-0">
        <a href="{{ route('settings.index') }}#profile"
           class="inline-flex items-center gap-1 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            إضافة رقم الجوال
        </a>
        <button
            @click="sessionStorage.setItem('phone_notice_dismissed', '1'); show = false"
            class="text-amber-400 hover:text-amber-600 transition focus:outline-none focus:ring-2 focus:ring-amber-400 rounded"
            aria-label="إغلاق الإشعار"
            type="button"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>
@endif
@endauth

{{-- ══════════════════════════════════════════════════════════════════════
     بانر التحقق من البريد — CONVERSION-01 Phase 2
     يظهر فقط للمستخدمين المدفوعين غير الموثَّقين أثناء فترة السماح.
     لا يمنع الوصول — تحذير لاتخاذ إجراء فقط.
══════════════════════════════════════════════════════════════════════ --}}
@auth
@if(auth()->user()->isInEmailVerificationGrace())
@php
    $graceDaysLeft = auth()->user()->graceDaysRemaining();
    $graceDaysText = match(true) {
        $graceDaysLeft >= 7  => '7 أيام',
        $graceDaysLeft >= 3  => "{$graceDaysLeft} أيام",
        $graceDaysLeft === 2 => 'يومين',
        default              => 'يوم واحد',
    };
@endphp
<div
    role="alert"
    aria-live="polite"
    class="bg-amber-50 border-b border-amber-300 text-amber-900 text-sm px-4 py-2.5 flex items-center justify-between gap-4 print:hidden"
>
    <div class="flex items-center gap-2.5 min-w-0">
        {{-- Warning icon --}}
        <svg class="w-4 h-4 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <span class="truncate">
            يرجى تأكيد بريدك الإلكتروني خلال
            <strong>{{ $graceDaysText }}</strong>
            للاستمرار في استخدام المنصة.
        </span>
    </div>
    <div class="flex items-center gap-3 shrink-0">
        {{-- CTA: انتقل لصفحة التحقق --}}
        <a href="{{ route('verification.notice') }}"
           class="inline-flex items-center gap-1 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-1">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            تأكيد البريد الآن
        </a>
        {{-- Resend: إعادة إرسال بريد التحقق --}}
        <form method="POST" action="{{ route('verification.send') }}" class="inline">
            @csrf
            <button type="submit"
                    class="text-amber-700 hover:text-amber-900 text-xs underline underline-offset-2 transition focus:outline-none"
                    title="إعادة إرسال بريد التحقق">
                إعادة الإرسال
            </button>
        </form>
    </div>
</div>
@endif
@endauth

<div class="min-h-screen flex" x-data="{ sidebarOpen: false }">

    @include('layouts.partials.sidebar')

    {{-- ===== Main Content ===== --}}
    <div class="flex-1 flex flex-col min-w-0">

        @include('layouts.partials.navbar')

        {{-- Flash (all screen sizes — desktop flash removed from navbar) --}}
        @if(session('success'))
            <div
                x-data="{ show: true }"
                x-show="show"
                x-init="setTimeout(() => show = false, 4000)"
                x-transition
                class="mx-4 mt-4 flex items-center gap-2 px-4 py-3 bg-success-soft border border-success/30 text-success-700 rounded-xl text-sm"
            >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mx-4 mt-4 flex items-center gap-2 px-4 py-3 bg-error-soft border border-error/30 text-red-700 rounded-xl text-sm">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- Validation Errors Banner --}}
        @if($errors->any())
            <div
                x-data="{ show: true }"
                x-show="show"
                x-transition
                class="mx-4 mt-4 px-4 py-3 bg-error-soft border border-error/30 rounded-xl text-sm text-red-700"
            >
                <div class="flex items-start gap-2">
                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    <div class="flex-1">
                        <p class="font-medium mb-1">يرجى تصحيح الأخطاء التالية:</p>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button @click="show = false" class="text-red-400 hover:text-red-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        {{-- Page Content --}}
        <main class="flex-1 p-4 sm:p-6">
            @yield('content')
        </main>

    </div>
</div>

{{-- Onboarding Modal — للمستخدمين الجدد --}}
<x-onboarding-modal />

{{-- Upgrade Modal — يظهر عند تجاوز حدود الخطة --}}
<x-upgrade-modal />

@stack('scripts')
</body>
</html>

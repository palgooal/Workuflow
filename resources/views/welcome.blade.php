<!DOCTYPE html>
<html lang="ar" dir="rtl" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دراهم — مال وأعمال | تحكم كامل في مالك وأعمالك</title>
    <meta name="description" content="دراهم — منصتك لإدارة العملاء والمشاريع والفواتير والإيرادات. تحكم كامل في مالك وأعمالك من مكان واحد.">
    <meta property="og:title" content="دراهم — مال وأعمال">
    <meta property="og:description" content="منصة مالية ذكية للمستقلين وأصحاب الأعمال. نظّم عملائك، مشاريعك، وإيراداتك من مكان واحد.">
    <meta property="og:type" content="website">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-alexandria bg-[#FAFAF9] text-[#0F172A] antialiased">


{{-- ═══════════════════════════════════════════
     § 1 — NAVBAR
════════════════════════════════════════════ --}}
<nav class="sticky top-0 z-50 bg-[#FAFAF9]/90 backdrop-blur-xl border-b border-slate-200/60">
    <div class="max-w-7xl mx-auto px-6 h-[68px] flex items-center justify-between">

        {{-- Logo --}}
        <a href="#" class="flex items-center gap-2.5 no-underline">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-[#2DCEA8] to-[#3730A3] flex items-center justify-center shadow-sm flex-shrink-0">
                <span class="text-white font-black text-sm leading-none">د</span>
            </div>
            <div class="flex flex-col leading-none">
                <span class="font-black text-lg text-[#0F172A] leading-none">دراهم</span>
                <span class="text-[0.6rem] text-accent font-medium leading-none mt-0.5">مال وأعمال</span>
            </div>
        </a>

        {{-- Nav Links --}}
        <ul class="hidden md:flex items-center gap-8 list-none m-0 p-0">
            <li>
                <a href="#features"
                   class="text-[#475569] font-medium text-sm no-underline hover:text-[#0F172A] transition-colors duration-150">
                    المميزات
                </a>
            </li>
            <li>
                <a href="#for-who"
                   class="text-[#475569] font-medium text-sm no-underline hover:text-[#0F172A] transition-colors duration-150">
                    لمن هو؟
                </a>
            </li>
            <li>
                <a href="#pricing"
                   class="text-[#475569] font-medium text-sm no-underline hover:text-[#0F172A] transition-colors duration-150">
                    الأسعار
                </a>
            </li>
        </ul>

        {{-- CTA Buttons --}}
        <div class="flex items-center gap-3">
            @auth
                <a href="{{ route('dashboard') }}"
                   class="bg-brand text-white font-semibold text-sm px-5 py-2.5 rounded-xl no-underline
                          hover:bg-brand/90 hover:-translate-y-px transition-all duration-200
                          shadow-[0_4px_14px_rgba(55,48,163,.3)]">
                    لوحة التحكم ←
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="hidden md:block text-[#475569] font-medium text-sm px-4 py-2 rounded-lg
                          hover:bg-slate-100 transition-colors no-underline">
                    دخول
                </a>
                <a href="{{ route('register') }}"
                   class="bg-brand text-white font-semibold text-sm px-5 py-2.5 rounded-xl no-underline
                          hover:bg-brand/90 hover:-translate-y-px transition-all duration-200
                          shadow-[0_4px_14px_rgba(55,48,163,.3)]">
                    ابدأ مجاناً
                </a>
            @endauth
        </div>

    </div>
</nav>


{{-- ═══════════════════════════════════════════
     § 2 — HERO
════════════════════════════════════════════ --}}
<section class="relative overflow-hidden pt-20 pb-0 px-6">

    {{-- Brand gradient background --}}
    <div class="absolute inset-0 bg-gradient-to-br from-[#EEF2FF] via-[#F5F7FF] to-[#FAFAF9] pointer-events-none"></div>
    {{-- Subtle grid lines --}}
    <div class="absolute inset-0 bg-[linear-gradient(rgba(55,48,163,.04)_1px,transparent_1px),linear-gradient(90deg,rgba(55,48,163,.04)_1px,transparent_1px)] bg-[size:60px_60px] pointer-events-none"></div>
    {{-- Brand glow top --}}
    <div class="absolute -top-40 right-1/2 translate-x-1/2 w-[600px] h-[600px] rounded-full bg-brand/8 blur-3xl pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto text-center">

        {{-- Badge --}}
        <div class="inline-flex items-center gap-2 bg-white border border-brand/20 text-brand text-xs font-semibold px-4 py-2 rounded-full mb-8 shadow-sm">
            <span class="w-1.5 h-1.5 rounded-full bg-brand inline-block animate-pulse"></span>
            مجاني حتى ٥٠ معاملة في الشهر · لا بطاقة ائتمان مطلوبة
        </div>

        {{-- H1 --}}
        <h1 class="text-4xl md:text-[3.2rem] lg:text-[4rem] font-black text-[#0F172A] leading-[1.1] max-w-4xl mx-auto mb-6 tracking-tight">
            تحكم كامل في
            <span class="text-brand relative">
                مالك وأعمالك
                <span class="absolute -bottom-1 right-0 left-0 h-0.5 bg-brand/30 rounded-full"></span>
            </span>
            <br class="hidden md:block">
            من مكان واحد
        </h1>

        {{-- Subheadline --}}
        <p class="text-lg md:text-xl text-[#475569] max-w-xl mx-auto mb-10 leading-relaxed">
            دراهم تساعد المستقلين وأصحاب الأعمال على إدارة عملائهم، مشاريعهم، فواتيرهم، وإيراداتهم بوضوح تام — دون تعقيد.
        </p>

        {{-- CTA Buttons --}}
        <div class="flex items-center justify-center gap-4 flex-wrap mb-5">
            @auth
                <a href="{{ route('dashboard') }}"
                   class="bg-brand text-white font-bold text-base px-10 py-4 rounded-xl no-underline
                          shadow-[0_8px_24px_rgba(55,48,163,.35)]
                          hover:bg-brand/90 hover:-translate-y-0.5 hover:shadow-[0_12px_32px_rgba(55,48,163,.4)]
                          transition-all duration-200">
                    اذهب للوحة التحكم ←
                </a>
            @else
                <a href="{{ route('register') }}"
                   class="bg-brand text-white font-bold text-base px-10 py-4 rounded-xl no-underline
                          shadow-[0_8px_24px_rgba(55,48,163,.35)]
                          hover:bg-brand/90 hover:-translate-y-0.5 hover:shadow-[0_12px_32px_rgba(55,48,163,.4)]
                          transition-all duration-200">
                    ابدأ مجاناً الآن ←
                </a>
                <a href="#features"
                   class="bg-white border border-slate-200 text-[#475569] font-semibold text-base px-8 py-4 rounded-xl
                          no-underline hover:border-brand/40 hover:text-brand transition-all duration-200 shadow-sm">
                    استكشف المميزات
                </a>
            @endauth
        </div>

        {{-- Trust micro text --}}
        <p class="text-sm text-[#94A3B8] mb-16">
            انضم إلى <strong class="text-[#475569] font-semibold">+٥٠٠٠</strong> مستقل وصاحب عمل يثقون في دراهم
        </p>

        {{-- ─── Dashboard Mockup ─── --}}
        <div class="relative max-w-5xl mx-auto">

            {{-- Glow behind mockup --}}
            <div class="absolute -inset-8 bg-brand/5 blur-3xl rounded-3xl pointer-events-none"></div>

            {{-- Browser window --}}
            <div class="relative bg-[#0B1120] rounded-2xl overflow-hidden shadow-[0_50px_100px_rgba(10,15,30,.35)] ring-1 ring-white/10">

                {{-- Browser chrome --}}
                <div class="bg-[#1A2332] px-5 py-3 flex items-center gap-3 border-b border-white/[0.06]">
                    <div class="flex gap-1.5 flex-shrink-0">
                        <div class="w-3 h-3 rounded-full bg-[#FF5F57]"></div>
                        <div class="w-3 h-3 rounded-full bg-[#FEBC2E]"></div>
                        <div class="w-3 h-3 rounded-full bg-[#28C840]"></div>
                    </div>
                    <div class="flex-1 mx-3 bg-[#0B1120] rounded-md h-6 flex items-center justify-center">
                        <span class="text-slate-500 text-xs">darahum.com/dashboard</span>
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                        <div class="w-4 h-4 rounded bg-white/5"></div>
                        <div class="w-4 h-4 rounded bg-white/5"></div>
                    </div>
                </div>

                {{-- Dashboard body --}}
                <div class="flex h-[420px]">

                    {{-- Sidebar --}}
                    <div class="w-48 flex-shrink-0 bg-[#0D1526] border-l border-white/[0.06] p-4 flex flex-col">
                        {{-- Brand mark --}}
                        <div class="flex items-center gap-2 px-1 py-1.5 mb-5">
                            <div class="w-6 h-6 rounded-md bg-gradient-to-br from-[#2DCEA8] to-[#3730A3] flex items-center justify-center flex-shrink-0">
                                <span class="text-white text-xs font-black">د</span>
                            </div>
                            <span class="text-white text-sm font-bold">دراهم</span>
                        </div>

                        {{-- Nav items --}}
                        <div class="space-y-0.5 flex-1">
                            <div class="flex items-center gap-2.5 px-3 py-2 rounded-lg bg-brand/10 text-brand text-xs font-medium cursor-pointer">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M2 10a8 8 0 1116 0A8 8 0 012 10zm8-3a1 1 0 100 2 1 1 0 000-2zm-1 4a1 1 0 112 0v2a1 1 0 11-2 0v-2z"/></svg>
                                لوحة التحكم
                            </div>
                            <div class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-400 text-xs cursor-pointer hover:text-slate-200">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                المشاريع
                            </div>
                            <div class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-400 text-xs cursor-pointer hover:text-slate-200">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
                                العملاء
                            </div>
                            <div class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-400 text-xs cursor-pointer hover:text-slate-200">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                الفواتير
                            </div>
                            <div class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-400 text-xs cursor-pointer hover:text-slate-200">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                المعاملات
                            </div>
                            <div class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-400 text-xs cursor-pointer hover:text-slate-200">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                التقارير
                            </div>
                        </div>

                        {{-- User info --}}
                        <div class="flex items-center gap-2 pt-3 border-t border-white/[0.06] mt-3">
                            <div class="w-6 h-6 rounded-full bg-brand/20 border border-brand/30 flex items-center justify-center flex-shrink-0">
                                <span class="text-brand text-[0.6rem] font-bold">م</span>
                            </div>
                            <div class="min-w-0">
                                <div class="text-white text-[0.65rem] font-medium truncate">محمد العمري</div>
                                <div class="text-slate-500 text-[0.55rem] truncate">خطة Pro</div>
                            </div>
                        </div>
                    </div>

                    {{-- Main Content --}}
                    <div class="flex-1 bg-[#0B1120] p-5 overflow-hidden flex flex-col gap-4">

                        {{-- Page Header --}}
                        <div class="flex items-center justify-between flex-shrink-0">
                            <div>
                                <h3 class="text-white font-bold text-sm">مرحباً، محمد 👋</h3>
                                <p class="text-slate-500 text-[0.65rem] mt-0.5">مايو ٢٠٢٦ · الربع الثاني</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="bg-white/5 text-slate-400 text-[0.65rem] px-2.5 py-1.5 rounded-lg border border-white/[0.06]">
                                    آخر ٣٠ يوم
                                </div>
                                <div class="bg-brand text-white text-[0.65rem] font-semibold px-3 py-1.5 rounded-lg cursor-pointer">
                                    + معاملة
                                </div>
                            </div>
                        </div>

                        {{-- Stats Grid --}}
                        <div class="grid grid-cols-4 gap-3 flex-shrink-0">
                            <div class="bg-[#1A2332] rounded-xl p-3 border border-accent/20">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-[#2DCEA8] text-[0.6rem] font-medium">الإيرادات</span>
                                    <span class="text-emerald-400 text-[0.55rem] bg-emerald-400/10 px-1.5 py-0.5 rounded">↑ ١٢٪</span>
                                </div>
                                <div class="text-white font-bold text-base">٤٥,٢٠٠</div>
                                <div class="text-slate-500 text-[0.55rem]">ريال سعودي</div>
                            </div>
                            <div class="bg-[#1A2332] rounded-xl p-3">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-slate-400 text-[0.6rem] font-medium">المصروفات</span>
                                    <span class="text-red-400 text-[0.55rem] bg-red-400/10 px-1.5 py-0.5 rounded">↑ ٣٪</span>
                                </div>
                                <div class="text-white font-bold text-base">١٢,٨٠٠</div>
                                <div class="text-slate-500 text-[0.55rem]">ريال سعودي</div>
                            </div>
                            <div class="bg-[#1A2332] rounded-xl p-3">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-slate-400 text-[0.6rem] font-medium">صافي الربح</span>
                                    <span class="text-emerald-400 text-[0.55rem] bg-emerald-400/10 px-1.5 py-0.5 rounded">↑ ١٨٪</span>
                                </div>
                                <div class="text-white font-bold text-base">٣٢,٤٠٠</div>
                                <div class="text-slate-500 text-[0.55rem]">ريال سعودي</div>
                            </div>
                            <div class="bg-[#1A2332] rounded-xl p-3">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-slate-400 text-[0.6rem] font-medium">غير مدفوع</span>
                                    <span class="text-amber-400 text-[0.55rem] bg-amber-400/10 px-1.5 py-0.5 rounded">٣ فواتير</span>
                                </div>
                                <div class="text-white font-bold text-base">٨,٥٠٠</div>
                                <div class="text-slate-500 text-[0.55rem]">ريال سعودي</div>
                            </div>
                        </div>

                        {{-- Chart + Transactions --}}
                        <div class="grid grid-cols-5 gap-3 flex-1 min-h-0">

                            {{-- Bar Chart --}}
                            <div class="col-span-3 bg-[#1A2332] rounded-xl p-4 flex flex-col min-h-0">
                                <div class="flex items-center justify-between mb-3 flex-shrink-0">
                                    <span class="text-white text-xs font-semibold">الإيرادات مقابل المصروفات</span>
                                    <div class="flex items-center gap-3">
                                        <span class="flex items-center gap-1 text-[0.6rem] text-brand">
                                            <span class="w-2 h-2 rounded-sm bg-brand inline-block"></span>إيرادات
                                        </span>
                                        <span class="flex items-center gap-1 text-[0.6rem] text-slate-500">
                                            <span class="w-2 h-2 rounded-sm bg-slate-600 inline-block"></span>مصروفات
                                        </span>
                                    </div>
                                </div>
                                {{-- Bars --}}
                                <div class="flex items-end gap-2 flex-1 pb-1 justify-between">
                                    {{-- Dec --}}
                                    <div class="flex-1 flex flex-col items-center gap-1">
                                        <div class="w-full flex items-end gap-0.5 h-20">
                                            <div class="flex-1 bg-brand/75 rounded-t-sm h-[60%]"></div>
                                            <div class="flex-1 bg-slate-600/60 rounded-t-sm h-[35%]"></div>
                                        </div>
                                        <span class="text-slate-500 text-[0.55rem]">ديس</span>
                                    </div>
                                    {{-- Jan --}}
                                    <div class="flex-1 flex flex-col items-center gap-1">
                                        <div class="w-full flex items-end gap-0.5 h-20">
                                            <div class="flex-1 bg-brand/75 rounded-t-sm h-[45%]"></div>
                                            <div class="flex-1 bg-slate-600/60 rounded-t-sm h-[25%]"></div>
                                        </div>
                                        <span class="text-slate-500 text-[0.55rem]">يناير</span>
                                    </div>
                                    {{-- Feb --}}
                                    <div class="flex-1 flex flex-col items-center gap-1">
                                        <div class="w-full flex items-end gap-0.5 h-20">
                                            <div class="flex-1 bg-brand/75 rounded-t-sm h-[75%]"></div>
                                            <div class="flex-1 bg-slate-600/60 rounded-t-sm h-[40%]"></div>
                                        </div>
                                        <span class="text-slate-500 text-[0.55rem]">فبراير</span>
                                    </div>
                                    {{-- Mar --}}
                                    <div class="flex-1 flex flex-col items-center gap-1">
                                        <div class="w-full flex items-end gap-0.5 h-20">
                                            <div class="flex-1 bg-brand/75 rounded-t-sm h-[50%]"></div>
                                            <div class="flex-1 bg-slate-600/60 rounded-t-sm h-[28%]"></div>
                                        </div>
                                        <span class="text-slate-500 text-[0.55rem]">مارس</span>
                                    </div>
                                    {{-- Apr --}}
                                    <div class="flex-1 flex flex-col items-center gap-1">
                                        <div class="w-full flex items-end gap-0.5 h-20">
                                            <div class="flex-1 bg-brand/75 rounded-t-sm h-[85%]"></div>
                                            <div class="flex-1 bg-slate-600/60 rounded-t-sm h-[45%]"></div>
                                        </div>
                                        <span class="text-slate-500 text-[0.55rem]">ابريل</span>
                                    </div>
                                    {{-- May --}}
                                    <div class="flex-1 flex flex-col items-center gap-1">
                                        <div class="w-full flex items-end gap-0.5 h-20">
                                            <div class="flex-1 bg-brand rounded-t-sm h-[70%]"></div>
                                            <div class="flex-1 bg-slate-600/60 rounded-t-sm h-[35%]"></div>
                                        </div>
                                        <span class="text-slate-500 text-[0.55rem]">مايو</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Recent Transactions --}}
                            <div class="col-span-2 bg-[#1A2332] rounded-xl p-4 flex flex-col min-h-0">
                                <div class="flex items-center justify-between mb-3 flex-shrink-0">
                                    <span class="text-white text-xs font-semibold">آخر المعاملات</span>
                                    <span class="text-brand text-[0.6rem] cursor-pointer">الكل</span>
                                </div>
                                <div class="space-y-2.5 flex-1 overflow-hidden">
                                    {{-- Txn 1 --}}
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-lg bg-emerald-500/10 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                            </div>
                                            <div>
                                                <div class="text-slate-200 text-[0.65rem] font-medium">أحمد السالم</div>
                                                <div class="text-slate-500 text-[0.55rem]">مشروع تصميم</div>
                                            </div>
                                        </div>
                                        <span class="text-emerald-400 text-[0.65rem] font-bold">+٣,٥٠٠</span>
                                    </div>
                                    {{-- Txn 2 --}}
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-lg bg-red-500/10 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-3 h-3 text-red-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                            </div>
                                            <div>
                                                <div class="text-slate-200 text-[0.65rem] font-medium">فاتورة استضافة</div>
                                                <div class="text-slate-500 text-[0.55rem]">مصروف شهري</div>
                                            </div>
                                        </div>
                                        <span class="text-red-400 text-[0.65rem] font-bold">-٢٠٠</span>
                                    </div>
                                    {{-- Txn 3 --}}
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-lg bg-emerald-500/10 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                            </div>
                                            <div>
                                                <div class="text-slate-200 text-[0.65rem] font-medium">محمد العلي</div>
                                                <div class="text-slate-500 text-[0.55rem]">تطوير موقع</div>
                                            </div>
                                        </div>
                                        <span class="text-emerald-400 text-[0.65rem] font-bold">+٨,٠٠٠</span>
                                    </div>
                                    {{-- Txn 4 --}}
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-lg bg-red-500/10 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-3 h-3 text-red-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                            </div>
                                            <div>
                                                <div class="text-slate-200 text-[0.65rem] font-medium">اشتراك Adobe</div>
                                                <div class="text-slate-500 text-[0.55rem]">برمجيات</div>
                                            </div>
                                        </div>
                                        <span class="text-red-400 text-[0.65rem] font-bold">-٥٥٠</span>
                                    </div>
                                    {{-- Txn 5 --}}
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-lg bg-emerald-500/10 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                            </div>
                                            <div>
                                                <div class="text-slate-200 text-[0.65rem] font-medium">سارة الغامدي</div>
                                                <div class="text-slate-500 text-[0.55rem]">استشارة</div>
                                            </div>
                                        </div>
                                        <span class="text-emerald-400 text-[0.65rem] font-bold">+٢,٢٠٠</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

            {{-- Fade to blend with next section --}}
            <div class="absolute bottom-0 inset-x-0 h-32 bg-gradient-to-t from-[#FAFAF9] to-transparent pointer-events-none"></div>
        </div>

    </div>
</section>


{{-- ═══════════════════════════════════════════
     § 3 — TRUST BAR
════════════════════════════════════════════ --}}
<section class="bg-white border-y border-slate-100 py-10 px-6">
    <div class="max-w-5xl mx-auto">
        <p class="text-center text-[#94A3B8] text-xs font-medium uppercase tracking-widest mb-8">
            أرقام تتحدث عن نفسها
        </p>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-6 md:gap-0 md:divide-x md:divide-x-reverse md:divide-slate-100">
            <div class="text-center md:px-6">
                <div class="text-2xl font-black text-[#0F172A]">+٥,٠٠٠</div>
                <div class="text-[#475569] text-sm mt-1">مستخدم نشط</div>
            </div>
            <div class="text-center md:px-6">
                <div class="text-2xl font-black text-[#0F172A]">+٢٠٠K</div>
                <div class="text-[#475569] text-sm mt-1">فاتورة أُنشئت</div>
            </div>
            <div class="text-center md:px-6">
                <div class="text-2xl font-black text-[#0F172A]">+٥٠M</div>
                <div class="text-[#475569] text-sm mt-1">ريال معالج</div>
            </div>
            <div class="text-center md:px-6">
                <div class="text-2xl font-black text-[#0F172A]">+١٥K</div>
                <div class="text-[#475569] text-sm mt-1">مشروع مكتمل</div>
            </div>
            <div class="text-center md:px-6">
                <div class="text-2xl font-black text-brand">٤.٩ ★</div>
                <div class="text-[#475569] text-sm mt-1">تقييم المستخدمين</div>
            </div>
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════
     § 4 — PAIN POINTS
════════════════════════════════════════════ --}}
<section class="py-24 px-6 bg-[#FAFAF9]">
    <div class="max-w-7xl mx-auto">

        {{-- Section header --}}
        <div class="max-w-2xl mx-auto text-center mb-16">
            <div class="inline-flex items-center gap-2 bg-red-50 text-red-600 text-xs font-semibold px-4 py-1.5 rounded-full mb-5 border border-red-100">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                مشكلات يعانيها المستقلون يومياً
            </div>
            <h2 class="text-3xl md:text-4xl font-black text-[#0F172A] leading-tight mb-4">
                هل تعاني من هذه المشاكل؟
            </h2>
            <p class="text-[#475569] text-lg">
                معظم المستقلين يخسرون جزءاً من دخلهم بسبب الفوضى المالية. دراهم تحل هذه المشاكل بشكل نهائي.
            </p>
        </div>

        {{-- Pain cards grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

            @php
            $pains = [
                [
                    'icon' => '<path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
                    'title' => 'فواتير ضائعة ومتأخرة',
                    'desc'  => 'تُرسل الفاتورة وتنساها، العميل لا يدفع، وأنت لا تعرف من دفع ومن لم يدفع.',
                ],
                [
                    'icon' => '<path d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                    'title' => 'لا تعرف ربحك الفعلي',
                    'desc'  => 'الدخل يبدو كبيراً لكن ما يتبقى في يدك أقل بكثير. لماذا؟ لا أحد يعرف.',
                ],
                [
                    'icon' => '<path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>',
                    'title' => 'فوضى في إدارة العملاء',
                    'desc'  => 'معلومات العملاء موزعة بين واتساب، ميل، ونوتة ورقية. لا نظام ولا ترتيب.',
                ],
                [
                    'icon' => '<path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
                    'title' => 'مصاريف غير محسوبة',
                    'desc'  => 'اشتراكات، أدوات، مصاريف تشغيلية — تُستنزف ببطء وأنت لا تلاحظ حتى تأتي نهاية الشهر.',
                ],
                [
                    'icon' => '<path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                    'title' => 'مشاريع بلا متابعة',
                    'desc'  => 'المشروع يمتد، الوقت يُهدر، والعميل ينتظر. لا أحد يعرف الوضع الفعلي لكل مشروع.',
                ],
                [
                    'icon' => '<path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>',
                    'title' => 'تقارير ضريبية مرهقة',
                    'desc'  => 'وقت تقديم الزكاة والضريبة يتحول إلى كابوس لأن السجلات غير منظمة على مدار السنة.',
                ],
            ];
            @endphp

            @foreach($pains as $pain)
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm
                        hover:border-brand/30 hover:shadow-[0_8px_24px_rgba(55,48,163,.08)]
                        transition-all duration-300 group">
                <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center mb-4
                            group-hover:bg-brand/10 transition-colors duration-300">
                    <svg class="w-5 h-5 text-red-500 group-hover:text-brand transition-colors duration-300"
                         fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        {!! $pain['icon'] !!}
                    </svg>
                </div>
                <h3 class="font-bold text-[#0F172A] text-base mb-2">{{ $pain['title'] }}</h3>
                <p class="text-[#475569] text-sm leading-relaxed">{{ $pain['desc'] }}</p>
            </div>
            @endforeach

        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════
     § 5 — FEATURES
════════════════════════════════════════════ --}}
<section id="features" class="py-24 px-6 bg-white">
    <div class="max-w-7xl mx-auto">

        {{-- Section header --}}
        <div class="max-w-2xl mx-auto text-center mb-16">
            <div class="inline-flex items-center gap-2 bg-brand/10 text-brand text-xs font-semibold px-4 py-1.5 rounded-full mb-5 border border-brand/20">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                كل ما تحتاجه في مكان واحد
            </div>
            <h2 class="text-3xl md:text-4xl font-black text-[#0F172A] leading-tight mb-4">
                منصة متكاملة لكل جانب من <span class="text-brand">أعمالك</span>
            </h2>
            <p class="text-[#475569] text-lg">
                من أول عميل إلى آخر فاتورة — دراهم تغطي كل خطوة في رحلة عملك.
            </p>
        </div>

        {{-- Features grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">

            @php
            $features = [
                [
                    'icon' => '<path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>',
                    'title' => 'إدارة العملاء',
                    'desc'  => 'سجّل بيانات عملائك، تاريخ تعاملاتهم، وقيمتهم المالية من مكان واحد.',
                    'color' => 'blue',
                ],
                [
                    'icon' => '<path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>',
                    'title' => 'متابعة المشاريع',
                    'desc'  => 'اربط كل مشروع بعميله، حدد مراحله، وتابع تقدمه حتى التسليم.',
                    'color' => 'purple',
                ],
                [
                    'icon' => '<path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
                    'title' => 'إنشاء الفواتير',
                    'desc'  => 'فواتير احترافية في ثوانٍ، مع تتبع حالة الدفع لكل فاتورة.',
                    'color' => 'brand',
                ],
                [
                    'icon' => '<path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                    'title' => 'تتبع الإيرادات',
                    'desc'  => 'سجّل كل دخل وإيراداتك مصنّفة بدقة — واعرف من أين يأتي مالك.',
                    'color' => 'emerald',
                ],
                [
                    'icon' => '<path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>',
                    'title' => 'المصروفات والتكاليف',
                    'desc'  => 'سجّل مصروفاتك، صنّفها، واحسب هامش ربحك الفعلي بدقة.',
                    'color' => 'red',
                ],
                [
                    'icon' => '<path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
                    'title' => 'تقارير وتحليلات',
                    'desc'  => 'رؤية واضحة لأداء عملك — تقارير شهرية، فصلية، وسنوية في لحظة.',
                    'color' => 'amber',
                ],
                [
                    'icon' => '<path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>',
                    'title' => 'المعاملات المتكررة',
                    'desc'  => 'أتمتة الإيرادات والمصروفات المتكررة — لا تُدخلها يدوياً كل شهر.',
                    'color' => 'teal',
                ],
                [
                    'icon' => '<path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>',
                    'title' => 'تنبيهات ذكية',
                    'desc'  => 'تذكيرات تلقائية عند استحقاق الدفع، تأخر العميل، أو تجاوز الميزانية.',
                    'color' => 'indigo',
                ],
            ];
            @endphp

            @foreach($features as $f)
            @php
            $colorMap = [
                'brand'   => ['bg' => 'bg-brand/10',   'icon' => 'text-brand',        'hover' => 'group-hover:bg-brand/15'],
                'blue'    => ['bg' => 'bg-blue-50',     'icon' => 'text-blue-500',     'hover' => 'group-hover:bg-blue-100'],
                'purple'  => ['bg' => 'bg-purple-50',   'icon' => 'text-purple-500',   'hover' => 'group-hover:bg-purple-100'],
                'emerald' => ['bg' => 'bg-emerald-50',  'icon' => 'text-emerald-600',  'hover' => 'group-hover:bg-emerald-100'],
                'red'     => ['bg' => 'bg-red-50',      'icon' => 'text-red-500',      'hover' => 'group-hover:bg-red-100'],
                'amber'   => ['bg' => 'bg-amber-50',    'icon' => 'text-amber-600',    'hover' => 'group-hover:bg-amber-100'],
                'teal'    => ['bg' => 'bg-teal-50',     'icon' => 'text-teal-600',     'hover' => 'group-hover:bg-teal-100'],
                'indigo'  => ['bg' => 'bg-indigo-50',   'icon' => 'text-indigo-500',   'hover' => 'group-hover:bg-indigo-100'],
            ];
            $c = $colorMap[$f['color']];
            @endphp
            <div class="bg-[#FAFAF9] rounded-2xl p-6 border border-slate-100
                        hover:bg-white hover:border-slate-200 hover:shadow-[0_8px_24px_rgba(15,23,42,.06)]
                        hover:-translate-y-0.5 transition-all duration-300 group">
                <div class="w-11 h-11 rounded-xl {{ $c['bg'] }} {{ $c['hover'] }} flex items-center justify-center mb-4 transition-colors duration-300">
                    <svg class="w-5 h-5 {{ $c['icon'] }}" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        {!! $f['icon'] !!}
                    </svg>
                </div>
                <h3 class="font-bold text-[#0F172A] text-base mb-2">{{ $f['title'] }}</h3>
                <p class="text-[#475569] text-sm leading-relaxed">{{ $f['desc'] }}</p>
            </div>
            @endforeach

        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════
     § 6 — FOR WHO
════════════════════════════════════════════ --}}
<section id="for-who" class="py-24 px-6 bg-gradient-to-br from-[#1E1B4B] via-[#231F5C] to-[#1A2C4E] relative overflow-hidden">

    {{-- Background decoration --}}
    <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-[#2DCEA8]/40 to-transparent"></div>
    <div class="absolute bottom-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-[#2DCEA8]/30 to-transparent"></div>
    <div class="absolute top-1/2 right-1/2 translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] rounded-full bg-[#2DCEA8]/5 blur-3xl pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto">

        {{-- Section header --}}
        <div class="max-w-2xl mx-auto text-center mb-16">
            <div class="inline-flex items-center gap-2 bg-brand/10 text-brand text-xs font-semibold px-4 py-1.5 rounded-full mb-5 border border-brand/20">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
                مصمم لك تحديداً
            </div>
            <h2 class="text-3xl md:text-4xl font-black text-white leading-tight mb-4">
                لمن صُممت <span class="text-brand">دراهم</span>؟
            </h2>
            <p class="text-slate-400 text-lg">
                سواء كنت مستقلاً يبدأ مشواره أو وكالة راسخة — دراهم تناسبك.
            </p>
        </div>

        {{-- Audience cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">

            @php
            $audiences = [
                [
                    'emoji' => '💻',
                    'title' => 'المستقل الحر',
                    'subtitle' => 'Freelancer',
                    'desc'  => 'مصمم، مطور، كاتب، مصوّر — أي شخص يبيع مهاراته ويريد ضبط ماليته بدون تعقيد.',
                    'points' => ['تتبع دخلك من كل عميل', 'فواتير احترافية بثوانٍ', 'اعرف ربحك الفعلي'],
                ],
                [
                    'emoji' => '🏢',
                    'title' => 'الوكالة الصغيرة',
                    'subtitle' => 'Small Agency',
                    'desc'  => 'فريق صغير يدير عدة عملاء وعشرات المشاريع — كل المعلومات في لوحة واحدة.',
                    'points' => ['إدارة عملاء متعددين', 'تقارير مالية للفريق', 'تتبع أداء كل مشروع'],
                ],
                [
                    'emoji' => '🎯',
                    'title' => 'المستشار',
                    'subtitle' => 'Consultant',
                    'desc'  => 'مستشار أعمال، مالي، أو قانوني — يحتاج إلى نظام واضح لتسعير وقته وتحصيل مستحقاته.',
                    'points' => ['سعّر جلساتك بدقة', 'احسب قيمة وقتك', 'لا فاتورة تضيع بعد الآن'],
                ],
                [
                    'emoji' => '🎨',
                    'title' => 'منشئ المحتوى',
                    'subtitle' => 'Creator',
                    'desc'  => 'يوتيوبر، بودكاستر، مؤثر — يجني من محتواه ويريد فهم تدفق مداخيله من كل مصدر.',
                    'points' => ['نظّم مصادر دخلك', 'تتبع العقود والرعايات', 'اعرف أين يذهب مالك'],
                ],
            ];
            @endphp

            @foreach($audiences as $aud)
            <div class="bg-white/5 rounded-2xl p-6 border border-white/10
                        hover:bg-white/8 hover:border-brand/30 hover:shadow-[0_8px_32px_rgba(55,48,163,.1)]
                        transition-all duration-300 group">
                <div class="text-3xl mb-4">{{ $aud['emoji'] }}</div>
                <div class="mb-4">
                    <h3 class="font-bold text-white text-lg">{{ $aud['title'] }}</h3>
                    <span class="text-brand text-xs font-medium">{{ $aud['subtitle'] }}</span>
                </div>
                <p class="text-slate-400 text-sm leading-relaxed mb-5">{{ $aud['desc'] }}</p>
                <ul class="space-y-2">
                    @foreach($aud['points'] as $point)
                    <li class="flex items-center gap-2 text-slate-300 text-sm">
                        <svg class="w-4 h-4 text-brand flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        {{ $point }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endforeach

        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════
     § 7 — OUTCOMES / TRANSFORMATION
════════════════════════════════════════════ --}}
<section class="py-24 px-6 bg-white">
    <div class="max-w-7xl mx-auto">

        {{-- Section header --}}
        <div class="max-w-2xl mx-auto text-center mb-16">
            <div class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-700 text-xs font-semibold px-4 py-1.5 rounded-full mb-5 border border-emerald-100">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                التحول مع دراهم
            </div>
            <h2 class="text-3xl md:text-4xl font-black text-[#0F172A] leading-tight mb-4">
                من الفوضى إلى <span class="text-brand">الوضوح التام</span>
            </h2>
            <p class="text-[#475569] text-lg">
                دراهم لا تديّر أرقاماً — تمنحك وضوحاً يساعدك على قرارات أفضل.
            </p>
        </div>

        {{-- Outcomes grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 max-w-4xl mx-auto">

            @php
            $outcomes = [
                [
                    'before' => 'أدير فواتيري بالإكسل',
                    'after'  => 'أعرف بالضبط من دفع ومن لم يدفع',
                    'icon'   => '<path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
                ],
                [
                    'before' => 'لا أعرف ربحي الفعلي',
                    'after'  => 'صافي ربحي واضح بعد كل مصروف',
                    'icon'   => '<path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                ],
                [
                    'before' => 'بيانات عملائي مبعثرة',
                    'after'  => 'تاريخ كل عميل أمامي بنقرة واحدة',
                    'icon'   => '<path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>',
                ],
                [
                    'before' => 'أخشى وقت الزكاة والضريبة',
                    'after'  => 'تقاريري جاهزة في أي وقت',
                    'icon'   => '<path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
                ],
            ];
            @endphp

            @foreach($outcomes as $out)
            <div class="bg-[#FAFAF9] rounded-2xl p-6 border border-slate-100 hover:border-brand/20 hover:shadow-sm transition-all duration-300">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-brand/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            {!! $out['icon'] !!}
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="bg-red-50 text-red-500 text-xs font-semibold px-2.5 py-1 rounded-lg border border-red-100">قبل</span>
                            <span class="text-[#475569] text-sm">{{ $out['before'] }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="bg-emerald-50 text-emerald-600 text-xs font-semibold px-2.5 py-1 rounded-lg border border-emerald-100">بعد</span>
                            <span class="text-[#0F172A] text-sm font-semibold">{{ $out['after'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════
     § 8 — TESTIMONIALS
════════════════════════════════════════════ --}}
<section class="py-24 px-6 bg-[#FAFAF9]">
    <div class="max-w-7xl mx-auto">

        {{-- Section header --}}
        <div class="max-w-2xl mx-auto text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-black text-[#0F172A] leading-tight mb-4">
                ماذا يقول <span class="text-brand">مستخدمونا</span>؟
            </h2>
            <p class="text-[#475569] text-lg">
                أكثر من ٥,٠٠٠ محترف يثقون في دراهم لإدارة أعمالهم.
            </p>
        </div>

        {{-- Testimonial cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            @php
            $testimonials = [
                [
                    'name'   => 'سالم المطيري',
                    'role'   => 'مصمم جرافيك مستقل',
                    'avatar' => 'س',
                    'rating' => 5,
                    'text'   => 'كنت أنسى فواتير وأخسر مال كل شهر. مع دراهم أصبحت أعرف بالضبط وضعي المالي كل يوم. أفضل قرار اتخذته لعملي.',
                ],
                [
                    'name'   => 'نورة الشمري',
                    'role'   => 'مستشارة تسويق رقمي',
                    'avatar' => 'ن',
                    'rating' => 5,
                    'text'   => 'المنصة بسيطة جداً ولكنها قوية. أستطيع إنشاء فاتورة وإرسالها للعميل في دقيقتين. التقارير الشهرية وفّرت عليّ ساعات من العمل.',
                ],
                [
                    'name'   => 'خالد العتيبي',
                    'role'   => 'مطور تطبيقات، وكالة Pixelate',
                    'avatar' => 'خ',
                    'rating' => 5,
                    'text'   => 'وكالتنا تدير ١٢ عميلاً بالتوازي. دراهم أعطتنا وضوحاً كاملاً على أداء كل مشروع. الربح الصافي أصبح واضحاً ولأول مرة نعرف أين نستثمر أكثر.',
                ],
            ];
            @endphp

            @foreach($testimonials as $t)
            <div class="bg-white rounded-2xl p-7 border border-slate-100 shadow-sm hover:shadow-md hover:border-brand/20 transition-all duration-300">
                {{-- Stars --}}
                <div class="flex gap-1 mb-5">
                    @for($i = 0; $i < $t['rating']; $i++)
                    <svg class="w-4 h-4 text-brand" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    @endfor
                </div>
                {{-- Quote --}}
                <p class="text-[#0F172A] text-base leading-relaxed mb-6">"{{ $t['text'] }}"</p>
                {{-- Author --}}
                <div class="flex items-center gap-3 pt-5 border-t border-slate-100">
                    <div class="w-10 h-10 rounded-full bg-brand/10 border-2 border-brand/20 flex items-center justify-center flex-shrink-0">
                        <span class="text-brand font-bold text-sm">{{ $t['avatar'] }}</span>
                    </div>
                    <div>
                        <div class="font-bold text-[#0F172A] text-sm">{{ $t['name'] }}</div>
                        <div class="text-[#475569] text-xs mt-0.5">{{ $t['role'] }}</div>
                    </div>
                </div>
            </div>
            @endforeach

        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════
     § 9 — PRICING
════════════════════════════════════════════ --}}
<section id="pricing" class="py-24 px-6 bg-white">
    <div class="max-w-7xl mx-auto">

        {{-- Section header --}}
        <div class="max-w-2xl mx-auto text-center mb-16">
            <div class="inline-flex items-center gap-2 bg-brand/10 text-brand text-xs font-semibold px-4 py-1.5 rounded-full mb-5 border border-brand/20">
                شفافية كاملة في الأسعار
            </div>
            <h2 class="text-3xl md:text-4xl font-black text-[#0F172A] leading-tight mb-4">
                خطة تناسب كل مرحلة
            </h2>
            <p class="text-[#475569] text-lg">
                ابدأ مجاناً وارتقِ مع نموّ عملك. لا رسوم خفية.
            </p>
        </div>

        {{-- Pricing cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto items-start">

            {{-- Free Plan --}}
            <div class="bg-[#FAFAF9] rounded-2xl p-8 border border-slate-200">
                <div class="mb-6">
                    <h3 class="font-bold text-[#0F172A] text-lg mb-1">مجاني</h3>
                    <p class="text-[#475569] text-sm">للبداية والتجربة</p>
                </div>
                <div class="mb-6">
                    <span class="text-4xl font-black text-[#0F172A]">٠</span>
                    <span class="text-[#475569] text-base"> ر.س/شهر</span>
                </div>
                <ul class="space-y-3 mb-8">
                    @php
                    $freeFeatures = ['حتى ٥٠ معاملة/شهر','٣ عملاء','٥ مشاريع','الفواتير الأساسية','التقارير البسيطة'];
                    @endphp
                    @foreach($freeFeatures as $feat)
                    <li class="flex items-center gap-2.5 text-[#475569] text-sm">
                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        {{ $feat }}
                    </li>
                    @endforeach
                </ul>
                <a href="{{ route('register') }}"
                   class="block text-center border-2 border-slate-200 text-[#475569] font-semibold py-3 rounded-xl
                          no-underline hover:border-brand/50 hover:text-brand transition-all duration-200">
                    ابدأ مجاناً
                </a>
            </div>

            {{-- Pro Plan — Featured --}}
            <div class="relative bg-[#1E1B4B] rounded-2xl p-8 border-2 border-brand
                        shadow-[0_20px_60px_rgba(55,48,163,.2)] -mt-2">
                {{-- Badge --}}
                <div class="absolute -top-3.5 right-1/2 translate-x-1/2 bg-brand text-white text-xs font-bold px-4 py-1.5 rounded-full whitespace-nowrap shadow-md">
                    الأكثر شيوعاً ⭐
                </div>
                <div class="mb-6">
                    <h3 class="font-bold text-white text-lg mb-1">Pro</h3>
                    <p class="text-slate-400 text-sm">للمحترفين الجادين</p>
                </div>
                <div class="mb-6">
                    <span class="text-4xl font-black text-white">٩٩</span>
                    <span class="text-slate-400 text-base"> ر.س/شهر</span>
                    <div class="text-brand text-xs mt-1 font-medium">وفّر ٢٠٪ مع الاشتراك السنوي</div>
                </div>
                <ul class="space-y-3 mb-8">
                    @php
                    $proFeatures = ['معاملات غير محدودة','عملاء غير محدودين','مشاريع غير محدودة','فواتير احترافية PDF','تقارير متقدمة','تنبيهات ذكية','معاملات متكررة','دعم أولوية'];
                    @endphp
                    @foreach($proFeatures as $feat)
                    <li class="flex items-center gap-2.5 text-slate-200 text-sm">
                        <svg class="w-4 h-4 text-brand flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        {{ $feat }}
                    </li>
                    @endforeach
                </ul>
                <a href="{{ route('register') }}"
                   class="block text-center bg-brand text-white font-bold py-3.5 rounded-xl no-underline
                          hover:bg-brand/90 hover:-translate-y-px transition-all duration-200
                          shadow-[0_4px_14px_rgba(55,48,163,.4)]">
                    ابدأ تجربة ١٤ يوم مجاناً
                </a>
            </div>

            {{-- Business Plan --}}
            <div class="bg-[#FAFAF9] rounded-2xl p-8 border border-slate-200">
                <div class="mb-6">
                    <h3 class="font-bold text-[#0F172A] text-lg mb-1">Business</h3>
                    <p class="text-[#475569] text-sm">للفرق والوكالات</p>
                </div>
                <div class="mb-6">
                    <span class="text-4xl font-black text-[#0F172A]">٢٩٩</span>
                    <span class="text-[#475569] text-base"> ر.س/شهر</span>
                </div>
                <ul class="space-y-3 mb-8">
                    @php
                    $bizFeatures = ['كل مميزات Pro','حتى ١٠ أعضاء فريق','لوحات تحكم متعددة','تقارير مخصصة','تكامل API','مدير حساب مخصص','اتفاقية SLA'];
                    @endphp
                    @foreach($bizFeatures as $feat)
                    <li class="flex items-center gap-2.5 text-[#475569] text-sm">
                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        {{ $feat }}
                    </li>
                    @endforeach
                </ul>
                <a href="{{ route('register') }}"
                   class="block text-center border-2 border-slate-200 text-[#475569] font-semibold py-3 rounded-xl
                          no-underline hover:border-brand/50 hover:text-brand transition-all duration-200">
                    تواصل معنا
                </a>
            </div>

        </div>

        {{-- Money back guarantee --}}
        <p class="text-center text-[#94A3B8] text-sm mt-10">
            <svg class="w-4 h-4 inline-block text-emerald-500 ml-1.5 -mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            ضمان استرداد كامل خلال ١٤ يوماً · إلغاء في أي وقت · بدون التزامات
        </p>

    </div>
</section>


{{-- ═══════════════════════════════════════════
     § 10 — FINAL CTA
════════════════════════════════════════════ --}}
<section class="py-28 px-6 bg-gradient-to-br from-[#3730A3] to-[#1E1B4B] relative overflow-hidden">

    {{-- Decorations --}}
    <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-[#2DCEA8]/50 to-transparent"></div>
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(45,206,168,.12)_0%,transparent_60%)] pointer-events-none"></div>
    <div class="absolute top-1/2 -translate-y-1/2 -right-32 w-64 h-64 rounded-full bg-[#2DCEA8]/8 blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/2 -translate-y-1/2 -left-32 w-64 h-64 rounded-full bg-brand/10 blur-3xl pointer-events-none"></div>

    <div class="relative max-w-3xl mx-auto text-center">

        <div class="text-brand text-4xl mb-6">⬡</div>

        <h2 class="text-3xl md:text-5xl font-black text-white leading-tight mb-6">
            جاهز لتحكم كامل<br>
            في <span class="text-brand">مالك وأعمالك</span>؟
        </h2>

        <p class="text-slate-400 text-lg mb-10 max-w-lg mx-auto">
            انضم اليوم وابدأ مجاناً. لا بطاقة ائتمان، لا إعداد معقد — جاهز خلال دقيقة.
        </p>

        <div class="flex items-center justify-center gap-4 flex-wrap">
            @auth
                <a href="{{ route('dashboard') }}"
                   class="bg-brand text-white font-bold text-lg px-12 py-4 rounded-xl no-underline
                          shadow-[0_8px_28px_rgba(55,48,163,.4)]
                          hover:bg-brand/90 hover:-translate-y-0.5 hover:shadow-[0_12px_36px_rgba(55,48,163,.45)]
                          transition-all duration-200">
                    اذهب للوحة التحكم ←
                </a>
            @else
                <a href="{{ route('register') }}"
                   class="bg-brand text-white font-bold text-lg px-12 py-4 rounded-xl no-underline
                          shadow-[0_8px_28px_rgba(55,48,163,.4)]
                          hover:bg-brand/90 hover:-translate-y-0.5 hover:shadow-[0_12px_36px_rgba(55,48,163,.45)]
                          transition-all duration-200">
                    ابدأ مجاناً الآن ←
                </a>
                <a href="{{ route('login') }}"
                   class="border border-white/20 text-white font-semibold text-base px-8 py-4 rounded-xl
                          no-underline hover:border-white/40 hover:bg-white/5 transition-all duration-200">
                    لديّ حساب — دخول
                </a>
            @endauth
        </div>

        <p class="text-slate-600 text-sm mt-8">
            مجاني حتى ٥٠ معاملة شهرياً · ترقية اختيارية في أي وقت
        </p>

    </div>
</section>


{{-- ═══════════════════════════════════════════
     § 11 — FOOTER
════════════════════════════════════════════ --}}
<footer class="bg-[#0F0D2A] text-slate-400 pt-16 pb-8 px-6">
    <div class="max-w-7xl mx-auto">

        {{-- Footer grid --}}
        <div class="grid grid-cols-1 md:grid-cols-5 gap-10 mb-12">

            {{-- Brand column --}}
            <div class="md:col-span-2">
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-brand flex items-center justify-center flex-shrink-0">
                        <span class="text-white font-black text-sm">د</span>
                    </div>
                    <div>
                        <div class="text-white font-black text-lg leading-none">دراهم</div>
                        <div class="text-brand text-[0.65rem] font-medium leading-none mt-0.5">مال وأعمال</div>
                    </div>
                </div>
                <p class="text-slate-500 text-sm leading-relaxed max-w-xs mb-5">
                    منصتك الذكية لإدارة المال والأعمال. نساعدك على تنظيم عملائك، مشاريعك، وإيراداتك من مكان واحد.
                </p>
                <div class="flex gap-3">
                    <a href="#" class="w-9 h-9 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center hover:bg-brand/20 hover:border-brand/30 transition-all duration-200 no-underline">
                        <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                    <a href="#" class="w-9 h-9 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center hover:bg-brand/20 hover:border-brand/30 transition-all duration-200 no-underline">
                        <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                    </a>
                </div>
            </div>

            {{-- Product links --}}
            <div>
                <h4 class="text-white font-semibold text-sm mb-4">المنتج</h4>
                <ul class="space-y-3 list-none m-0 p-0">
                    <li><a href="#features" class="text-slate-500 text-sm no-underline hover:text-brand transition-colors">المميزات</a></li>
                    <li><a href="#pricing" class="text-slate-500 text-sm no-underline hover:text-brand transition-colors">الأسعار</a></li>
                    <li><a href="#for-who" class="text-slate-500 text-sm no-underline hover:text-brand transition-colors">لمن هو؟</a></li>
                    <li><a href="#" class="text-slate-500 text-sm no-underline hover:text-brand transition-colors">آخر التحديثات</a></li>
                </ul>
            </div>

            {{-- Company links --}}
            <div>
                <h4 class="text-white font-semibold text-sm mb-4">الشركة</h4>
                <ul class="space-y-3 list-none m-0 p-0">
                    <li><a href="#" class="text-slate-500 text-sm no-underline hover:text-brand transition-colors">من نحن</a></li>
                    <li><a href="#" class="text-slate-500 text-sm no-underline hover:text-brand transition-colors">المدونة</a></li>
                    <li><a href="#" class="text-slate-500 text-sm no-underline hover:text-brand transition-colors">وظائف</a></li>
                    <li><a href="#" class="text-slate-500 text-sm no-underline hover:text-brand transition-colors">تواصل معنا</a></li>
                </ul>
            </div>

            {{-- Legal --}}
            <div>
                <h4 class="text-white font-semibold text-sm mb-4">قانوني</h4>
                <ul class="space-y-3 list-none m-0 p-0">
                    <li><a href="#" class="text-slate-500 text-sm no-underline hover:text-brand transition-colors">سياسة الخصوصية</a></li>
                    <li><a href="#" class="text-slate-500 text-sm no-underline hover:text-brand transition-colors">شروط الاستخدام</a></li>
                    <li><a href="#" class="text-slate-500 text-sm no-underline hover:text-brand transition-colors">سياسة الاسترداد</a></li>
                </ul>
            </div>

        </div>

        {{-- Footer bottom --}}
        <div class="border-t border-white/[0.06] pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-slate-600 text-sm">
                © ٢٠٢٦ دراهم — جميع الحقوق محفوظة
            </p>
            <div class="flex items-center gap-2 text-slate-600 text-sm">
                <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                مصنوع بعناية في المملكة العربية السعودية 🇸🇦
            </div>
        </div>

    </div>
</footer>


</body>
</html>

<!DOCTYPE html>
<html lang="ar" dir="rtl" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دراهم — مال وأعمال | تحكم كامل في مالك وأعمالك</title>
    <meta name="description"
        content="دراهم — منصتك لإدارة العملاء والمشاريع والفواتير والإيرادات. تحكم كامل في مالك وأعمالك من مكان واحد.">
    <meta property="og:title" content="دراهم — مال وأعمال">
    <meta property="og:description"
        content="منصة مالية ذكية للمستقلين وأصحاب الأعمال. نظّم عملائك، مشاريعك، وإيراداتك من مكان واحد.">
    <meta property="og:type" content="website">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>

<body class="font-alexandria bg-[#FAFAF9] text-[#0F172A] antialiased">


    
    <nav class="sticky top-0 z-50 bg-[#FAFAF9]/90 backdrop-blur-xl border-b border-slate-200/60">
        <div class="max-w-7xl mx-auto px-6 h-[68px] flex items-center justify-between">

            
            <a href="#" class="flex items-center no-underline">
                <img src="<?php echo e(asset('img/logo-darahum.png')); ?>" alt="دراهم — مال وأعمال"
                    class="h-11 w-auto object-contain">
            </a>

            
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

            
            <div class="flex items-center gap-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                    <a href="<?php echo e(route('dashboard')); ?>"
                        class="bg-brand text-white font-semibold text-sm px-5 py-2.5 rounded-xl no-underline
                          hover:bg-brand/90 hover:-translate-y-px transition-all duration-200
                          shadow-[0_4px_14px_rgba(55,48,163,.3)]">
                        لوحة التحكم ←
                    </a>
                <?php else: ?>
                    <a href="<?php echo e(route('login')); ?>"
                        class="hidden md:block text-[#475569] font-medium text-sm px-4 py-2 rounded-lg
                          hover:bg-slate-100 transition-colors no-underline">
                        دخول
                    </a>
                    <a href="<?php echo e(route('register')); ?>"
                        class="bg-brand text-white font-semibold text-sm px-5 py-2.5 rounded-xl no-underline
                          hover:bg-brand/90 hover:-translate-y-px transition-all duration-200
                          shadow-[0_4px_14px_rgba(55,48,163,.3)]">
                        ابدأ مجاناً
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

        </div>
    </nav>


    

    <section class="relative isolate overflow-hidden px-4 pt-10 pb-0 sm:px-6 lg:pt-14">

        
        <div
            class="absolute inset-0 bg-[linear-gradient(180deg,#F8FAFC_0%,#F4F7FF_42%,#FAFAF9_100%)] pointer-events-none">
        </div>
        
        <div
            class="absolute inset-0 bg-[linear-gradient(rgba(55,48,163,.025)_1px,transparent_1px),linear-gradient(90deg,rgba(55,48,163,.025)_1px,transparent_1px)] bg-[size:72px_72px] [mask-image:linear-gradient(to_bottom,black,transparent_78%)] pointer-events-none">
        </div>
        
        <div
            class="absolute -top-24 right-1/2 h-[320px] w-[320px] translate-x-1/2 rounded-full bg-[#3730A3]/10 blur-3xl pointer-events-none">
        </div>
        <div
            class="absolute top-32 left-[-6rem] hidden h-64 w-64 rounded-full bg-[#2DCEA8]/10 blur-3xl pointer-events-none lg:block">
        </div>
        <div
            class="absolute top-32 right-[-6rem] hidden h-48 w-48 rounded-full bg-[#3730A3]/[0.08] blur-3xl pointer-events-none lg:block">
        </div>
        
        <div
            class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-[#2DCEA8]/30 to-transparent pointer-events-none">
        </div>

        <div class="relative mx-auto max-w-7xl text-center">

            
            <div
                class="mb-5 inline-flex max-w-full items-center gap-2 rounded-full border border-[#3730A3]/[0.14] bg-white/85 px-3.5 py-2 text-[0.72rem] font-semibold text-[#3730A3] shadow-[0_8px_24px_rgba(15,23,42,.06)] backdrop-blur-xl sm:px-4">
                <span class="relative flex h-2 w-2 flex-shrink-0">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-[#2DCEA8]/50"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-[#2DCEA8]"></span>
                </span>
                <span class="truncate">✨ مجاني حتى 50 معاملة في الشهر · لا بطاقة ائتمان مطلوبة</span>
            </div>

            
            <h1
                class="mx-auto mb-4 max-w-4xl text-4xl font-black leading-[1.12] text-[#0F172A] sm:text-5xl md:text-[3.4rem] lg:text-[2rem]">
                منصتك لإدارة
                <span class="relative inline-block text-[#3730A3]">
                    المال والعمل
                    <span class="absolute -bottom-1 right-0 h-1 w-full rounded-full bg-[#2DCEA8]/40"></span>
                </span>
                من مكان واحد
            </h1>

            
            <p class="mx-auto mb-7 max-w-2xl text-base leading-8 text-[#475569] sm:text-lg">
                دراهم تساعد المستقلين وأصحاب الخدمات على تنظيم العملاء، المشاريع، الفواتير، والإيرادات بسهولة ووضوح.
                تابع أعمالك، اعرف أرباحك، ونظّم كل تفاصيل شغلك من لوحة تحكم واحدة — بدون تعقيد.

            </p>

            
            <div class="mb-4 flex flex-col items-stretch justify-center gap-3 sm:flex-row sm:items-center">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                    <a href="<?php echo e(route('dashboard')); ?>"
                        class="inline-flex items-center justify-center rounded-xl bg-[#3730A3] px-8 py-3.5 text-base font-bold text-white no-underline
                          shadow-[0_18px_45px_rgba(55,48,163,.28),inset_0_1px_0_rgba(255,255,255,.18)]
                          transition-all duration-200 hover:-translate-y-0.5 hover:bg-[#3730A3]/95 hover:shadow-[0_22px_55px_rgba(55,48,163,.34)]">
                        اذهب للوحة التحكم ←
                    </a>
                <?php else: ?>
                    <a href="<?php echo e(route('register')); ?>"
                        class="inline-flex items-center justify-center rounded-xl bg-[#3730A3] px-8 py-3.5 text-base font-bold text-white no-underline
                          shadow-[0_18px_45px_rgba(55,48,163,.28),inset_0_1px_0_rgba(255,255,255,.18)]
                          transition-all duration-200 hover:-translate-y-0.5 hover:bg-[#3730A3]/95 hover:shadow-[0_22px_55px_rgba(55,48,163,.34)]">
                        ابدأ مجاناً الآن ←
                    </a>
                    <a href="#features"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200/80 bg-white/85 px-7 py-3.5 text-base font-bold text-[#334155] no-underline
                          shadow-[0_10px_30px_rgba(15,23,42,.06),inset_0_1px_0_rgba(255,255,255,.85)] backdrop-blur-xl
                          transition-all duration-200 hover:-translate-y-0.5 hover:border-[#3730A3]/25 hover:bg-white hover:text-[#3730A3]">
                        استكشف المميزات
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <p class="mb-8 text-sm text-[#64748B] sm:mb-10">
                انضم إلى <strong class="font-bold text-[#3730A3]">+5000</strong> مستقل وصاحب عمل يثقون في دراهم
            </p>

            
            <div class="relative mx-auto max-w-6xl">

                
                <div
                    class="absolute -inset-x-6 top-10 h-40 rounded-[2rem] bg-[#3730A3]/[0.08] blur-3xl pointer-events-none">
                </div>
                <div
                    class="absolute -inset-x-3 bottom-0 h-28 rounded-[2rem] bg-[#2DCEA8]/[0.08] blur-3xl pointer-events-none">
                </div>

                <div
                    class="relative rounded-[1.35rem] border border-white/70 bg-white/55 p-2 shadow-[0_35px_90px_rgba(15,23,42,.18)] backdrop-blur-xl">
                    <div
                        class="overflow-hidden rounded-2xl border border-[#1E293B]/10 bg-[#07111F] shadow-[inset_0_1px_0_rgba(255,255,255,.08)]">

                        
                        <div
                            class="flex items-center gap-3 border-b border-white/[0.07] bg-[#0B1220] px-3 py-3 sm:px-5">
                            <div class="flex flex-shrink-0 items-center gap-1.5">
                                <span class="h-2.5 w-2.5 rounded-full bg-slate-600"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-[#3730A3]"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-[#2DCEA8]"></span>
                            </div>
                            <div
                                class="mx-1 flex h-7 min-w-0 flex-1 items-center justify-center rounded-lg border border-white/[0.06] bg-white/[0.04] px-3">
                                <span
                                    class="truncate text-[0.68rem] font-medium text-slate-400">darahum.com/dashboard</span>
                            </div>
                            <div class="hidden flex-shrink-0 items-center gap-2 sm:flex">
                                <span class="h-7 w-7 rounded-lg border border-white/[0.06] bg-white/[0.04]"></span>
                                <span class="h-7 w-7 rounded-lg border border-white/[0.06] bg-white/[0.04]"></span>
                            </div>
                        </div>

                        
                        <div class="flex h-[440px] sm:h-[480px] lg:h-[470px]">

                            
                            <aside
                                class="hidden w-56 flex-shrink-0 border-l border-white/[0.07] bg-[#0A1424] p-4 md:flex md:flex-col">
                                <div class="mb-6 flex items-center gap-2 px-1 py-1">
                                    <div
                                        class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-[#2DCEA8] to-[#3730A3] shadow-[0_10px_22px_rgba(45,206,168,.18)]">
                                        <span class="text-sm font-black text-white">د</span>
                                    </div>
                                    <div class="text-right leading-none">
                                        <div class="text-sm font-black text-white">دراهم</div>
                                        <div class="mt-1 text-[0.58rem] font-medium text-[#2DCEA8]">مال وأعمال</div>
                                    </div>
                                </div>

                                <div class="flex-1 space-y-1">
                                    <div
                                        class="flex items-center gap-2.5 rounded-xl border border-[#2DCEA8]/[0.15] bg-[#3730A3]/[0.22] px-3 py-2.5 text-xs font-semibold text-white shadow-[inset_0_1px_0_rgba(255,255,255,.08)]">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0 text-[#2DCEA8]" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path
                                                d="M2 10a8 8 0 1116 0A8 8 0 012 10zm8-3a1 1 0 100 2 1 1 0 000-2zm-1 4a1 1 0 112 0v2a1 1 0 11-2 0v-2z" />
                                        </svg>
                                        لوحة التحكم
                                    </div>
                                    <div
                                        class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-xs font-medium text-slate-400 transition-colors duration-200 hover:bg-white/[0.04] hover:text-slate-200">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        المشاريع
                                    </div>
                                    <div
                                        class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-xs font-medium text-slate-400 transition-colors duration-200 hover:bg-white/[0.04] hover:text-slate-200">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0" />
                                        </svg>
                                        العملاء
                                    </div>
                                    <div
                                        class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-xs font-medium text-slate-400 transition-colors duration-200 hover:bg-white/[0.04] hover:text-slate-200">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        الفواتير
                                    </div>
                                    <div
                                        class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-xs font-medium text-slate-400 transition-colors duration-200 hover:bg-white/[0.04] hover:text-slate-200">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        المعاملات
                                    </div>
                                    <div
                                        class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-xs font-medium text-slate-400 transition-colors duration-200 hover:bg-white/[0.04] hover:text-slate-200">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path
                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                        التقارير
                                    </div>
                                </div>

                                <div class="mt-4 rounded-xl border border-white/[0.07] bg-white/[0.04] p-3">
                                    <div class="mb-3 flex items-center justify-between">
                                        <span class="text-[0.62rem] font-medium text-slate-400">استخدام الخطة</span>
                                        <span class="text-[0.62rem] font-bold text-[#2DCEA8]">Pro</span>
                                    </div>
                                    <div class="h-1.5 overflow-hidden rounded-full bg-white/[0.07]">
                                        <div class="h-full w-[72%] rounded-full bg-[#2DCEA8]"></div>
                                    </div>
                                </div>

                                <div class="mt-3 flex items-center gap-2 border-t border-white/[0.07] pt-3">
                                    <div
                                        class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full border border-[#2DCEA8]/25 bg-[#2DCEA8]/10">
                                        <span class="text-xs font-bold text-[#2DCEA8]">م</span>
                                    </div>
                                    <div class="min-w-0 text-right">
                                        <div class="truncate text-[0.72rem] font-semibold text-white">محمد العمري</div>
                                        <div class="truncate text-[0.6rem] text-slate-500">خطة Pro</div>
                                    </div>
                                </div>
                            </aside>

                            
                            <div class="min-w-0 flex-1 overflow-hidden bg-[#08111F] p-3 sm:p-4 lg:p-5">
                                <div class="flex h-full min-h-0 flex-col gap-3 lg:gap-4">

                                    
                                    <div class="flex flex-shrink-0 items-start justify-between gap-3">
                                        <div class="min-w-0 text-right">
                                            <h3 class="truncate text-sm font-bold text-white sm:text-base">مرحباً، محمد
                                            </h3>
                                            <p class="mt-1 text-[0.68rem] font-medium text-slate-500">مايو ٢٠٢٦ · الربع
                                                الثاني</p>
                                        </div>
                                        <div class="flex flex-shrink-0 items-center gap-2">
                                            <div
                                                class="hidden rounded-lg border border-white/[0.07] bg-white/[0.04] px-2.5 py-1.5 text-[0.65rem] font-medium text-slate-400 sm:block">
                                                آخر ٣٠ يوم
                                            </div>
                                            <div
                                                class="relative flex h-8 w-8 items-center justify-center rounded-lg border border-white/[0.07] bg-white/[0.04]">
                                                <span
                                                    class="absolute right-1.5 top-1.5 h-1.5 w-1.5 rounded-full bg-[#2DCEA8]"></span>
                                                <svg class="h-3.5 w-3.5 text-slate-300" fill="none"
                                                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path
                                                        d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 11-6 0" />
                                                </svg>
                                            </div>
                                            <div
                                                class="rounded-lg bg-[#3730A3] px-3 py-2 text-[0.68rem] font-bold text-white shadow-[0_10px_26px_rgba(55,48,163,.28)]">
                                                + معاملة
                                            </div>
                                        </div>
                                    </div>

                                    
                                    <div class="grid flex-shrink-0 grid-cols-2 gap-2 sm:grid-cols-4 lg:gap-3">
                                        <div
                                            class="rounded-xl border border-[#2DCEA8]/20 bg-white/[0.065] p-3 shadow-[inset_0_1px_0_rgba(255,255,255,.06)]">
                                            <div class="mb-2 flex items-center justify-between gap-2">
                                                <span
                                                    class="text-[0.62rem] font-semibold text-[#2DCEA8]">الإيرادات</span>
                                                <span
                                                    class="rounded-full bg-[#2DCEA8]/10 px-1.5 py-0.5 text-[0.55rem] font-bold text-[#2DCEA8]">↑
                                                    ١٢٪</span>
                                            </div>
                                            <div class="text-lg font-black text-white">٤٥,٢٠٠</div>
                                            <div class="mt-0.5 text-[0.56rem] text-slate-500">ريال سعودي</div>
                                        </div>
                                        <div
                                            class="rounded-xl border border-white/[0.07] bg-white/[0.045] p-3 shadow-[inset_0_1px_0_rgba(255,255,255,.045)]">
                                            <div class="mb-2 flex items-center justify-between gap-2">
                                                <span
                                                    class="text-[0.62rem] font-semibold text-slate-400">المصروفات</span>
                                                <span
                                                    class="rounded-full bg-white/[0.06] px-1.5 py-0.5 text-[0.55rem] font-bold text-slate-300">↑
                                                    ٣٪</span>
                                            </div>
                                            <div class="text-lg font-black text-white">١٢,٨٠٠</div>
                                            <div class="mt-0.5 text-[0.56rem] text-slate-500">ريال سعودي</div>
                                        </div>
                                        <div
                                            class="rounded-xl border border-white/[0.07] bg-white/[0.045] p-3 shadow-[inset_0_1px_0_rgba(255,255,255,.045)]">
                                            <div class="mb-2 flex items-center justify-between gap-2">
                                                <span class="text-[0.62rem] font-semibold text-slate-400">صافي
                                                    الربح</span>
                                                <span
                                                    class="rounded-full bg-[#2DCEA8]/10 px-1.5 py-0.5 text-[0.55rem] font-bold text-[#2DCEA8]">↑
                                                    ١٨٪</span>
                                            </div>
                                            <div class="text-lg font-black text-white">٣٢,٤٠٠</div>
                                            <div class="mt-0.5 text-[0.56rem] text-slate-500">ريال سعودي</div>
                                        </div>
                                        <div
                                            class="rounded-xl border border-white/[0.07] bg-white/[0.045] p-3 shadow-[inset_0_1px_0_rgba(255,255,255,.045)]">
                                            <div class="mb-2 flex items-center justify-between gap-2">
                                                <span class="text-[0.62rem] font-semibold text-slate-400">غير
                                                    مدفوع</span>
                                                <span
                                                    class="rounded-full bg-[#3730A3]/25 px-1.5 py-0.5 text-[0.55rem] font-bold text-slate-200">٣
                                                    فواتير</span>
                                            </div>
                                            <div class="text-lg font-black text-white">٨,٥٠٠</div>
                                            <div class="mt-0.5 text-[0.56rem] text-slate-500">ريال سعودي</div>
                                        </div>
                                    </div>

                                    
                                    <div class="grid min-h-0 flex-1 grid-cols-1 gap-3 lg:grid-cols-5">

                                        
                                        <div
                                            class="flex min-h-0 flex-col rounded-xl border border-white/[0.07] bg-white/[0.045] p-4 shadow-[inset_0_1px_0_rgba(255,255,255,.045)] lg:col-span-3">
                                            <div class="mb-4 flex flex-shrink-0 items-center justify-between gap-3">
                                                <span class="text-xs font-bold text-white">الإيرادات مقابل
                                                    المصروفات</span>
                                                <div class="hidden items-center gap-3 sm:flex">
                                                    <span
                                                        class="flex items-center gap-1.5 text-[0.6rem] font-medium text-[#2DCEA8]">
                                                        <span
                                                            class="inline-block h-2 w-2 rounded-full bg-[#2DCEA8]"></span>إيرادات
                                                    </span>
                                                    <span
                                                        class="flex items-center gap-1.5 text-[0.6rem] font-medium text-slate-500">
                                                        <span
                                                            class="inline-block h-2 w-2 rounded-full bg-slate-600"></span>مصروفات
                                                    </span>
                                                </div>
                                            </div>
                                            <div
                                                class="relative flex min-h-0 flex-1 items-end justify-between gap-2 border-b border-white/[0.06] pb-3">
                                                <div class="absolute inset-x-0 top-0 h-px bg-white/[0.04]"></div>
                                                <div class="absolute inset-x-0 top-1/3 h-px bg-white/[0.04]"></div>
                                                <div class="absolute inset-x-0 top-2/3 h-px bg-white/[0.04]"></div>
                                                <div class="relative flex flex-1 flex-col items-center gap-2">
                                                    <div class="flex h-28 w-full items-end gap-1">
                                                        <div class="flex-1 rounded-t-md bg-[#2DCEA8]/[0.65] h-[60%]">
                                                        </div>
                                                        <div class="flex-1 rounded-t-md bg-slate-600/[0.55] h-[35%]">
                                                        </div>
                                                    </div>
                                                    <span class="text-[0.55rem] text-slate-500">ديس</span>
                                                </div>
                                                <div class="relative flex flex-1 flex-col items-center gap-2">
                                                    <div class="flex h-28 w-full items-end gap-1">
                                                        <div class="flex-1 rounded-t-md bg-[#2DCEA8]/[0.65] h-[45%]">
                                                        </div>
                                                        <div class="flex-1 rounded-t-md bg-slate-600/[0.55] h-[25%]">
                                                        </div>
                                                    </div>
                                                    <span class="text-[0.55rem] text-slate-500">يناير</span>
                                                </div>
                                                <div class="relative flex flex-1 flex-col items-center gap-2">
                                                    <div class="flex h-28 w-full items-end gap-1">
                                                        <div class="flex-1 rounded-t-md bg-[#2DCEA8]/75 h-[75%]"></div>
                                                        <div class="flex-1 rounded-t-md bg-slate-600/[0.55] h-[40%]">
                                                        </div>
                                                    </div>
                                                    <span class="text-[0.55rem] text-slate-500">فبراير</span>
                                                </div>
                                                <div class="relative flex flex-1 flex-col items-center gap-2">
                                                    <div class="flex h-28 w-full items-end gap-1">
                                                        <div class="flex-1 rounded-t-md bg-[#2DCEA8]/[0.65] h-[50%]">
                                                        </div>
                                                        <div class="flex-1 rounded-t-md bg-slate-600/[0.55] h-[28%]">
                                                        </div>
                                                    </div>
                                                    <span class="text-[0.55rem] text-slate-500">مارس</span>
                                                </div>
                                                <div class="relative flex flex-1 flex-col items-center gap-2">
                                                    <div class="flex h-28 w-full items-end gap-1">
                                                        <div
                                                            class="flex-1 rounded-t-md bg-[#2DCEA8] h-[85%] shadow-[0_0_18px_rgba(45,206,168,.28)]">
                                                        </div>
                                                        <div class="flex-1 rounded-t-md bg-slate-600/[0.55] h-[45%]">
                                                        </div>
                                                    </div>
                                                    <span class="text-[0.55rem] text-slate-500">ابريل</span>
                                                </div>
                                                <div class="relative flex flex-1 flex-col items-center gap-2">
                                                    <div class="flex h-28 w-full items-end gap-1">
                                                        <div
                                                            class="flex-1 rounded-t-md bg-[#3730A3] h-[70%] shadow-[0_0_18px_rgba(55,48,163,.24)]">
                                                        </div>
                                                        <div class="flex-1 rounded-t-md bg-slate-600/[0.55] h-[35%]">
                                                        </div>
                                                    </div>
                                                    <span class="text-[0.55rem] text-slate-500">مايو</span>
                                                </div>
                                            </div>
                                        </div>

                                        
                                        <div
                                            class="hidden min-h-0 flex-col rounded-xl border border-white/[0.07] bg-white/[0.045] p-4 shadow-[inset_0_1px_0_rgba(255,255,255,.045)] lg:col-span-2 lg:flex">
                                            <div class="mb-3 flex flex-shrink-0 items-center justify-between">
                                                <span class="text-xs font-bold text-white">آخر المعاملات</span>
                                                <span class="text-[0.6rem] font-bold text-[#2DCEA8]">الكل</span>
                                            </div>
                                            <div class="min-h-0 flex-1 space-y-2 overflow-hidden">
                                                <div
                                                    class="flex items-center justify-between rounded-lg border border-white/[0.05] bg-white/[0.035] px-2.5 py-2">
                                                    <div class="flex min-w-0 items-center gap-2">
                                                        <div
                                                            class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg bg-[#2DCEA8]/10">
                                                            <svg class="h-3.5 w-3.5 text-[#2DCEA8]" fill="none"
                                                                stroke="currentColor" stroke-width="2.5"
                                                                viewBox="0 0 24 24">
                                                                <path d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                                            </svg>
                                                        </div>
                                                        <div class="min-w-0 text-right">
                                                            <div
                                                                class="truncate text-[0.68rem] font-semibold text-slate-200">
                                                                أحمد السالم</div>
                                                            <div class="truncate text-[0.56rem] text-slate-500">مشروع
                                                                تصميم</div>
                                                        </div>
                                                    </div>
                                                    <span
                                                        class="text-[0.68rem] font-black text-[#2DCEA8]">+٣,٥٠٠</span>
                                                </div>
                                                <div class="flex items-center justify-between rounded-lg px-2.5 py-2">
                                                    <div class="flex min-w-0 items-center gap-2">
                                                        <div
                                                            class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg bg-white/[0.06]">
                                                            <svg class="h-3.5 w-3.5 text-slate-400" fill="none"
                                                                stroke="currentColor" stroke-width="2.5"
                                                                viewBox="0 0 24 24">
                                                                <path d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                                            </svg>
                                                        </div>
                                                        <div class="min-w-0 text-right">
                                                            <div
                                                                class="truncate text-[0.68rem] font-semibold text-slate-200">
                                                                فاتورة استضافة</div>
                                                            <div class="truncate text-[0.56rem] text-slate-500">مصروف
                                                                شهري</div>
                                                        </div>
                                                    </div>
                                                    <span class="text-[0.68rem] font-bold text-slate-400">-٢٠٠</span>
                                                </div>
                                                <div class="flex items-center justify-between rounded-lg px-2.5 py-2">
                                                    <div class="flex min-w-0 items-center gap-2">
                                                        <div
                                                            class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg bg-[#2DCEA8]/10">
                                                            <svg class="h-3.5 w-3.5 text-[#2DCEA8]" fill="none"
                                                                stroke="currentColor" stroke-width="2.5"
                                                                viewBox="0 0 24 24">
                                                                <path d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                                            </svg>
                                                        </div>
                                                        <div class="min-w-0 text-right">
                                                            <div
                                                                class="truncate text-[0.68rem] font-semibold text-slate-200">
                                                                محمد العلي</div>
                                                            <div class="truncate text-[0.56rem] text-slate-500">تطوير
                                                                موقع</div>
                                                        </div>
                                                    </div>
                                                    <span
                                                        class="text-[0.68rem] font-black text-[#2DCEA8]">+٨,٠٠٠</span>
                                                </div>
                                                <div class="flex items-center justify-between rounded-lg px-2.5 py-2">
                                                    <div class="flex min-w-0 items-center gap-2">
                                                        <div
                                                            class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg bg-white/[0.06]">
                                                            <svg class="h-3.5 w-3.5 text-slate-400" fill="none"
                                                                stroke="currentColor" stroke-width="2.5"
                                                                viewBox="0 0 24 24">
                                                                <path d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                                            </svg>
                                                        </div>
                                                        <div class="min-w-0 text-right">
                                                            <div
                                                                class="truncate text-[0.68rem] font-semibold text-slate-200">
                                                                اشتراك Adobe</div>
                                                            <div class="truncate text-[0.56rem] text-slate-500">برمجيات
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span class="text-[0.68rem] font-bold text-slate-400">-٥٥٠</span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    
                                    <div class="hidden flex-shrink-0 grid-cols-3 gap-3 lg:grid">
                                        <div
                                            class="rounded-xl border border-white/[0.07] bg-white/[0.035] px-3 py-2 text-right">
                                            <div class="mb-1 flex items-center gap-2">
                                                <span class="h-1.5 w-1.5 rounded-full bg-[#2DCEA8]"></span>
                                                <span class="text-[0.6rem] font-bold text-slate-300">تنبيه جديد</span>
                                            </div>
                                            <p class="truncate text-[0.58rem] text-slate-500">فاتورة مستحقة خلال ٣ أيام
                                            </p>
                                        </div>
                                        <div
                                            class="rounded-xl border border-white/[0.07] bg-white/[0.035] px-3 py-2 text-right">
                                            <div class="mb-1 flex items-center gap-2">
                                                <span class="h-1.5 w-1.5 rounded-full bg-[#3730A3]"></span>
                                                <span class="text-[0.6rem] font-bold text-slate-300">نشاط اليوم</span>
                                            </div>
                                            <p class="truncate text-[0.58rem] text-slate-500">٥ معاملات تمت مزامنتها
                                            </p>
                                        </div>
                                        <div
                                            class="rounded-xl border border-white/[0.07] bg-white/[0.035] px-3 py-2 text-right">
                                            <div class="mb-1 flex items-center gap-2">
                                                <span class="h-1.5 w-1.5 rounded-full bg-[#2DCEA8]"></span>
                                                <span class="text-[0.6rem] font-bold text-slate-300">تدفق نقدي</span>
                                            </div>
                                            <p class="truncate text-[0.58rem] text-slate-500">النمو أعلى من الشهر
                                                السابق</p>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                
                <div
                    class="absolute bottom-0 inset-x-0 h-24 bg-gradient-to-t from-[#FAFAF9] to-transparent pointer-events-none">
                </div>
            </div>

        </div>
    </section>


    
    <section class="bg-white border-y border-slate-100 py-10 px-6">
        <div class="max-w-5xl mx-auto">
            <p class="text-center text-[#94A3B8] text-xs font-medium uppercase tracking-widest mb-8">
                أرقام تتحدث عن نفسها
            </p>
            <div
                class="grid grid-cols-2 md:grid-cols-5 gap-6 md:gap-0 md:divide-x md:divide-x-reverse md:divide-slate-100">
                <div class="text-center md:px-6">
                    <div class="text-2xl font-black text-[#0F172A]">+5,000</div>
                    <div class="text-[#475569] text-sm mt-1">مستخدم نشط</div>
                </div>
                <div class="text-center md:px-6">
                    <div class="text-2xl font-black text-[#0F172A]">+200K</div>
                    <div class="text-[#475569] text-sm mt-1">فاتورة أُنشئت</div>
                </div>
                <div class="text-center md:px-6">
                    <div class="text-2xl font-black text-[#0F172A]">+50M</div>
                    <div class="text-[#475569] text-sm mt-1">ريال معالج</div>
                </div>
                <div class="text-center md:px-6">
                    <div class="text-2xl font-black text-[#0F172A]">+15K</div>
                    <div class="text-[#475569] text-sm mt-1">مشروع مكتمل</div>
                </div>
                <div class="text-center md:px-6">
                    <div class="text-2xl font-black text-brand">4.9 ★</div>
                    <div class="text-[#475569] text-sm mt-1">تقييم المستخدمين</div>
                </div>
            </div>
        </div>
    </section>


    
    <section class="py-24 px-6 bg-[#FAFAF9]">
        <div class="max-w-7xl mx-auto">

            
            <div class="max-w-2xl mx-auto text-center mb-16">
                <div
                    class="inline-flex items-center gap-2 bg-red-50 text-red-600 text-xs font-semibold px-4 py-1.5 rounded-full mb-5 border border-red-100">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                    مشكلات يعانيها المستقلون يومياً
                </div>
                <h2 class="text-3xl md:text-4xl font-black text-[#0F172A] leading-tight mb-4">
                    هل تعاني من هذه المشاكل؟
                </h2>
                <p class="text-[#475569] text-lg">
                    معظم المستقلين يخسرون جزءاً من دخلهم بسبب الفوضى المالية. دراهم تحل هذه المشاكل بشكل نهائي.
                </p>
            </div>

            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

                <?php
                    $pains = [
                        [
                            'icon' =>
                                '<path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
                            'title' => 'فواتير ضائعة ومتأخرة',
                            'desc' => 'تُرسل الفاتورة وتنساها، العميل لا يدفع، وأنت لا تعرف من دفع ومن لم يدفع.',
                        ],
                        [
                            'icon' =>
                                '<path d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                            'title' => 'لا تعرف ربحك الفعلي',
                            'desc' => 'الدخل يبدو كبيراً لكن ما يتبقى في يدك أقل بكثير. لماذا؟ لا أحد يعرف.',
                        ],
                        [
                            'icon' =>
                                '<path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>',
                            'title' => 'فوضى في إدارة العملاء',
                            'desc' => 'معلومات العملاء موزعة بين واتساب، ميل، ونوتة ورقية. لا نظام ولا ترتيب.',
                        ],
                        [
                            'icon' =>
                                '<path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
                            'title' => 'مصاريف غير محسوبة',
                            'desc' =>
                                'اشتراكات، أدوات، مصاريف تشغيلية — تُستنزف ببطء وأنت لا تلاحظ حتى تأتي نهاية الشهر.',
                        ],
                        [
                            'icon' => '<path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                            'title' => 'مشاريع بلا متابعة',
                            'desc' => 'المشروع يمتد، الوقت يُهدر، والعميل ينتظر. لا أحد يعرف الوضع الفعلي لكل مشروع.',
                        ],
                        [
                            'icon' =>
                                '<path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>',
                            'title' => 'تقارير ضريبية مرهقة',
                            'desc' => 'وقت تقديم الزكاة والضريبة يتحول إلى كابوس لأن السجلات غير منظمة على مدار السنة.',
                        ],
                    ];
                ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $pains; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pain): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div
                        class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm
                        hover:border-brand/30 hover:shadow-[0_8px_24px_rgba(55,48,163,.08)]
                        transition-all duration-300 group">
                        <div
                            class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center mb-4
                            group-hover:bg-brand/10 transition-colors duration-300">
                            <svg class="w-5 h-5 text-red-500 group-hover:text-brand transition-colors duration-300"
                                fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <?php echo $pain['icon']; ?>

                            </svg>
                        </div>
                        <h3 class="font-bold text-[#0F172A] text-base mb-2"><?php echo e($pain['title']); ?></h3>
                        <p class="text-[#475569] text-sm leading-relaxed"><?php echo e($pain['desc']); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            </div>
        </div>
    </section>


    
    <section id="features" class="py-24 px-6 bg-white">
        <div class="max-w-7xl mx-auto">

            
            <div class="max-w-2xl mx-auto text-center mb-16">
                <div
                    class="inline-flex items-center gap-2 bg-brand/10 text-brand text-xs font-semibold px-4 py-1.5 rounded-full mb-5 border border-brand/20">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"
                            clip-rule="evenodd" />
                    </svg>
                    كل ما تحتاجه في مكان واحد
                </div>
                <h2 class="text-3xl md:text-4xl font-black text-[#0F172A] leading-tight mb-4">
                    منصة متكاملة لكل جانب من <span class="text-brand">أعمالك</span>
                </h2>
                <p class="text-[#475569] text-lg">
                    من أول عميل إلى آخر فاتورة — دراهم تغطي كل خطوة في رحلة عملك.
                </p>
            </div>

            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">

                <?php
                    $features = [
                        [
                            'icon' =>
                                '<path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>',
                            'title' => 'إدارة العملاء',
                            'desc' => 'سجّل بيانات عملائك، تاريخ تعاملاتهم، وقيمتهم المالية من مكان واحد.',
                            'color' => 'blue',
                        ],
                        [
                            'icon' =>
                                '<path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>',
                            'title' => 'متابعة المشاريع',
                            'desc' => 'اربط كل مشروع بعميله، حدد مراحله، وتابع تقدمه حتى التسليم.',
                            'color' => 'purple',
                        ],
                        [
                            'icon' =>
                                '<path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
                            'title' => 'إنشاء الفواتير',
                            'desc' => 'فواتير احترافية في ثوانٍ، مع تتبع حالة الدفع لكل فاتورة.',
                            'color' => 'brand',
                        ],
                        [
                            'icon' =>
                                '<path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                            'title' => 'تتبع الإيرادات',
                            'desc' => 'سجّل كل دخل وإيراداتك مصنّفة بدقة — واعرف من أين يأتي مالك.',
                            'color' => 'emerald',
                        ],
                        [
                            'icon' =>
                                '<path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>',
                            'title' => 'المصروفات والتكاليف',
                            'desc' => 'سجّل مصروفاتك، صنّفها، واحسب هامش ربحك الفعلي بدقة.',
                            'color' => 'red',
                        ],
                        [
                            'icon' =>
                                '<path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
                            'title' => 'تقارير وتحليلات',
                            'desc' => 'رؤية واضحة لأداء عملك — تقارير شهرية، فصلية، وسنوية في لحظة.',
                            'color' => 'amber',
                        ],
                        [
                            'icon' =>
                                '<path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>',
                            'title' => 'المعاملات المتكررة',
                            'desc' => 'أتمتة الإيرادات والمصروفات المتكررة — لا تُدخلها يدوياً كل شهر.',
                            'color' => 'teal',
                        ],
                        [
                            'icon' =>
                                '<path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>',
                            'title' => 'تنبيهات ذكية',
                            'desc' => 'تذكيرات تلقائية عند استحقاق الدفع، تأخر العميل، أو تجاوز الميزانية.',
                            'color' => 'indigo',
                        ],
                    ];
                ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $colorMap = [
                            'brand' => [
                                'bg' => 'bg-brand/10',
                                'icon' => 'text-brand',
                                'hover' => 'group-hover:bg-brand/15',
                            ],
                            'blue' => [
                                'bg' => 'bg-blue-50',
                                'icon' => 'text-blue-500',
                                'hover' => 'group-hover:bg-blue-100',
                            ],
                            'purple' => [
                                'bg' => 'bg-purple-50',
                                'icon' => 'text-purple-500',
                                'hover' => 'group-hover:bg-purple-100',
                            ],
                            'emerald' => [
                                'bg' => 'bg-emerald-50',
                                'icon' => 'text-emerald-600',
                                'hover' => 'group-hover:bg-emerald-100',
                            ],
                            'red' => [
                                'bg' => 'bg-red-50',
                                'icon' => 'text-red-500',
                                'hover' => 'group-hover:bg-red-100',
                            ],
                            'amber' => [
                                'bg' => 'bg-amber-50',
                                'icon' => 'text-amber-600',
                                'hover' => 'group-hover:bg-amber-100',
                            ],
                            'teal' => [
                                'bg' => 'bg-teal-50',
                                'icon' => 'text-teal-600',
                                'hover' => 'group-hover:bg-teal-100',
                            ],
                            'indigo' => [
                                'bg' => 'bg-indigo-50',
                                'icon' => 'text-indigo-500',
                                'hover' => 'group-hover:bg-indigo-100',
                            ],
                        ];
                        $c = $colorMap[$f['color']];
                    ?>
                    <div
                        class="bg-[#FAFAF9] rounded-2xl p-6 border border-slate-100
                        hover:bg-white hover:border-slate-200 hover:shadow-[0_8px_24px_rgba(15,23,42,.06)]
                        hover:-translate-y-0.5 transition-all duration-300 group">
                        <div
                            class="w-11 h-11 rounded-xl <?php echo e($c['bg']); ?> <?php echo e($c['hover']); ?> flex items-center justify-center mb-4 transition-colors duration-300">
                            <svg class="w-5 h-5 <?php echo e($c['icon']); ?>" fill="none" stroke="currentColor"
                                stroke-width="1.75" viewBox="0 0 24 24">
                                <?php echo $f['icon']; ?>

                            </svg>
                        </div>
                        <h3 class="font-bold text-[#0F172A] text-base mb-2"><?php echo e($f['title']); ?></h3>
                        <p class="text-[#475569] text-sm leading-relaxed"><?php echo e($f['desc']); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            </div>
        </div>
    </section>


    
    <section id="for-who"
        class="py-24 px-6 bg-gradient-to-br from-[#1E1B4B] via-[#231F5C] to-[#1A2C4E] relative overflow-hidden">

        
        <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-[#2DCEA8]/40 to-transparent">
        </div>
        <div
            class="absolute bottom-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-[#2DCEA8]/30 to-transparent">
        </div>
        <div
            class="absolute top-1/2 right-1/2 translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] rounded-full bg-[#2DCEA8]/5 blur-3xl pointer-events-none">
        </div>

        <div class="relative max-w-7xl mx-auto">

            
            <div class="max-w-2xl mx-auto text-center mb-16">
                <div
                    class="inline-flex items-center gap-2 bg-brand/10 text-brand text-xs font-semibold px-4 py-1.5 rounded-full mb-5 border border-brand/20">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                    </svg>
                    مصمم لك تحديداً
                </div>
                <h2 class="text-3xl md:text-4xl font-black text-white leading-tight mb-4">
                    لمن صُممت <span class="text-brand">دراهم</span>؟
                </h2>
                <p class="text-slate-400 text-lg">
                    سواء كنت مستقلاً يبدأ مشواره أو وكالة راسخة — دراهم تناسبك.
                </p>
            </div>

            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">

                <?php
                    $audiences = [
                        [
                            'emoji' => '💻',
                            'title' => 'المستقل الحر',
                            'subtitle' => 'Freelancer',
                            'desc' => 'مصمم، مطور، كاتب، مصوّر — أي شخص يبيع مهاراته ويريد ضبط ماليته بدون تعقيد.',
                            'points' => ['تتبع دخلك من كل عميل', 'فواتير احترافية بثوانٍ', 'اعرف ربحك الفعلي'],
                        ],
                        [
                            'emoji' => '🏢',
                            'title' => 'الوكالة الصغيرة',
                            'subtitle' => 'Small Agency',
                            'desc' => 'فريق صغير يدير عدة عملاء وعشرات المشاريع — كل المعلومات في لوحة واحدة.',
                            'points' => ['إدارة عملاء متعددين', 'تقارير مالية للفريق', 'تتبع أداء كل مشروع'],
                        ],
                        [
                            'emoji' => '🎯',
                            'title' => 'المستشار',
                            'subtitle' => 'Consultant',
                            'desc' =>
                                'مستشار أعمال، مالي، أو قانوني — يحتاج إلى نظام واضح لتسعير وقته وتحصيل مستحقاته.',
                            'points' => ['سعّر جلساتك بدقة', 'احسب قيمة وقتك', 'لا فاتورة تضيع بعد الآن'],
                        ],
                        [
                            'emoji' => '🎨',
                            'title' => 'منشئ المحتوى',
                            'subtitle' => 'Creator',
                            'desc' => 'يوتيوبر، بودكاستر، مؤثر — يجني من محتواه ويريد فهم تدفق مداخيله من كل مصدر.',
                            'points' => ['نظّم مصادر دخلك', 'تتبع العقود والرعايات', 'اعرف أين يذهب مالك'],
                        ],
                    ];
                ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $audiences; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $aud): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div
                        class="bg-white/5 rounded-2xl p-6 border border-white/10
                        hover:bg-white/8 hover:border-brand/30 hover:shadow-[0_8px_32px_rgba(55,48,163,.1)]
                        transition-all duration-300 group">
                        <div class="text-3xl mb-4"><?php echo e($aud['emoji']); ?></div>
                        <div class="mb-4">
                            <h3 class="font-bold text-white text-lg"><?php echo e($aud['title']); ?></h3>
                            <span class="text-brand text-xs font-medium"><?php echo e($aud['subtitle']); ?></span>
                        </div>
                        <p class="text-slate-400 text-sm leading-relaxed mb-5"><?php echo e($aud['desc']); ?></p>
                        <ul class="space-y-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $aud['points']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="flex items-center gap-2 text-slate-300 text-sm">
                                    <svg class="w-4 h-4 text-brand flex-shrink-0" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <?php echo e($point); ?>

                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </ul>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            </div>
        </div>
    </section>


    
    <section class="py-24 px-6 bg-white">
        <div class="max-w-7xl mx-auto">

            
            <div class="max-w-2xl mx-auto text-center mb-16">
                <div
                    class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-700 text-xs font-semibold px-4 py-1.5 rounded-full mb-5 border border-emerald-100">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    التحول مع دراهم
                </div>
                <h2 class="text-3xl md:text-4xl font-black text-[#0F172A] leading-tight mb-4">
                    من الفوضى إلى <span class="text-brand">الوضوح التام</span>
                </h2>
                <p class="text-[#475569] text-lg">
                    دراهم لا تديّر أرقاماً — تمنحك وضوحاً يساعدك على قرارات أفضل.
                </p>
            </div>

            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 max-w-4xl mx-auto">

                <?php
                    $outcomes = [
                        [
                            'before' => 'أدير فواتيري بالإكسل',
                            'after' => 'أعرف بالضبط من دفع ومن لم يدفع',
                            'icon' =>
                                '<path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
                        ],
                        [
                            'before' => 'لا أعرف ربحي الفعلي',
                            'after' => 'صافي ربحي واضح بعد كل مصروف',
                            'icon' =>
                                '<path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                        ],
                        [
                            'before' => 'بيانات عملائي مبعثرة',
                            'after' => 'تاريخ كل عميل أمامي بنقرة واحدة',
                            'icon' =>
                                '<path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>',
                        ],
                        [
                            'before' => 'أخشى وقت الزكاة والضريبة',
                            'after' => 'تقاريري جاهزة في أي وقت',
                            'icon' =>
                                '<path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
                        ],
                    ];
                ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $outcomes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $out): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div
                        class="bg-[#FAFAF9] rounded-2xl p-6 border border-slate-100 hover:border-brand/20 hover:shadow-sm transition-all duration-300">
                        <div class="flex items-start gap-4">
                            <div
                                class="w-10 h-10 rounded-xl bg-brand/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor"
                                    stroke-width="1.75" viewBox="0 0 24 24">
                                    <?php echo $out['icon']; ?>

                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-3">
                                    <span
                                        class="bg-red-50 text-red-500 text-xs font-semibold px-2.5 py-1 rounded-lg border border-red-100">قبل</span>
                                    <span class="text-[#475569] text-sm"><?php echo e($out['before']); ?></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="bg-emerald-50 text-emerald-600 text-xs font-semibold px-2.5 py-1 rounded-lg border border-emerald-100">بعد</span>
                                    <span class="text-[#0F172A] text-sm font-semibold"><?php echo e($out['after']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            </div>
        </div>
    </section>


    
    <section class="py-24 px-6 bg-[#FAFAF9]">
        <div class="max-w-7xl mx-auto">

            
            <div class="max-w-2xl mx-auto text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-black text-[#0F172A] leading-tight mb-4">
                    ماذا يقول <span class="text-brand">مستخدمونا</span>؟
                </h2>
                <p class="text-[#475569] text-lg">
                    أكثر من ٥,٠٠٠ محترف يثقون في دراهم لإدارة أعمالهم.
                </p>
            </div>

            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <?php
                    $testimonials = [
                        [
                            'name' => 'سالم المطيري',
                            'role' => 'مصمم جرافيك مستقل',
                            'avatar' => 'س',
                            'rating' => 5,
                            'text' =>
                                'كنت أنسى فواتير وأخسر مال كل شهر. مع دراهم أصبحت أعرف بالضبط وضعي المالي كل يوم. أفضل قرار اتخذته لعملي.',
                        ],
                        [
                            'name' => 'نورة الشمري',
                            'role' => 'مستشارة تسويق رقمي',
                            'avatar' => 'ن',
                            'rating' => 5,
                            'text' =>
                                'المنصة بسيطة جداً ولكنها قوية. أستطيع إنشاء فاتورة وإرسالها للعميل في دقيقتين. التقارير الشهرية وفّرت عليّ ساعات من العمل.',
                        ],
                        [
                            'name' => 'خالد العتيبي',
                            'role' => 'مطور تطبيقات، وكالة Pixelate',
                            'avatar' => 'خ',
                            'rating' => 5,
                            'text' =>
                                'وكالتنا تدير ١٢ عميلاً بالتوازي. دراهم أعطتنا وضوحاً كاملاً على أداء كل مشروع. الربح الصافي أصبح واضحاً ولأول مرة نعرف أين نستثمر أكثر.',
                        ],
                    ];
                ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $testimonials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div
                        class="bg-white rounded-2xl p-7 border border-slate-100 shadow-sm hover:shadow-md hover:border-brand/20 transition-all duration-300">
                        
                        <div class="flex gap-1 mb-5">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 0; $i < $t['rating']; $i++): ?>
                                <svg class="w-4 h-4 text-brand" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        
                        <p class="text-[#0F172A] text-base leading-relaxed mb-6">"<?php echo e($t['text']); ?>"</p>
                        
                        <div class="flex items-center gap-3 pt-5 border-t border-slate-100">
                            <div
                                class="w-10 h-10 rounded-full bg-brand/10 border-2 border-brand/20 flex items-center justify-center flex-shrink-0">
                                <span class="text-brand font-bold text-sm"><?php echo e($t['avatar']); ?></span>
                            </div>
                            <div>
                                <div class="font-bold text-[#0F172A] text-sm"><?php echo e($t['name']); ?></div>
                                <div class="text-[#475569] text-xs mt-0.5"><?php echo e($t['role']); ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            </div>
        </div>
    </section>


    
    <section id="pricing" class="py-24 px-6 bg-white">
        <div class="max-w-7xl mx-auto">

            
            <div class="max-w-2xl mx-auto text-center mb-16">
                <div
                    class="inline-flex items-center gap-2 bg-brand/10 text-brand text-xs font-semibold px-4 py-1.5 rounded-full mb-5 border border-brand/20">
                    شفافية كاملة في الأسعار
                </div>
                <h2 class="text-3xl md:text-4xl font-black text-[#0F172A] leading-tight mb-4">
                    خطة تناسب كل مرحلة
                </h2>
                <p class="text-[#475569] text-lg">
                    ابدأ مجاناً وارتقِ مع نموّ عملك. لا رسوم خفية.
                </p>
            </div>

            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto items-start">

                
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
                        <?php
                            $freeFeatures = [
                                'حتى ٥٠ معاملة/شهر',
                                '٣ عملاء',
                                '٥ مشاريع',
                                'الفواتير الأساسية',
                                'التقارير البسيطة',
                            ];
                        ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $freeFeatures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex items-center gap-2.5 text-[#475569] text-sm">
                                <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                <?php echo e($feat); ?>

                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </ul>
                    <a href="<?php echo e(route('register')); ?>"
                        class="block text-center border-2 border-slate-200 text-[#475569] font-semibold py-3 rounded-xl
                          no-underline hover:border-brand/50 hover:text-brand transition-all duration-200">
                        ابدأ مجاناً
                    </a>
                </div>

                
                <div
                    class="relative bg-[#1E1B4B] rounded-2xl p-8 border-2 border-brand
                        shadow-[0_20px_60px_rgba(55,48,163,.2)] -mt-2">
                    
                    <div
                        class="absolute -top-3.5 right-1/2 translate-x-1/2 bg-brand text-white text-xs font-bold px-4 py-1.5 rounded-full whitespace-nowrap shadow-md">
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
                        <?php
                            $proFeatures = [
                                'معاملات غير محدودة',
                                'عملاء غير محدودين',
                                'مشاريع غير محدودة',
                                'فواتير احترافية PDF',
                                'تقارير متقدمة',
                                'تنبيهات ذكية',
                                'معاملات متكررة',
                                'دعم أولوية',
                            ];
                        ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $proFeatures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex items-center gap-2.5 text-slate-200 text-sm">
                                <svg class="w-4 h-4 text-brand flex-shrink-0" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                <?php echo e($feat); ?>

                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </ul>
                    <a href="<?php echo e(route('register')); ?>"
                        class="block text-center bg-brand text-white font-bold py-3.5 rounded-xl no-underline
                          hover:bg-brand/90 hover:-translate-y-px transition-all duration-200
                          shadow-[0_4px_14px_rgba(55,48,163,.4)]">
                        ابدأ تجربة ١٤ يوم مجاناً
                    </a>
                </div>

                
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
                        <?php
                            $bizFeatures = [
                                'كل مميزات Pro',
                                'حتى ١٠ أعضاء فريق',
                                'لوحات تحكم متعددة',
                                'تقارير مخصصة',
                                'تكامل API',
                                'مدير حساب مخصص',
                                'اتفاقية SLA',
                            ];
                        ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $bizFeatures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex items-center gap-2.5 text-[#475569] text-sm">
                                <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                <?php echo e($feat); ?>

                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </ul>
                    <a href="<?php echo e(route('register')); ?>"
                        class="block text-center border-2 border-slate-200 text-[#475569] font-semibold py-3 rounded-xl
                          no-underline hover:border-brand/50 hover:text-brand transition-all duration-200">
                        تواصل معنا
                    </a>
                </div>

            </div>

            
            <p class="text-center text-[#94A3B8] text-sm mt-10">
                <svg class="w-4 h-4 inline-block text-emerald-500 ml-1.5 -mt-0.5" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                ضمان استرداد كامل خلال ١٤ يوماً · إلغاء في أي وقت · بدون التزامات
            </p>

        </div>
    </section>


    
    <section class="py-28 px-6 bg-gradient-to-br from-[#3730A3] to-[#1E1B4B] relative overflow-hidden">

        
        <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-[#2DCEA8]/50 to-transparent">
        </div>
        <div
            class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(45,206,168,.12)_0%,transparent_60%)] pointer-events-none">
        </div>
        <div
            class="absolute top-1/2 -translate-y-1/2 -right-32 w-64 h-64 rounded-full bg-[#2DCEA8]/8 blur-3xl pointer-events-none">
        </div>
        <div
            class="absolute top-1/2 -translate-y-1/2 -left-32 w-64 h-64 rounded-full bg-brand/10 blur-3xl pointer-events-none">
        </div>

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
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                    <a href="<?php echo e(route('dashboard')); ?>"
                        class="bg-brand text-white font-bold text-lg px-12 py-4 rounded-xl no-underline
                          shadow-[0_8px_28px_rgba(55,48,163,.4)]
                          hover:bg-brand/90 hover:-translate-y-0.5 hover:shadow-[0_12px_36px_rgba(55,48,163,.45)]
                          transition-all duration-200">
                        اذهب للوحة التحكم ←
                    </a>
                <?php else: ?>
                    <a href="<?php echo e(route('register')); ?>"
                        class="bg-brand text-white font-bold text-lg px-12 py-4 rounded-xl no-underline
                          shadow-[0_8px_28px_rgba(55,48,163,.4)]
                          hover:bg-brand/90 hover:-translate-y-0.5 hover:shadow-[0_12px_36px_rgba(55,48,163,.45)]
                          transition-all duration-200">
                        ابدأ مجاناً الآن ←
                    </a>
                    <a href="<?php echo e(route('login')); ?>"
                        class="border border-white/20 text-white font-semibold text-base px-8 py-4 rounded-xl
                          no-underline hover:border-white/40 hover:bg-white/5 transition-all duration-200">
                        لديّ حساب — دخول
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <p class="text-slate-600 text-sm mt-8">
                مجاني حتى ٥٠ معاملة شهرياً · ترقية اختيارية في أي وقت
            </p>

        </div>
    </section>


    
    <footer class="bg-[#0F0D2A] text-slate-400 pt-16 pb-8 px-6">
        <div class="max-w-7xl mx-auto">

            
            <div class="grid grid-cols-1 md:grid-cols-5 gap-10 mb-12">

                
                <div class="md:col-span-2">
                    <div class="mb-5">
                        <img src="<?php echo e(asset('img/logo-darahum.png')); ?>" alt="دراهم — مال وأعمال"
                            class="h-12 w-auto object-contain brightness-0 invert opacity-90">
                    </div>
                    <p class="text-slate-500 text-sm leading-relaxed max-w-xs mb-5">
                        منصتك الذكية لإدارة المال والأعمال. نساعدك على تنظيم عملائك، مشاريعك، وإيراداتك من مكان واحد.
                    </p>
                    <div class="flex gap-3">
                        <a href="#"
                            class="w-9 h-9 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center hover:bg-brand/20 hover:border-brand/30 transition-all duration-200 no-underline">
                            <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                            </svg>
                        </a>
                        <a href="#"
                            class="w-9 h-9 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center hover:bg-brand/20 hover:border-brand/30 transition-all duration-200 no-underline">
                            <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                            </svg>
                        </a>
                    </div>
                </div>

                
                <div>
                    <h4 class="text-white font-semibold text-sm mb-4">المنتج</h4>
                    <ul class="space-y-3 list-none m-0 p-0">
                        <li><a href="#features"
                                class="text-slate-500 text-sm no-underline hover:text-brand transition-colors">المميزات</a>
                        </li>
                        <li><a href="#pricing"
                                class="text-slate-500 text-sm no-underline hover:text-brand transition-colors">الأسعار</a>
                        </li>
                        <li><a href="#for-who"
                                class="text-slate-500 text-sm no-underline hover:text-brand transition-colors">لمن
                                هو؟</a></li>
                        <li><a href="#"
                                class="text-slate-500 text-sm no-underline hover:text-brand transition-colors">آخر
                                التحديثات</a></li>
                    </ul>
                </div>

                
                <div>
                    <h4 class="text-white font-semibold text-sm mb-4">الشركة</h4>
                    <ul class="space-y-3 list-none m-0 p-0">
                        <li><a href="#"
                                class="text-slate-500 text-sm no-underline hover:text-brand transition-colors">من
                                نحن</a></li>
                        <li><a href="#"
                                class="text-slate-500 text-sm no-underline hover:text-brand transition-colors">المدونة</a>
                        </li>
                        <li><a href="#"
                                class="text-slate-500 text-sm no-underline hover:text-brand transition-colors">وظائف</a>
                        </li>
                        <li><a href="#"
                                class="text-slate-500 text-sm no-underline hover:text-brand transition-colors">تواصل
                                معنا</a></li>
                    </ul>
                </div>

                
                <div>
                    <h4 class="text-white font-semibold text-sm mb-4">قانوني</h4>
                    <ul class="space-y-3 list-none m-0 p-0">
                        <li><a href="#"
                                class="text-slate-500 text-sm no-underline hover:text-brand transition-colors">سياسة
                                الخصوصية</a></li>
                        <li><a href="#"
                                class="text-slate-500 text-sm no-underline hover:text-brand transition-colors">شروط
                                الاستخدام</a></li>
                        <li><a href="#"
                                class="text-slate-500 text-sm no-underline hover:text-brand transition-colors">سياسة
                                الاسترداد</a></li>
                    </ul>
                </div>

            </div>

            
            <div
                class="border-t border-white/[0.06] pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-slate-600 text-sm">
                    © ٢٠٢٦ دراهم — جميع الحقوق محفوظة
                </p>
                <div class="flex items-center gap-2 text-slate-600 text-sm">
                    <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    مصنوع بعناية في المملكة العربية السعودية 🇸🇦
                </div>
            </div>

        </div>
    </footer>


</body>

</html>
<?php /**PATH F:\laragon\www\Workuflow\resources\views/welcome.blade.php ENDPATH**/ ?>
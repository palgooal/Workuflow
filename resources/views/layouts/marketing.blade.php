<!doctype html>
<html lang="ar" dir="rtl" class="scroll-smooth">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'دراهم — المنصة المالية للمستقلين')</title>

    @yield('meta')

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@700&family=Readex+Pro:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('marketing/css/output.css') }}" />

    @yield('head')

</head>

<body class="@yield('body-class', 'bg-white text-g-dark antialiased overflow-x-hidden font-sans')">

    <!-- ════════════════════════════════════════
       NAVBAR
  ════════════════════════════════════════ -->
    <header id="navbar"
        class="fixed top-0 inset-x-0 z-50 bg-[rgba(248,249,253,0.80)] border-b border-g-border shadow-[0px_1px_2px_0px_rgba(0,0,0,0.05)] backdrop-blur-[12px]">
        <div class="max-w-[1280px] mx-auto px-10">
            <div class="flex items-center justify-between h-20 px-6">

                <!-- Logo + Nav -->
                <div class="flex items-center gap-12">
                    <a href="{{ route('home') }}" aria-label="دراهم - الرئيسية">
                        <img src="{{ asset('marketing/imgs/logo.png') }}" alt="دراهم" width="88" height="44"
                            class="w-[88px] h-[44px] object-contain" />
                    </a>

                    <nav class="hidden md:flex items-center gap-8">
                        <a href="{{ route('marketing.features') }}"
                            class="font-medium text-base transition-colors {{ request()->routeIs('marketing.features') ? 'text-g-green' : 'text-g-body hover:text-g-green' }}">المميزات</a>
                        <a href="{{ route('marketing.pricing') }}"
                            class="font-medium text-base transition-colors {{ request()->routeIs('marketing.pricing') ? 'text-g-green' : 'text-g-body hover:text-g-green' }}">الأسعار</a>
                        <a href="{{ route('marketing.faq') }}"
                            class="font-medium text-base transition-colors {{ request()->routeIs('marketing.faq') ? 'text-g-green' : 'text-g-body hover:text-g-green' }}">الأسئلة
                            الشائعة</a>
                        <a href="#"
                            class="font-medium text-base text-g-body hover:text-g-green transition-colors">المدونة</a>
                    </nav>
                </div>

                <!-- CTA Buttons -->
                <div class="hidden md:flex items-center gap-4">
                    @auth
                        @php
                            $plan = auth()->user()->subscription_plan;
                            $planLabel = match (true) {
                                $plan instanceof \App\Support\Enums\SubscriptionPlan => $plan->label(),
                                $plan === 'pro' => 'Pro',
                                $plan === 'business' => 'Business',
                                default => 'مجاني',
                            };
                            $isPro =
                                ($plan instanceof \App\Support\Enums\SubscriptionPlan && $plan->value === 'pro') ||
                                $plan === 'pro';
                            $isBusiness =
                                ($plan instanceof \App\Support\Enums\SubscriptionPlan && $plan->value === 'business') ||
                                $plan === 'business';
                            $planBadgeClass = $isPro
                                ? 'text-violet-700 border-violet-200 bg-violet-50'
                                : ($isBusiness
                                    ? 'text-emerald-700 border-emerald-200 bg-emerald-50'
                                    : 'text-gray-500 border-gray-200 bg-gray-50');
                            $planDotClass = $isPro ? 'bg-violet-600' : ($isBusiness ? 'bg-emerald-500' : 'bg-gray-400');
                        @endphp

                        {{-- Avatar Dropdown --}}
                        <div class="relative" id="user-menu-wrapper">

                            {{-- Trigger button --}}
                            <button id="user-menu-btn"
                                class="group flex items-center gap-2.5 py-2 px-2 bg-slate-50 border border-gray-200 rounded-xl shadow-sm hover:bg-white hover:border-emerald-500 hover:ring-2 hover:ring-emerald-500/10 transition-all"
                                aria-haspopup="true" aria-expanded="false">

                                {{-- Gradient avatar --}}
                                <div
                                    class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-emerald-500 flex items-center justify-center shrink-0 transition-transform group-hover:scale-105">
                                    <span class="text-white text-xs font-bold leading-none select-none">
                                        {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1, 'UTF-8'), 'UTF-8') }}
                                    </span>
                                </div>

                                <span
                                    class="text-sm font-semibold text-g-dark max-w-[110px] truncate">{{ auth()->user()->name }}</span>

                                <svg id="user-menu-chevron"
                                    class="w-3.5 h-3.5 text-g-body shrink-0 transition-transform duration-300"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            {{-- Dropdown panel --}}
                            <div id="user-menu-dropdown" data-state="closed"
                                class="absolute left-0 top-[calc(100%+10px)] w-60 bg-white rounded-[14px] border border-black/[8%] shadow-xl z-50 overflow-hidden
                          transition-all duration-200 ease-out
                          data-[state=closed]:opacity-0 data-[state=closed]:-translate-y-2 data-[state=closed]:scale-95 data-[state=closed]:pointer-events-none
                          data-[state=open]:opacity-100 data-[state=open]:translate-y-0 data-[state=open]:scale-100 data-[state=open]:pointer-events-auto">

                                {{-- Caret --}}
                                <div
                                    class="absolute -top-[6px] left-5 size-3 bg-white -rotate-45 rounded-tl border-t border-r border-black/10">
                                </div>

                                {{-- Header --}}
                                <div class="px-4 pt-4 pb-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-emerald-500 flex items-center justify-center shrink-0">
                                            <span class="text-white text-sm font-bold leading-none select-none">
                                                {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1, 'UTF-8'), 'UTF-8') }}
                                            </span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-g-dark truncate leading-tight">
                                                {{ auth()->user()->name }}</p>
                                            <p class="text-xs text-g-body truncate leading-tight mt-0.5">
                                                {{ auth()->user()->email }}</p>
                                        </div>
                                    </div>
                                    {{-- Plan badge --}}
                                    <div class="mt-3">
                                        <span
                                            class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-full border {{ $planBadgeClass }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $planDotClass }}"></span>
                                            خطة {{ $planLabel }}
                                        </span>
                                    </div>
                                </div>

                                <div class="h-px bg-gray-100 mx-3"></div>

                                {{-- Nav items --}}
                                <div class="py-1.5">
                                    <a href="{{ route('dashboard') }}"
                                        class="group/item flex items-center gap-2.5 px-4 py-2.5 text-sm font-medium text-gray-800 hover:bg-gray-50 transition-colors">
                                        <span
                                            class="w-[30px] h-[30px] rounded-lg flex items-center justify-center shrink-0 bg-emerald-50 text-emerald-500 group-hover/item:bg-emerald-100 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                            </svg>
                                        </span>
                                        <span>لوحة التحكم</span>
                                    </a>
                                    <a href="{{ route('settings.index') }}"
                                        class="group/item flex items-center gap-2.5 px-4 py-2.5 text-sm font-medium text-gray-800 hover:bg-gray-50 transition-colors">
                                        <span
                                            class="w-[30px] h-[30px] rounded-lg flex items-center justify-center shrink-0 bg-gray-100 text-gray-500 group-hover/item:bg-gray-200 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </span>
                                        <span>الإعدادات</span>
                                    </a>
                                </div>

                                <div class="h-px bg-gray-100 mx-3"></div>

                                {{-- Logout --}}
                                <div class="py-1.5">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                            class="group/item flex items-center gap-2.5 w-full px-4 py-2.5 text-sm font-medium text-red-500 hover:bg-red-50 transition-colors">
                                            <span
                                                class="w-[30px] h-[30px] rounded-lg flex items-center justify-center shrink-0 bg-red-50 text-red-400 group-hover/item:bg-red-100 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                                </svg>
                                            </span>
                                            <span>تسجيل الخروج</span>
                                        </button>
                                    </form>
                                </div>

                            </div>{{-- /dropdown --}}
                        </div>
                    @else
                        <a href="{{ route('login') }}"
                            class="font-bold text-base text-g-purple-mid px-4 hover:opacity-75 transition-opacity">تسجيل
                            الدخول</a>
                        <a href="{{ route('register') }}"
                            class="font-bold text-base text-white bg-g-green-lt rounded-[10px] py-[10px] px-6 hover:opacity-90 transition-opacity">ابدأ
                            مجاناً</a>
                    @endauth
                </div>

                <!-- Hamburger -->
                <button id="menu-toggle" class="md:hidden p-2 px-2 rounded-lg hover:bg-g-light2 transition-colors"
                    aria-label="القائمة">
                    <svg id="icon-menu" width="24" height="24" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg id="icon-close" class="hidden" width="24" height="24" fill="none"
                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" d="M6 6l12 12M6 18L18 6" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-g-border">
            <nav class="flex flex-col px-6 py-4 gap-1">
                <a href="{{ route('marketing.features') }}"
                    class="py-3 border-b border-g-border/50 {{ request()->routeIs('marketing.features') ? 'text-g-green font-semibold' : 'text-g-body' }}">المميزات</a>
                <a href="{{ route('marketing.pricing') }}"
                    class="py-3 border-b border-g-border/50 {{ request()->routeIs('marketing.pricing') ? 'text-g-green font-semibold' : 'text-g-body' }}">الأسعار</a>
                <a href="{{ route('marketing.faq') }}"
                    class="py-3 border-b border-g-border/50 {{ request()->routeIs('marketing.faq') ? 'text-g-green font-semibold' : 'text-g-body' }}">الأسئلة
                    الشائعة</a>
                <a href="#" class="py-3 border-b border-g-border/50 text-g-body">المدونة</a>
                @auth
                    {{-- Mobile: user card --}}
                    <div class="mt-3 rounded-2xl overflow-hidden border border-g-border/60">
                        {{-- Card header with gradient --}}
                        <div class="bg-gradient-to-br from-indigo-500 to-emerald-500 px-4 py-3 flex items-center gap-3">
                            <div class="w-11 h-11 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                                <span class="text-white font-bold leading-none select-none">
                                    {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1, 'UTF-8'), 'UTF-8') }}
                                </span>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-white truncate leading-tight">{{ auth()->user()->name }}
                                </p>
                                <p class="text-xs text-white/70 truncate leading-tight mt-0.5">{{ auth()->user()->email }}
                                </p>
                            </div>
                        </div>
                        {{-- Actions --}}
                        <div class="bg-white px-4 py-2 flex flex-col gap-1">
                            <a href="{{ route('dashboard') }}"
                                class="flex items-center gap-3 py-2.5 text-sm font-medium text-g-dark hover:text-g-green transition-colors">
                                <svg class="w-4 h-4 text-g-green" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                                لوحة التحكم
                            </a>
                            <div class="border-t border-g-border/40"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="flex items-center gap-3 w-full py-2.5 text-sm font-medium text-red-500 hover:text-red-700 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    تسجيل الخروج
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="pt-4 pb-2 text-center font-bold text-g-purple-mid">تسجيل
                        الدخول</a>
                    <a href="{{ route('register') }}"
                        class="py-3 rounded-[10px] bg-g-green-lt text-white text-center font-bold">ابدأ مجاناً</a>
                @endauth
            </nav>
        </div>
    </header>

    <!-- Page Content -->
    @yield('content')

    <!-- ════════════════════════════════════════
       FOOTER
  ════════════════════════════════════════ -->
    <footer class="bg-g-light border-t border-g-border py-20 px-5">
        <div class="max-w-[1200px] mx-auto px-6 flex flex-col gap-16">

            <!-- Top: 4 columns -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">

                <!-- Brand -->
                <div class="flex flex-col gap-6 items-start text-start">
                    <img src="{{ asset('marketing/imgs/logo.png') }}" alt="دراهم" width="88" height="44"
                        class="w-[88px] h-[44px] object-contain" />
                    <p class="text-g-body text-sm leading-relaxed max-w-xs">
                        المنصة المالية الأولى المصممة لتمكين المستقلين في العالم العربي. نحن هنا لنمكّن المستقل العربي
                        من التركيز
                        على ما يتقنه.
                    </p>
                </div>

                <!-- المنتج -->
                <div class="flex flex-col gap-6 items-start text-start">
                    <h4 class="font-bold text-base text-black">المنتج</h4>
                    <ul class="flex flex-col gap-4">
                        <li><a href="{{ route('marketing.features') }}"
                                class="text-g-body text-sm hover:text-g-green transition-colors">المميزات</a></li>
                        <li><a href="{{ route('marketing.pricing') }}"
                                class="text-g-body text-sm hover:text-g-green transition-colors">الأسعار</a></li>
                        <li><a href="#"
                                class="text-g-body text-sm hover:text-g-green transition-colors">المدونة</a></li>
                        <li><a href="{{ route('marketing.faq') }}"
                                class="text-g-body text-sm hover:text-g-green transition-colors">الأسئلة الشائعة</a>
                        </li>
                    </ul>
                </div>

                <!-- الشركة -->
                <div class="flex flex-col gap-6 items-start text-start">
                    <h4 class="font-bold text-base text-black">الشركة</h4>
                    <ul class="flex flex-col gap-4">
                        <li><a href="#" class="text-g-body text-sm hover:text-g-green transition-colors">عن
                                دراهم</a></li>
                        <li><a href="#" class="text-g-body text-sm hover:text-g-green transition-colors">سياسة
                                الخصوصية</a></li>
                        <li><a href="#" class="text-g-body text-sm hover:text-g-green transition-colors">شروط
                                الاستخدام</a></li>
                        <li><a href="#"
                                class="text-g-body text-sm hover:text-g-green transition-colors">الوظائف</a></li>
                    </ul>
                </div>

                <!-- تواصل معنا -->
                <div class="flex flex-col gap-6 items-start text-start">
                    <h4 class="font-bold text-base text-black">تواصل معنا</h4>
                    <ul class="flex flex-col gap-4">
                        <li class="flex items-center justify-start gap-2">
                            <svg class="w-4 h-4 text-g-body shrink-0" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                            </svg>
                            <span class="text-g-body text-sm" dir="ltr">support@darahum.com</span>
                        </li>
                        <li class="flex items-center justify-start gap-2">
                            <svg class="w-4 h-4 text-g-body shrink-0" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                            <span class="text-g-body text-sm">الرياض، المملكة العربية السعودية</span>
                        </li>
                    </ul>
                    <div class="flex gap-3">
                        <a href="#"
                            class="w-8 h-8 rounded-full bg-black/10 flex items-center justify-center hover:bg-g-green/20 transition-colors"
                            aria-label="Twitter">
                            <svg class="w-4 h-4 text-black" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                            </svg>
                        </a>
                        <a href="#"
                            class="w-8 h-8 rounded-full bg-black/10 flex items-center justify-center hover:bg-g-green/20 transition-colors"
                            aria-label="LinkedIn">
                            <svg class="w-4 h-4 text-black" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bottom bar -->
            <div
                class="border-t border-g-border pt-8 flex flex-col-reverse md:flex-row items-center justify-between gap-4">
                <p class="text-xs text-black/40">© {{ date('Y') }} دراهم. جميع الحقوق محفوظة.</p>
                <div class="flex gap-6">
                    <a href="#" class="text-xs text-black/40 hover:text-g-green transition-colors">سياسة
                        الكوكيز</a>
                    <a href="#" class="text-xs text-black/40 hover:text-g-green transition-colors">إعدادات
                        الخصوصية</a>
                </div>
            </div>

        </div>
    </footer>

    <script src="{{ asset('marketing/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('marketing/js/script.js') }}"></script>

    @auth
        <script>
            $(function() {
                var $btn = $('#user-menu-btn');
                var $dropdown = $('#user-menu-dropdown');
                var $chevron = $('#user-menu-chevron');

                function openMenu() {
                    $dropdown.attr('data-state', 'open');
                    $chevron.addClass('rotate-180');
                    $btn.attr('aria-expanded', 'true');
                }

                function closeMenu() {
                    $dropdown.attr('data-state', 'closed');
                    $chevron.removeClass('rotate-180');
                    $btn.attr('aria-expanded', 'false');
                }

                $btn.on('click', function(e) {
                    e.stopPropagation();
                    $dropdown.attr('data-state') === 'open' ? closeMenu() : openMenu();
                });

                $(document).on('click', closeMenu);
                $dropdown.on('click', function(e) {
                    e.stopPropagation();
                });

                // إغلاق بـ Escape
                $(document).on('keydown', function(e) {
                    if (e.key === 'Escape') closeMenu();
                });
            });
        </script>
    @endauth

    @yield('scripts')
</body>

</html>

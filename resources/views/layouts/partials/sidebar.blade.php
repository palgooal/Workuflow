{{-- ===== Sidebar ===== --}}
<aside
    class="fixed inset-y-0 right-0 z-modal w-64 flex flex-col bg-surface border-l border-subtle
           transform transition-transform duration-200 ease-in-out
           lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'"
>
    {{-- Logo --}}
    <div class="flex items-center px-5 h-[65px] shrink-0 border-b border-subtle">
        <img src="{{ asset('img/logo-darahum.png') }}" alt="دراهم" class="h-10 w-auto object-contain">
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 min-h-0 px-3 py-4 overflow-y-auto scrollbar-hidden">

        {{-- القسم الرئيسي --}}
        <p class="px-3 mb-1.5 text-[11px] font-bold uppercase tracking-[0.08em] text-slate-400">الرئيسية</p>
        <div class="space-y-0.5">
            <x-nav-item href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </x-slot>
                لوحة التحكم
            </x-nav-item>
        </div>

        {{-- القسم المالي --}}
        <p class="px-3 pt-5 mb-1.5 text-[11px] font-bold uppercase tracking-[0.08em] text-slate-400">المالية</p>
        <div class="space-y-0.5">
            <x-nav-item href="{{ route('projects.index') }}" :active="request()->routeIs('projects.*')">
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                </x-slot>
                المشاريع
            </x-nav-item>

            <x-nav-item href="{{ route('transactions.index') }}" :active="request()->routeIs('transactions.*')">
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </x-slot>
                المعاملات
            </x-nav-item>

            @if(auth()->user()->currentPlan()->can('wallets'))
            <x-nav-item href="{{ route('wallets.index') }}" :active="request()->routeIs('wallets.*')">
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </x-slot>
                الصناديق
            </x-nav-item>
            @else
            {{-- Free plan: Wallets locked → billing.upgrade --}}
            <a href="{{ route('billing.upgrade') }}"
               class="group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[14px] min-h-[42px]
                      transition-all duration-150 text-slate-400 font-medium hover:bg-slate-50 hover:text-slate-500">
                <span class="shrink-0 text-slate-300 group-hover:text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </span>
                <span class="truncate flex-1">الصناديق</span>
                <span class="text-xs bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-400
                             px-1.5 py-0.5 rounded-md font-semibold shrink-0">Pro</span>
            </a>
            @endif

            <x-nav-item href="{{ route('debts.index') }}" :active="request()->routeIs('debts.*')">
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </x-slot>
                الديون والالتزامات
            </x-nav-item>

            <x-nav-item href="{{ route('budget.index') }}" :active="request()->routeIs('budget.*')">
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </x-slot>
                الميزانية
            </x-nav-item>

            <x-nav-item href="{{ route('recurring.index') }}" :active="request()->routeIs('recurring.*')">
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </x-slot>
                الالتزامات الثابتة
            </x-nav-item>
        </div>

        {{-- الأعمال --}}
        <p class="px-3 pt-5 mb-1.5 text-[11px] font-bold uppercase tracking-[0.08em] text-slate-400">الأعمال</p>
        <div class="space-y-0.5">
            <x-nav-item href="{{ route('clients.index') }}" :active="request()->routeIs('clients.index') || request()->routeIs('clients.show') || request()->routeIs('clients.create') || request()->routeIs('clients.edit')">
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </x-slot>
                العملاء
            </x-nav-item>

            <x-nav-item href="{{ route('invoices.index') }}" :active="request()->routeIs('invoices.*')">
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </x-slot>
                الفواتير
            </x-nav-item>

            <x-nav-item href="{{ route('clients.follow-ups.index') }}" :active="request()->routeIs('clients.follow-ups.*')">
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </x-slot>
                المتابعات
            </x-nav-item>

            <x-nav-item href="{{ route('clients.segments.index') }}" :active="request()->routeIs('clients.segments.*')">
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                    </svg>
                </x-slot>
                شرائح العملاء
            </x-nav-item>

            <x-nav-item href="{{ route('team.index') }}" :active="request()->routeIs('team.*')">
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </x-slot>
                الفريق
            </x-nav-item>

            @php
                try {
                    $sidebarAffiliate  = auth()->user()->affiliate;
                    $affiliateRoute    = $sidebarAffiliate
                        ? route('affiliates.dashboard')
                        : route('affiliates.join');
                } catch (\Throwable $e) {
                    $sidebarAffiliate = null;
                    $affiliateRoute   = route('affiliates.join');
                }
                $affiliateActive = request()->routeIs('affiliates.*');
            @endphp
            <a href="{{ $affiliateRoute }}"
               @if($affiliateActive) aria-current="page" @endif
               class="group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[14px] min-h-[42px]
                      transition-all duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-accent/40
                      {{ $affiliateActive ? 'bg-brand-50 text-brand font-semibold' : 'text-slate-500 font-medium hover:bg-slate-50 hover:text-ink' }}">
                @if($affiliateActive)
                    <span class="absolute inset-y-2 right-0 w-[3px] rounded-full bg-accent"></span>
                @endif
                <span class="shrink-0 transition-colors {{ $affiliateActive ? 'text-brand' : 'text-slate-400 group-hover:text-slate-600' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <span class="truncate flex-1">💰 برنامج الإحالات</span>
                @if($sidebarAffiliate)
                    {{-- رصيد المسوّق المتاح --}}
                    <span class="text-[11px] font-bold bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded-md shrink-0 nums">
                        {{ number_format($sidebarAffiliate->balance, 0) }}₪
                    </span>
                @else
                    {{-- badge جديد --}}
                    <span class="text-[10px] font-bold bg-brand-100 text-brand px-1.5 py-0.5 rounded-md shrink-0 leading-tight">
                        جديد
                    </span>
                @endif
            </a>
        </div>

        {{-- التحليل --}}
        <p class="px-3 pt-5 mb-1.5 text-[11px] font-bold uppercase tracking-[0.08em] text-slate-400">التحليل</p>
        <div class="space-y-0.5">
            <x-nav-item href="{{ route('reports.index') }}" :active="request()->routeIs('reports.*')">
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </x-slot>
                التقارير
            </x-nav-item>

            <x-nav-item href="{{ route('categories.index') }}" :active="request()->routeIs('categories.*')">
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </x-slot>
                الفئات
            </x-nav-item>
        </div>

        {{-- الدعم --}}
        <p class="px-3 pt-5 mb-1.5 text-[11px] font-bold uppercase tracking-[0.08em] text-slate-400">الدعم</p>
        <div class="space-y-0.5">
            <x-nav-item href="{{ route('help.index') }}" :active="request()->routeIs('help.*')">
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </x-slot>
                مركز المساعدة
            </x-nav-item>
        </div>

    </nav>

    {{-- Plan Badge / Upgrade CTA --}}
    @php $plan = auth()->user()->currentPlan(); @endphp
    <div class="px-3 pb-2 shrink-0">
        @if($plan->value === 'free')
            {{-- Free: Upgrade CTA --}}
            <a href="{{ route('billing.upgrade') }}"
               class="flex items-center gap-2 w-full px-3 py-2.5 rounded-xl
                      bg-gradient-to-l from-indigo-50 to-purple-50
                      dark:from-indigo-900/30 dark:to-purple-900/20
                      border border-indigo-100 dark:border-indigo-800/50
                      hover:from-indigo-100 hover:to-purple-100
                      dark:hover:from-indigo-900/50 dark:hover:to-purple-900/40
                      transition group">
                <div class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/60 flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-indigo-700 dark:text-indigo-300 leading-tight">ترقّ إلى Pro</p>
                    <p class="text-[10px] text-indigo-500 dark:text-indigo-400 leading-tight">افتح كل الميزات</p>
                </div>
                <svg class="w-3.5 h-3.5 text-indigo-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-300 transition shrink-0 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        @elseif($plan->value === 'pro')
            {{-- Pro Badge --}}
            <div class="flex items-center gap-2 px-3 py-2 rounded-xl
                        bg-indigo-50 dark:bg-indigo-900/30
                        border border-indigo-100 dark:border-indigo-800/50">
                <span class="text-indigo-500 dark:text-indigo-400 text-sm leading-none">⚡</span>
                <span class="text-xs font-bold text-indigo-700 dark:text-indigo-300">خطة {{ $plan->label() }}</span>
                <span class="ms-auto inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold
                             bg-indigo-200 dark:bg-indigo-800 text-indigo-800 dark:text-indigo-200">نشطة</span>
            </div>
        @else
            {{-- Business Badge --}}
            <div class="flex items-center gap-2 px-3 py-2 rounded-xl
                        bg-purple-50 dark:bg-purple-900/30
                        border border-purple-100 dark:border-purple-800/50">
                <span class="text-purple-500 dark:text-purple-400 text-sm leading-none">⚡</span>
                <span class="text-xs font-bold text-purple-700 dark:text-purple-300">خطة {{ $plan->label() }}</span>
                <span class="ms-auto inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold
                             bg-purple-200 dark:bg-purple-800 text-purple-800 dark:text-purple-200">نشطة</span>
            </div>
        @endif
    </div>

    {{-- User Info --}}
    <div class="p-3 border-t border-subtle shrink-0">
        <a href="{{ route('settings.index') }}"
           class="flex items-center gap-3 rounded-xl px-2.5 py-2.5 hover:bg-slate-50 transition-colors group">
            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-accent to-brand flex items-center justify-center shrink-0 shadow-sm">
                <span class="text-white font-bold text-sm">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-ink truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-muted truncate">{{ auth()->user()->currentPlan()->label() }}</p>
            </div>
            <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
    </div>
</aside>

{{-- Sidebar Overlay (mobile) --}}
<div
    x-show="sidebarOpen"
    x-transition:enter="transition-opacity ease-in duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-out duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @click="sidebarOpen = false"
    class="fixed inset-0 z-overlay bg-ink/40 lg:hidden"
    style="display:none"
></div>

{{-- ===== Topbar ===== --}}
<header class="sticky top-0 z-sticky bg-surface/90 backdrop-blur-md border-b border-subtle
               px-4 sm:px-6 h-[65px] flex items-center gap-4">

    {{-- Hamburger (mobile) --}}
    <button
        @click="sidebarOpen = !sidebarOpen"
        class="lg:hidden p-1.5 -mr-1.5 rounded-lg text-muted hover:text-ink hover:bg-slate-100 transition-colors"
        aria-label="القائمة"
    >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>

    {{-- Page Title --}}
    <div class="flex-1 min-w-0 flex items-center gap-3">
        <span class="hidden sm:block w-1 h-7 rounded-full bg-gradient-to-b from-accent to-brand shrink-0"></span>
        <div class="min-w-0">
            <h1 class="text-[17px] font-bold text-ink truncate leading-tight">@yield('title')</h1>
            @hasSection('breadcrumb')
                <div class="flex items-center gap-1.5 text-xs text-muted mt-0.5">
                    @yield('breadcrumb')
                </div>
            @endif
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-1.5 sm:gap-2">

        {{-- Flash Success handled in app.blade.php body (all screen sizes) --}}

        {{-- Notifications --}}
        <a href="{{ route('notifications.index') }}"
           class="relative p-2 rounded-xl text-muted hover:text-brand hover:bg-brand-50 transition-colors"
           aria-label="الإشعارات">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            @php $unread = auth()->user()->unreadNotifications()->count() @endphp
            @if($unread > 0)
                <span class="absolute top-1 end-1 min-w-[17px] h-[17px] px-1 bg-error text-white text-[10px] font-bold rounded-full flex items-center justify-center nums ring-2 ring-surface">
                    {{ $unread > 9 ? '9+' : $unread }}
                </span>
            @endif
        </a>

        {{-- Separator --}}
        <span class="hidden sm:block w-px h-6 bg-subtle mx-1"></span>

        {{-- User Dropdown --}}
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open"
                    class="flex items-center gap-2 p-1 pr-1.5 rounded-xl hover:bg-slate-100 transition-colors">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-accent to-brand flex items-center justify-center shadow-sm">
                    <span class="text-white font-bold text-sm">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                </div>
                <svg class="w-4 h-4 text-muted transition-transform duration-150" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div
                x-show="open"
                @click.outside="open = false"
                @keydown.escape.window="open = false"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute end-0 mt-2 w-64 bg-surface rounded-2xl shadow-pop border border-subtle p-1.5 z-dropdown"
                style="display:none"
                x-cloak
            >
                {{-- رأس: الأفاتار + الاسم + البريد --}}
                <div class="flex items-center gap-3 px-2.5 py-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-accent to-brand flex items-center justify-center shadow-sm shrink-0">
                        <span class="text-white font-bold">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-ink truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-muted truncate">{{ auth()->user()->email }}</p>
                    </div>
                </div>

                <div class="h-px bg-subtle my-1"></div>

                {{-- روابط --}}
                <div class="space-y-0.5">
                    <a href="{{ route('profile.edit') }}"
                       class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-sm font-medium text-ink hover:bg-brand-50 hover:text-brand transition-colors">
                        <svg class="w-[18px] h-[18px] text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        الملف الشخصي
                    </a>
                    <a href="{{ route('settings.index') }}"
                       class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-sm font-medium text-ink hover:bg-brand-50 hover:text-brand transition-colors">
                        <svg class="w-[18px] h-[18px] text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        الإعدادات
                    </a>
                </div>

                <div class="h-px bg-subtle my-1"></div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-3 w-full px-2.5 py-2 rounded-xl text-sm font-medium text-red-600 hover:bg-error-soft transition-colors">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        تسجيل الخروج
                    </button>
                </form>
            </div>
        </div>

    </div>
</header>

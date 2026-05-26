<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $pageTitle ?? 'بوابة العميل' }} — {{ config('app.name') }}</title>

    {{-- Tailwind + Font --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen font-sans antialiased" x-data>

    {{-- ==================== HEADER ==================== --}}
    <header class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">

            <div class="flex items-center gap-3">
                {{-- Logo / App Name --}}
                <span class="text-lg font-bold text-gray-800">{{ config('app.name') }}</span>
                @isset($portalClient)
                    <span class="text-gray-300">|</span>
                    <span class="text-sm text-gray-600">{{ $portalClient->company ?? $portalClient->name }}</span>
                @endisset
            </div>

            {{-- Portal Nav --}}
            @isset($portalToken)
                <nav class="hidden sm:flex items-center gap-5 text-sm font-medium text-gray-600">
                    <a href="{{ route('portal.dashboard') }}"
                       class="{{ request()->routeIs('portal.dashboard') ? 'text-indigo-600 font-semibold' : 'hover:text-gray-800' }} transition">
                        الرئيسية
                    </a>
                    @if($portalToken->hasPermission(\App\Modules\CRM\Enums\PortalPermission::ViewInvoices))
                        <a href="{{ route('portal.invoices') }}"
                           class="{{ request()->routeIs('portal.invoices*') ? 'text-indigo-600 font-semibold' : 'hover:text-gray-800' }} transition">
                            الفواتير
                        </a>
                    @endif
                    <a href="{{ route('portal.profile') }}"
                       class="{{ request()->routeIs('portal.profile') ? 'text-indigo-600 font-semibold' : 'hover:text-gray-800' }} transition">
                        ملفي
                    </a>
                </nav>

                <form method="POST" action="{{ route('portal.logout') }}">
                    @csrf
                    <button type="submit"
                            class="text-xs text-gray-500 hover:text-red-600 transition px-2 py-1.5 rounded-lg hover:bg-red-50">
                        خروج
                    </button>
                </form>
            @endisset
        </div>
    </header>

    {{-- ==================== FLASH MESSAGES ==================== --}}
    @if(session('success'))
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-xl px-4 py-3">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-50 border border-red-200 text-red-800 text-sm rounded-xl px-4 py-3">
                {{ session('error') }}
            </div>
        </div>
    @endif

    {{-- ==================== MAIN CONTENT ==================== --}}
    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    {{-- ==================== FOOTER ==================== --}}
    <footer class="mt-16 border-t border-gray-100 bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4 text-center text-xs text-gray-400">
            مُدار بواسطة {{ config('app.name') }} · هذه البوابة مخصصة للعميل فقط
        </div>
    </footer>

</body>
</html>

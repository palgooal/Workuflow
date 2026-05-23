<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="دراهم — منصة إدارة مال وأعمال للمستقلين وأصحاب المشاريع">
    <title>{{ config('app.name', 'دراهم') }}{{ isset($title) ? ' — ' . $title : '' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style> body { font-family: 'Tajawal', sans-serif; } </style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased">

<div class="min-h-screen flex">

    {{-- الجانب الأيسر — العلامة التجارية (desktop only) --}}
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800 flex-col justify-between p-12">
        <div>
            {{-- Logo --}}
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-white font-bold text-xl">دراهم</span>
            </div>

            {{-- Headline --}}
            <div class="mt-16">
                <h1 class="text-4xl font-bold text-white leading-snug">
                    نظّم فلوسك ومشاريعك<br>
                    <span class="text-indigo-200">من مكان واحد</span>
                </h1>
                <p class="mt-4 text-indigo-200 text-lg leading-relaxed">
                    اعرف بالضبط أين يذهب ربحك — بدون تعقيد محاسبي.
                </p>
            </div>

            {{-- Features --}}
            <div class="mt-12 space-y-5">
                @foreach([
                    ['icon' => '📊', 'text' => 'لوحة تحكم تعرض ربحك الصافي فوراً'],
                    ['icon' => '📁', 'text' => 'إدارة مشاريع متعددة بعزل مالي كامل'],
                    ['icon' => '🔔', 'text' => 'تنبيهات ذكية للفواتير والديون'],
                    ['icon' => '📈', 'text' => 'تقارير مبسّطة بدون مصطلحات محاسبية'],
                ] as $feature)
                <div class="flex items-center gap-3">
                    <span class="text-2xl">{{ $feature['icon'] }}</span>
                    <span class="text-indigo-100">{{ $feature['text'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <p class="text-indigo-300 text-sm">© {{ date('Y') }} دراهم. جميع الحقوق محفوظة.</p>
    </div>

    {{-- الجانب الأيمن — النموذج --}}
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12">
        <div class="w-full max-w-md">

            {{-- شعار للموبايل --}}
            <div class="flex items-center gap-2 mb-8 lg:hidden">
                <div class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="font-bold text-gray-900 text-lg">دراهم</span>
            </div>

            {{ $slot }}

        </div>
    </div>

</div>

</body>
</html>

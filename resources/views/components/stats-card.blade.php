@props([
    'title',
    'value',
    'change'  => null,   // نسبة التغيير مثال: +12.5 أو -3.2
    'color'   => 'indigo', // indigo | green | red | yellow
    'prefix'  => '',
    'suffix'  => '',
])
{{-- $icon يُمرَّر كـ named slot: <x-slot name="icon"> --}}

@php
    $colors = [
        'indigo' => ['bg' => 'bg-indigo-50', 'icon' => 'text-indigo-600', 'badge' => 'bg-indigo-100 text-indigo-700'],
        'green'  => ['bg' => 'bg-green-50',  'icon' => 'text-green-600',  'badge' => 'bg-green-100 text-green-700'],
        'red'    => ['bg' => 'bg-red-50',    'icon' => 'text-red-600',    'badge' => 'bg-red-100 text-red-700'],
        'yellow' => ['bg' => 'bg-yellow-50', 'icon' => 'text-yellow-600', 'badge' => 'bg-yellow-100 text-yellow-700'],
    ];
    $c = $colors[$color] ?? $colors['indigo'];
    $isPositive = $change !== null && $change >= 0;
@endphp

<div class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-sm transition">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-sm text-gray-500">{{ $title }}</p>
            <p class="mt-1.5 text-2xl font-bold text-gray-900">
                {{ $prefix }}{{ $value }}{{ $suffix }}
            </p>
        </div>
        @isset($icon)
            <div class="w-11 h-11 {{ $c['bg'] }} rounded-xl flex items-center justify-center shrink-0">
                <span class="{{ $c['icon'] }}">{{ $icon }}</span>
            </div>
        @endisset
    </div>

    @if($change !== null)
        <div class="mt-3 flex items-center gap-1.5">
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                {{ $isPositive ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    @if($isPositive)
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    @endif
                </svg>
                {{ abs($change) }}%
            </span>
            <span class="text-xs text-gray-400">مقارنة بالشهر الماضي</span>
        </div>
    @endif
</div>

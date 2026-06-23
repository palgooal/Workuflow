@props([
    'title',
    'value',
    'change'   => null,    // نسبة التغيير مثال: +12.5 أو -3.2
    'color'    => 'brand', // brand | accent | green | red | yellow
    'prefix'   => '',
    'suffix'   => '',
    'tooltip'  => null,    // نص الـ tooltip التوضيحي
])
{{-- $icon يُمرَّر كـ named slot: <x-slot name="icon"> --}}

@php
    $colors = [
        'brand'  => ['bg' => 'bg-brand-50',   'icon' => 'text-brand'],
        'accent' => ['bg' => 'bg-accent-50',  'icon' => 'text-accent-700'],
        'indigo' => ['bg' => 'bg-brand-50',   'icon' => 'text-brand'],   // توافق خلفي
        'green'  => ['bg' => 'bg-success-soft','icon' => 'text-success-700'],
        'red'    => ['bg' => 'bg-error-soft', 'icon' => 'text-red-700'],
        'yellow' => ['bg' => 'bg-amber-50',   'icon' => 'text-amber-700'],
    ];
    $c = $colors[$color] ?? $colors['brand'];
    $isPositive = $change !== null && $change >= 0;
@endphp

<div {{ $attributes->merge(['class' => 'dash-card dash-card-hover p-5']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="flex items-center gap-1.5">
                <p class="text-[13px] font-medium text-muted truncate">{{ $title }}</p>
                @if($tooltip)
                <div class="relative flex-shrink-0" x-data="{ show: false }">
                    <button type="button"
                            @mouseenter="show = true" @mouseleave="show = false"
                            @focus="show = true" @blur="show = false"
                            class="w-4 h-4 rounded-full bg-slate-200 hover:bg-slate-300 flex items-center
                                   justify-center text-slate-600 text-[10px] font-bold transition cursor-help">
                        ?
                    </button>
                    <div x-show="show"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-cloak
                         class="absolute bottom-full mb-2 right-0 z-tooltip w-56 p-3
                                bg-ink text-white text-xs rounded-xl shadow-pop leading-relaxed"
                         style="min-width: 200px;">
                        {{ $tooltip }}
                        <div class="absolute top-full right-2 w-0 h-0"
                             style="border-left: 6px solid transparent; border-right: 6px solid transparent; border-top: 6px solid #0E0E1A;"></div>
                    </div>
                </div>
                @endif
            </div>
            {{-- الرقم هو البطل: أكبر وأبرز من الـlabel --}}
            <p class="mt-2 text-[28px] leading-none font-bold text-ink nums tracking-tight">
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
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold nums
                {{ $isPositive ? 'bg-success-soft text-success-700' : 'bg-error-soft text-red-700' }}">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    @if($isPositive)
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    @endif
                </svg>
                {{ abs($change) }}%
            </span>
            <span class="text-xs text-muted">مقارنة بالشهر الماضي</span>
        </div>
    @endif

    {{-- سطر تذييل اختياري (slot افتراضي) --}}
    @if(trim($slot) !== '')
        <div class="mt-2.5">{{ $slot }}</div>
    @endif
</div>

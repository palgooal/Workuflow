@props([
    'cols' => 4,   // عدد الأعمدة على الشاشات الكبيرة: 2 | 3 | 4
])
@php
    $lg = match((int) $cols) {
        2 => 'lg:grid-cols-2',
        3 => 'lg:grid-cols-3',
        default => 'lg:grid-cols-4',
    };
@endphp

<div {{ $attributes->merge(['class' => "grid grid-cols-2 $lg gap-3 sm:gap-4"]) }}>
    {{ $slot }}
</div>

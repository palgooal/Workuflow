@props(['color' => 'gray'])

@php
    // ألوان من نظام دراهم — تباين ≥ 4.5:1 على خلفية فاتحة
    $colors = [
        'green'  => 'bg-success-soft text-success-700',
        'red'    => 'bg-error-soft text-red-700',
        'yellow' => 'bg-amber-100 text-amber-800',
        'blue'   => 'bg-sky-100 text-sky-800',
        'indigo' => 'bg-brand-50 text-brand-700',
        'brand'  => 'bg-brand-50 text-brand-700',
        'teal'   => 'bg-accent-50 text-accent-700',
        'gray'   => 'bg-slate-100 text-slate-700',
        'purple' => 'bg-violet-100 text-violet-800',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium ' . ($colors[$color] ?? $colors['gray'])]) }}>
    {{ $slot }}
</span>

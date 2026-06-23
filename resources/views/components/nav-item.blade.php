@props(['href', 'active' => false])

<a
    href="{{ $href }}"
    @if($active) aria-current="page" @endif
    {{ $attributes->merge(['class' => 'group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[14px] min-h-[42px]
           transition-all duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-accent/40 '
        . ($active
            ? 'bg-brand-50 text-brand font-semibold'
            : 'text-slate-500 font-medium hover:bg-slate-50 hover:text-ink')]) }}
>
    {{-- مؤشر accent تركوازي للحالة النشطة (يخدم لون الشعار) --}}
    @if($active)
        <span class="absolute inset-y-2 right-0 w-[3px] rounded-full bg-accent"></span>
    @endif
    <span class="shrink-0 transition-colors {{ $active ? 'text-brand' : 'text-slate-400 group-hover:text-slate-600' }}">
        {{ $icon }}
    </span>
    <span class="truncate">{{ $slot }}</span>
</a>

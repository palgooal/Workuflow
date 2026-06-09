@props(['href', 'active' => false])

<a
    href="{{ $href }}"
    class="flex items-center gap-3 px-3 py-3 rounded-xl font-medium transition-all min-h-[44px]
           {{ $active
               ? 'bg-[#14C698]/20 text-[#14C698]'
               : 'text-white/70 hover:bg-white/10 hover:text-white' }}"
    style="font-size: 14.5px;"
>
    <span class="shrink-0 {{ $active ? 'text-[#14C698]' : 'text-white/55' }}">
        {{ $icon }}
    </span>
    <span class="truncate">{{ $slot }}</span>
</a>

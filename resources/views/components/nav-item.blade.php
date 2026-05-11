@props(['href', 'active' => false])

<a
    href="{{ $href }}"
    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
           {{ $active
               ? 'bg-indigo-50 text-indigo-700'
               : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
>
    <span class="{{ $active ? 'text-indigo-600' : 'text-gray-400' }}">
        {{ $icon }}
    </span>
    {{ $slot }}
</a>

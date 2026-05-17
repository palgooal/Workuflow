{{--
    x-tooltip — مكوّن Tooltip قابل للاستخدام في أي مكان
    الاستخدام:
        <x-tooltip text="شرح هنا">
            <button>زر</button>
        </x-tooltip>

    خيارات إضافية:
        position="top|bottom|right|left"  (افتراضي: top)
        width="48"                        (عرض الفقاعة بـ Tailwind، افتراضي: 56)
--}}

@props([
    'text'     => '',
    'position' => 'top',
    'width'    => '56',
])

<div class="relative inline-flex" x-data="{ show: false }">

    {{-- المحتوى الذي يُطلق الـ tooltip --}}
    <div @mouseenter="show = true" @mouseleave="show = false" @focus="show = true" @blur="show = false" class="inline-flex">
        {{ $slot }}
    </div>

    {{-- فقاعة الشرح --}}
    <div
        x-show="show"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 w-{{ $width }} p-3 bg-gray-900 text-white text-xs rounded-xl shadow-xl leading-relaxed pointer-events-none
               @if($position === 'top')    bottom-full mb-2 right-1/2 translate-x-1/2
               @elseif($position === 'bottom') top-full mt-2 right-1/2 translate-x-1/2
               @elseif($position === 'right')  right-full mr-2 top-1/2 -translate-y-1/2
               @else                       left-full ml-2 top-1/2 -translate-y-1/2
               @endif"
        style="min-width: 10rem"
    >
        {{ $text }}

        {{-- مؤشر السهم --}}
        @if($position === 'top')
            <div class="absolute top-full right-1/2 translate-x-1/2 w-0 h-0"
                 style="border-left:6px solid transparent;border-right:6px solid transparent;border-top:6px solid rgb(17 24 39)"></div>
        @elseif($position === 'bottom')
            <div class="absolute bottom-full right-1/2 translate-x-1/2 w-0 h-0"
                 style="border-left:6px solid transparent;border-right:6px solid transparent;border-bottom:6px solid rgb(17 24 39)"></div>
        @elseif($position === 'right')
            <div class="absolute top-1/2 -translate-y-1/2 left-full w-0 h-0"
                 style="border-top:6px solid transparent;border-bottom:6px solid transparent;border-right:6px solid rgb(17 24 39)"></div>
        @else
            <div class="absolute top-1/2 -translate-y-1/2 right-full w-0 h-0"
                 style="border-top:6px solid transparent;border-bottom:6px solid transparent;border-left:6px solid rgb(17 24 39)"></div>
        @endif
    </div>

</div>

@props([
    'action',                  // route للـGET form
    'reset' => null,           // route لمسح الفلاتر (يظهر زر ✕ عند تمريره + وجود فلاتر فعّالة)
    'active' => false,         // هل توجد فلاتر مطبّقة حالياً
])
{{--
    شريط فلترة موحّد (GET). الحقول تُمرَّر في الـslot الافتراضي داخل شبكة.
    <x-filter-bar :action="route('x.index')" :reset="route('x.index')" :active="request()->hasAny([...])">
        <input name="search" ...> ...
    </x-filter-bar>
--}}

<form method="GET" action="{{ $action }}" {{ $attributes->merge(['class' => 'dash-card p-3 sm:p-4']) }}>
    <div class="flex flex-col lg:flex-row lg:items-end gap-3">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:flex lg:flex-wrap gap-3 flex-1">
            {{ $slot }}
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <button type="submit"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-brand hover:bg-brand-600
                           text-white text-sm font-semibold rounded-btn transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L14 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 018 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                فلترة
            </button>
            @if($reset && $active)
                <a href="{{ $reset }}"
                   class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 bg-slate-100 hover:bg-slate-200
                          text-slate-600 text-sm font-medium rounded-btn transition-colors"
                   aria-label="مسح الفلاتر">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <span class="hidden sm:inline">مسح</span>
                </a>
            @endif
        </div>
    </div>
</form>

@props([
    'title'   => null,
    'subtitle'=> null,
    'padding' => 'p-5',   // مساحة المحتوى الداخلي (مرّر 'p-0' لجداول حافة-لحافة)
])
{{--
    بطاقة قسم بعنوان اختياري ورابط إجراء.
    <x-card-section title="..."><x-slot name="action">...</x-slot> المحتوى </x-card-section>
--}}

<section {{ $attributes->merge(['class' => 'dash-card overflow-hidden']) }}>
    @if($title || isset($action))
        <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-subtle">
            <div class="min-w-0">
                @if($title)
                    <h2 class="text-sm font-bold text-ink">{{ $title }}</h2>
                @endif
                @if($subtitle)
                    <p class="text-xs text-muted mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            @isset($action)
                <div class="shrink-0 text-sm">{{ $action }}</div>
            @endisset
        </div>
    @endif

    <div class="{{ $padding }}">
        {{ $slot }}
    </div>
</section>

@props(['title', 'description' => null, 'action' => null, 'actionLabel' => null])

<div class="flex flex-col items-center justify-center py-16 text-center">
    <div class="w-16 h-16 bg-brand-50 rounded-2xl flex items-center justify-center mb-4 text-brand">
        {{ $icon ?? '' }}
        @unless(isset($icon))
            <svg class="w-8 h-8 text-brand/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
        @endunless
    </div>
    <h3 class="text-base font-semibold text-ink">{{ $title }}</h3>
    @if($description)
        <p class="mt-1 text-sm text-muted max-w-xs">{{ $description }}</p>
    @endif
    @if($action && $actionLabel)
        <a href="{{ $action }}"
           class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 bg-brand hover:bg-brand-600
                  text-white text-sm font-semibold rounded-btn shadow-card transition-colors duration-150">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ $actionLabel }}
        </a>
    @endif
</div>

<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-brand text-white text-sm font-semibold rounded-btn shadow-card hover:bg-brand-600 active:bg-brand-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand/40 focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none transition-colors duration-150']) }}>
    {{ $slot }}
</button>

@php $balance = $wallet->balance(); @endphp
<div class="dash-card dash-card-hover {{ !$wallet->is_active ? 'opacity-60' : '' }}"
     x-data="{ menuOpen: false }">

    {{-- شريط لون الصندوق (هوية) --}}
    <div class="h-1 rounded-t-2xl overflow-hidden" style="background-color: {{ $wallet->color }}"></div>

    <div class="p-5">
        {{-- Header --}}
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center text-2xl shrink-0"
                     style="background-color: {{ $wallet->color }}1f;">
                    {{ $wallet->icon ?: $wallet->type->icon() }}
                </div>
                <div class="min-w-0">
                    <h3 class="font-semibold text-ink truncate">{{ $wallet->name }}</h3>
                    <span class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-0.5 rounded-full mt-0.5 {{ $wallet->type->tailwindBadge() }}">
                        {{ $wallet->type->label() }}
                    </span>
                </div>
            </div>

            {{-- Dropdown --}}
            <div class="relative shrink-0" @click.outside="menuOpen = false">
                <button @click="menuOpen = !menuOpen"
                        class="p-1.5 rounded-lg text-muted hover:text-ink hover:bg-slate-100 transition-colors"
                        aria-label="خيارات">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                    </svg>
                </button>
                <div x-show="menuOpen" x-transition x-cloak
                     class="absolute left-0 bottom-full mb-1 w-44 bg-surface rounded-xl shadow-pop border border-subtle py-1 z-dropdown">
                    <a href="{{ route('wallets.show', $wallet) }}"
                       class="flex items-center gap-2.5 px-3 py-2 text-sm text-ink hover:bg-slate-50 transition-colors">
                        <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        التفاصيل
                    </a>
                    <a href="{{ route('wallets.edit', $wallet) }}"
                       class="flex items-center gap-2.5 px-3 py-2 text-sm text-ink hover:bg-slate-50 transition-colors">
                        <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        تعديل
                    </a>
                    <div class="h-px bg-subtle my-1"></div>
                    <form method="POST" action="{{ route('wallets.destroy', $wallet) }}"
                          onsubmit="return confirm('حذف صندوق {{ addslashes($wallet->name) }}؟')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="w-full flex items-center gap-2.5 px-3 py-2 text-sm text-red-600 hover:bg-error-soft transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            حذف
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- الرصيد --}}
        <div class="mt-5">
            <p class="text-xs text-muted mb-1">الرصيد الحالي</p>
            <p class="text-[26px] leading-none font-bold nums {{ $balance >= 0 ? 'text-ink' : 'text-red-600' }}">
                {{ number_format($balance, 2) }}
                <span class="text-sm font-normal text-muted">{{ $wallet->currency }}</span>
            </p>
        </div>

        {{-- دخل / مصروف --}}
        <div class="mt-4 grid grid-cols-2 gap-2.5">
            <div class="bg-success-soft/60 rounded-xl px-3 py-2.5">
                <p class="text-[11px] text-success-700/80 mb-0.5">دخل</p>
                <p class="text-sm font-bold text-success-700 nums">+{{ number_format($wallet->totalIncome(), 0) }}</p>
            </div>
            <div class="bg-error-soft/60 rounded-xl px-3 py-2.5">
                <p class="text-[11px] text-red-600/80 mb-0.5">مصروف</p>
                <p class="text-sm font-bold text-red-600 nums">-{{ number_format($wallet->totalExpenses(), 0) }}</p>
            </div>
        </div>

        {{-- Footer --}}
        <div class="mt-4 pt-3 border-t border-subtle flex items-center justify-between text-xs">
            <span class="text-muted nums">{{ $wallet->transactions_count ?? 0 }} معاملة</span>
            <a href="{{ route('wallets.show', $wallet) }}"
               class="inline-flex items-center gap-1 text-brand hover:text-brand-700 font-semibold transition-colors">
                التفاصيل
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        </div>
    </div>
</div>

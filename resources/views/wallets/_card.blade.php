<div class="bg-white rounded-2xl border border-gray-100 hover:shadow-md transition-shadow
            {{ !$wallet->is_active ? 'opacity-60' : '' }}"
     x-data="{ menuOpen: false }"
     style="border-top: 3px solid {{ $wallet->color }}">

    <div class="p-5">
        {{-- Header --}}
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3">
                {{-- أيقونة الصندوق --}}
                <div class="w-11 h-11 rounded-xl flex items-center justify-center text-2xl shrink-0"
                     style="background-color: {{ $wallet->color }}22; border: 2px solid {{ $wallet->color }}44">
                    {{ $wallet->icon ?: $wallet->type->icon() }}
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">{{ $wallet->name }}</h3>
                    <span class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-0.5 rounded-full {{ $wallet->type->tailwindBadge() }}">
                        {{ $wallet->type->label() }}
                    </span>
                </div>
            </div>

            {{-- Dropdown --}}
            <div class="relative shrink-0" @click.outside="menuOpen = false">
                <button @click="menuOpen = !menuOpen"
                        class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                    </svg>
                </button>
                <div x-show="menuOpen" x-transition
                     class="absolute left-0 bottom-full mb-1 w-44 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-20">
                    <a href="{{ route('wallets.show', $wallet) }}"
                       class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        التفاصيل
                    </a>
                    <a href="{{ route('wallets.edit', $wallet) }}"
                       class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        تعديل
                    </a>
                    <hr class="my-1 border-gray-100">
                    <form method="POST" action="{{ route('wallets.destroy', $wallet) }}"
                          onsubmit="return confirm('حذف صندوق {{ addslashes($wallet->name) }}؟')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            حذف
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- الرصيد --}}
        @php $balance = $wallet->balance(); @endphp
        <div class="mt-4">
            <p class="text-xs text-gray-400 mb-1">الرصيد الحالي</p>
            <p class="text-2xl font-bold {{ $balance >= 0 ? 'text-gray-900' : 'text-red-600' }}">
                {{ number_format($balance, 2) }}
                <span class="text-sm font-normal text-gray-400">{{ $wallet->currency }}</span>
            </p>
        </div>

        {{-- دخل / مصروف --}}
        <div class="mt-3 grid grid-cols-2 gap-2 text-center">
            <div class="bg-green-50 rounded-xl p-2">
                <p class="text-[10px] text-gray-400 mb-0.5">دخل</p>
                <p class="text-xs font-bold text-green-700">+{{ number_format($wallet->totalIncome(), 0) }}</p>
            </div>
            <div class="bg-red-50 rounded-xl p-2">
                <p class="text-[10px] text-gray-400 mb-0.5">مصروف</p>
                <p class="text-xs font-bold text-red-700">-{{ number_format($wallet->totalExpenses(), 0) }}</p>
            </div>
        </div>

        {{-- Footer --}}
        <div class="mt-3 flex items-center justify-between text-xs text-gray-400">
            <span>{{ $wallet->transactions_count ?? 0 }} معاملة</span>
            <a href="{{ route('wallets.show', $wallet) }}"
               class="text-indigo-600 hover:text-indigo-700 font-medium">
                التفاصيل ←
            </a>
        </div>
    </div>
</div>

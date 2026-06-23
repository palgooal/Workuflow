@extends('layouts.app')
@section('title', 'الصناديق')
@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <x-page-header title="الصناديق والخزائن" subtitle="تتبع كاشك وحساباتك البنكية ومحافظك">
        <x-slot name="actions">
            <a href="{{ route('wallets.transfer.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-surface border border-subtle text-slate-700
                      rounded-btn text-sm font-medium hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                تحويل
            </a>
            <a href="{{ route('wallets.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand text-white rounded-btn text-sm font-semibold hover:bg-brand-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                صندوق جديد
            </a>
        </x-slot>
    </x-page-header>

    {{-- Flash --}}
    @if(session('success'))
        <div class="bg-success-soft border border-success/30 text-success-700 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if($wallets->isEmpty())
        <div class="dash-card py-16">
            <x-empty-state
                title="لا يوجد صناديق بعد"
                description="أضف صندوقك الأول لتتبع رصيدك"
                :action="route('wallets.create')"
                actionLabel="صندوق جديد" />
        </div>
    @else

        {{-- ملخص per-currency --}}
        @if($summary->count() >= 1)
        <x-stat-grid cols="4">
            @foreach($summary as $s)
            <x-stats-card :title="'إجمالي '.$s['currency']"
                          :value="number_format($s['balance'], 2)"
                          :color="$s['balance'] >= 0 ? 'brand' : 'red'">
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </x-slot>
                <span class="text-xs text-muted nums">{{ $s['count'] }} صندوق</span>
            </x-stats-card>
            @endforeach
        </x-stat-grid>
        @endif

        {{-- Tabs: كروت / جدول --}}
        <div x-data="{ view: 'cards' }">

            {{-- Tab switcher --}}
            <div class="flex items-center justify-end mb-4 gap-1 bg-surface border border-subtle rounded-xl p-1 w-fit mr-auto">
                <button @click="view = 'cards'"
                        :class="view === 'cards' ? 'bg-brand text-white shadow-sm' : 'text-muted hover:text-ink'"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                </button>
                <button @click="view = 'table'"
                        :class="view === 'table' ? 'bg-brand text-white shadow-sm' : 'text-muted hover:text-ink'"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 6h18M3 14h18M3 18h18"/>
                    </svg>
                </button>
            </div>

            {{-- Cards View --}}
            <div x-show="view === 'cards'" x-transition>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($wallets as $wallet)
                        @include('wallets._card', ['wallet' => $wallet])
                    @endforeach
                </div>
            </div>

            {{-- Table View --}}
            <div x-show="view === 'table'" x-transition x-cloak>
                <div class="dash-card overflow-visible">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-subtle">
                                <x-table-th>الصندوق</x-table-th>
                                <x-table-th>النوع</x-table-th>
                                <x-table-th>العملة</x-table-th>
                                <x-table-th align="center">الرصيد</x-table-th>
                                <x-table-th align="center">دخل</x-table-th>
                                <x-table-th align="center">مصروف</x-table-th>
                                <x-table-th>الحالة</x-table-th>
                                <x-table-th></x-table-th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-subtle/70">
                            @foreach($wallets as $wallet)
                            @php $bal = $wallet->balance(); @endphp
                            <tr class="dash-row">
                                <td class="dash-td">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xl shrink-0"
                                             style="background-color: {{ $wallet->color }}22;">
                                            {{ $wallet->icon ?: $wallet->type->icon() }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-ink">{{ $wallet->name }}</p>
                                            @if($wallet->description)
                                                <p class="text-xs text-muted truncate max-w-[140px]">{{ $wallet->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="dash-td">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-0.5 rounded-full {{ $wallet->type->tailwindBadge() }}">
                                        {{ $wallet->type->label() }}
                                    </span>
                                </td>
                                <td class="dash-td text-muted font-medium">{{ $wallet->currency }}</td>
                                <td class="dash-td text-center font-bold nums {{ $bal >= 0 ? 'text-ink' : 'text-red-600' }}">
                                    {{ number_format($bal, 2) }}
                                </td>
                                <td class="dash-td text-center nums text-success-700 font-medium">
                                    +{{ number_format($wallet->totalIncome(), 0) }}
                                </td>
                                <td class="dash-td text-center nums text-red-600 font-medium">
                                    -{{ number_format($wallet->totalExpenses(), 0) }}
                                </td>
                                <td class="dash-td">
                                    @if($wallet->is_active)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-0.5 rounded-full bg-success-soft text-success-700">نشط</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-0.5 rounded-full bg-slate-100 text-muted">موقوف</span>
                                    @endif
                                </td>
                                <td class="dash-td">
                                    <div class="flex items-center gap-2 justify-end">
                                        <a href="{{ route('wallets.show', $wallet) }}"
                                           class="row-action text-brand hover:text-brand-700 font-medium">عرض</a>
                                        <button type="button"
                                                onclick="toggleWalletMenu(event, 'wmenu-{{ $wallet->id }}')"
                                                class="row-action p-1 rounded-lg text-muted hover:text-ink hover:bg-slate-100"
                                                aria-label="خيارات">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                            </svg>
                                        </button>
                                    </div>
                                    {{-- Dropdown (fixed position via JS) --}}
                                    <div id="wmenu-{{ $wallet->id }}"
                                         class="hidden fixed w-44 bg-surface rounded-xl shadow-pop border border-subtle py-1"
                                         style="z-index: 1000;">
                                        <a href="{{ route('wallets.show', $wallet) }}"
                                           class="flex items-center gap-2.5 px-3 py-2 text-sm text-ink hover:bg-slate-50 transition-colors">
                                            <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            عرض التفاصيل
                                        </a>
                                        <a href="{{ route('wallets.edit', $wallet) }}"
                                           class="flex items-center gap-2.5 px-3 py-2 text-sm text-ink hover:bg-slate-50 transition-colors">
                                            <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            تعديل
                                        </a>
                                        <a href="{{ route('wallets.transfer.create') }}"
                                           class="flex items-center gap-2.5 px-3 py-2 text-sm text-ink hover:bg-slate-50 transition-colors">
                                            <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                            </svg>
                                            تحويل
                                        </a>
                                        <div class="h-px bg-subtle my-1"></div>
                                        <form method="POST" action="{{ route('wallets.destroy', $wallet) }}"
                                              onsubmit="return confirm('حذف صندوق {{ addslashes($wallet->name) }}؟')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="w-full flex items-center gap-2.5 px-3 py-2 text-sm text-red-600 hover:bg-error-soft transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                حذف
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>{{-- /x-data --}}
    @endif

</div>
@endsection

@push('scripts')
<script>
(function () {
    let _activeMenu = null;

    function closeAll() {
        if (_activeMenu) { _activeMenu.classList.add('hidden'); _activeMenu = null; }
    }

    window.toggleWalletMenu = function (e, id) {
        e.stopPropagation();
        const menu = document.getElementById(id);
        if (!menu) return;
        if (_activeMenu && _activeMenu !== menu) closeAll();
        if (menu.classList.contains('hidden')) {
            const btn = e.currentTarget;
            const r   = btn.getBoundingClientRect();
            const mw  = 176;
            const gap = 4;
            let top  = r.bottom + window.scrollY + gap;
            let left = r.right + window.scrollX - mw;
            if (left < gap) left = gap;
            if (left + mw > window.innerWidth - gap) left = window.innerWidth - mw - gap;
            menu.style.top  = top + 'px';
            menu.style.left = left + 'px';
            menu.classList.remove('hidden');
            _activeMenu = menu;
        } else {
            closeAll();
        }
    };

    document.addEventListener('click', closeAll);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeAll(); });
    document.addEventListener('scroll', closeAll, true);
})();
</script>
@endpush

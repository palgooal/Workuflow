@extends('layouts.app')

@section('title', 'الديون والالتزامات')

@section('content')
<div class="space-y-5"
     x-data="{
         tab: '{{ $tab }}',
         payModal: false,
         payDebtId: null,
         payDebtName: '',
         payDebtRemaining: 0,
         payDebtCurrency: 'SAR',
         payAmount: '',

         openPayModal(id, name, remaining, currency) {
             this.payDebtId        = id;
             this.payDebtName      = name;
             this.payDebtRemaining = remaining;
             this.payDebtCurrency  = currency;
             this.payAmount        = '';
             this.payModal         = true;
         },
         setFullAmount() {
             this.payAmount = this.payDebtRemaining;
         }
     }">

    {{-- Header --}}
    <x-page-header title="الديون والالتزامات" subtitle="تتبع ما عليك وما لك من ديون">
        <x-slot name="actions">
            <a href="{{ route('debts.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand hover:bg-brand-600
                      text-white text-sm font-semibold rounded-btn transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                دين جديد
            </a>
        </x-slot>
    </x-page-header>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        {{-- Debts on me --}}
        <div class="dash-card p-5">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-11 h-11 bg-error-soft rounded-xl flex items-center justify-center text-xl shrink-0">💸</div>
                    <div class="min-w-0">
                        <p class="text-[13px] font-medium text-muted">دين عليّ</p>
                        <p class="text-2xl font-bold text-red-600 nums leading-none mt-1">
                            {{ number_format($summary['borrowed_total'], 2) }}
                        </p>
                    </div>
                </div>
                @if($summary['borrowed_overdue'] > 0)
                    <span class="text-xs bg-error-soft text-red-700 px-2 py-0.5 rounded-full font-medium shrink-0">
                        {{ $summary['borrowed_overdue'] }} متأخر
                    </span>
                @endif
            </div>
            <p class="text-xs text-muted mt-3">المتبقي من الديون عليك</p>
        </div>

        {{-- Debts for me --}}
        <div class="dash-card p-5">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-11 h-11 bg-success-soft rounded-xl flex items-center justify-center text-xl shrink-0">🤝</div>
                    <div class="min-w-0">
                        <p class="text-[13px] font-medium text-muted">دين لي</p>
                        <p class="text-2xl font-bold text-success-700 nums leading-none mt-1">
                            {{ number_format($summary['lent_total'], 2) }}
                        </p>
                    </div>
                </div>
                @if($summary['lent_overdue'] > 0)
                    <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-medium shrink-0">
                        {{ $summary['lent_overdue'] }} متأخر
                    </span>
                @endif
            </div>
            <p class="text-xs text-muted mt-3">المتبقي مما أقرضته للآخرين</p>
        </div>

    </div>

    {{-- Tabs --}}
    <div class="dash-card overflow-hidden">

        <div class="flex border-b border-subtle">
            <button @click="tab = 'borrowed'"
                    :class="tab === 'borrowed'
                        ? 'border-b-2 border-brand text-brand bg-brand-50/50'
                        : 'border-b-2 border-transparent text-muted hover:text-ink hover:bg-slate-50'"
                    class="flex-1 flex items-center justify-center gap-2 px-5 py-3.5 text-sm font-semibold transition-colors">
                <span>💸</span>
                <span>ديون عليّ</span>
                @if($borrowed->where('status', '!=', \App\Support\Enums\DebtStatus::Paid)->count() > 0)
                    <span class="mr-1 px-1.5 py-0.5 text-xs bg-error-soft text-red-600 rounded-full nums">
                        {{ $borrowed->where('status', '!=', \App\Support\Enums\DebtStatus::Paid)->count() }}
                    </span>
                @endif
            </button>
            <button @click="tab = 'lent'"
                    :class="tab === 'lent'
                        ? 'border-b-2 border-brand text-brand bg-brand-50/50'
                        : 'border-b-2 border-transparent text-muted hover:text-ink hover:bg-slate-50'"
                    class="flex-1 flex items-center justify-center gap-2 px-5 py-3.5 text-sm font-semibold transition-colors">
                <span>🤝</span>
                <span>ديون لي</span>
                @if($lent->where('status', '!=', \App\Support\Enums\DebtStatus::Paid)->count() > 0)
                    <span class="mr-1 px-1.5 py-0.5 text-xs bg-success-soft text-success-700 rounded-full nums">
                        {{ $lent->where('status', '!=', \App\Support\Enums\DebtStatus::Paid)->count() }}
                    </span>
                @endif
            </button>
        </div>

        {{-- Borrowed Tab --}}
        <div x-show="tab === 'borrowed'" x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">

            @if($borrowed->isEmpty())
                <div class="py-14">
                    <x-empty-state
                        title="لا توجد ديون عليك"
                        description="أضف ديناً اقترضته من شخص لتتبعه هنا"
                        :action="route('debts.create') . '?type=borrowed'"
                        actionLabel="إضافة دين عليّ"
                    />
                </div>
            @else
                <div class="divide-y divide-subtle/70">
                    @foreach($borrowed as $debt)
                        @include('debts._debt_row', ['debt' => $debt])
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Lent Tab --}}
        <div x-show="tab === 'lent'" x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">

            @if($lent->isEmpty())
                <div class="py-14">
                    <x-empty-state
                        title="لا توجد ديون لك"
                        description="أضف ديناً أقرضته لشخص لتتبع متى يُسدَّد"
                        :action="route('debts.create') . '?type=lent'"
                        actionLabel="إضافة دين لي"
                    />
                </div>
            @else
                <div class="divide-y divide-subtle/70">
                    @foreach($lent as $debt)
                        @include('debts._debt_row', ['debt' => $debt])
                    @endforeach
                </div>
            @endif
        </div>

    </div>

    {{-- ==================== Payment Modal ==================== --}}
    <div x-show="payModal"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-modal flex items-center justify-center p-4"
         style="display: none;">

        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" @click="payModal = false"></div>

        {{-- Modal --}}
        <div class="relative bg-surface rounded-2xl shadow-pop w-full max-w-sm p-6"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             @click.stop>

            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-bold text-slate-900">تسجيل دفعة</h3>
                <button @click="payModal = false"
                        class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <p class="text-sm text-slate-500 mb-1">الطرف الآخر:</p>
            <p class="text-base font-semibold text-slate-900 mb-4" x-text="payDebtName"></p>

            <div class="bg-slate-50 rounded-xl p-3 mb-5 flex items-center justify-between">
                <span class="text-sm text-slate-500">المتبقي:</span>
                <span class="text-base font-bold text-red-600">
                    <span x-text="Number(payDebtRemaining).toLocaleString('en', {minimumFractionDigits: 2})"></span>
                    <span x-text="payDebtCurrency" class="text-xs mr-1"></span>
                </span>
            </div>

            <form :action="'/debts/' + payDebtId + '/record-payment'" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">مبلغ الدفعة</label>
                    <div class="relative">
                        <input type="number" name="amount"
                               x-model="payAmount"
                               min="0.01" step="0.01" required
                               placeholder="0.00"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200
                                      focus:outline-none focus:ring-2 focus:ring-accent/40 text-sm">
                    </div>
                    <button type="button"
                            @click="setFullAmount()"
                            class="mt-1.5 text-xs text-brand hover:text-brand-700 underline">
                        دفع المبلغ كاملاً
                    </button>
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                            class="flex-1 py-2.5 bg-brand hover:bg-brand-600
                                   text-white text-sm font-medium rounded-xl transition">
                        تسجيل الدفعة
                    </button>
                    <button type="button" @click="payModal = false"
                            class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200
                                   text-slate-700 text-sm font-medium rounded-xl transition">
                        إلغاء
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

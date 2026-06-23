@extends('layouts.app')
@section('title', $wallet->name)

@section('breadcrumb')
    <span class="text-muted/60">/</span>
    <a href="{{ route('wallets.index') }}" class="text-muted hover:text-ink transition-colors">الصناديق</a>
    <span class="text-muted/60">/</span>
    <span class="text-ink">{{ $wallet->name }}</span>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="dash-card overflow-hidden">
        {{-- شريط لون علوي --}}
        <div class="h-1.5" style="background-color: {{ $wallet->color }}"></div>

        <div class="p-6 flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl shrink-0 shadow-sm"
                     style="background-color: {{ $wallet->color }}22; border: 2px solid {{ $wallet->color }}33;">
                    {{ $wallet->icon ?: $wallet->type->icon() }}
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h1 class="text-2xl font-bold text-ink tracking-tight">{{ $wallet->name }}</h1>
                        @if(!$wallet->is_active)
                            <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-slate-100 text-muted">موقوف</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center text-xs font-medium px-2 py-0.5 rounded-full {{ $wallet->type->tailwindBadge() }}">
                            {{ $wallet->type->label() }}
                        </span>
                        <span class="text-muted text-xs">·</span>
                        <span class="text-sm text-muted font-medium">{{ $wallet->currency }}</span>
                        @if($wallet->description)
                            <span class="text-muted text-xs">·</span>
                            <span class="text-xs text-muted">{{ $wallet->description }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('transactions.create') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-surface border border-subtle text-slate-700 rounded-btn text-sm font-medium hover:bg-slate-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    معاملة جديدة
                </a>
                <a href="{{ route('wallets.transfer.create') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-surface border border-subtle text-slate-700 rounded-btn text-sm font-medium hover:bg-slate-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    تحويل
                </a>
                <a href="{{ route('wallets.edit', $wallet) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-brand text-white rounded-btn text-sm font-semibold hover:bg-brand-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    تعديل
                </a>
            </div>
        </div>
    </div>

    {{-- KPIs --}}
    @php $balance = $wallet->balance(); @endphp
    <x-stat-grid cols="3">
        <x-stats-card title="الرصيد الحالي"
                      :color="$balance >= 0 ? 'brand' : 'red'"
                      :value="number_format($balance, 2)"
                      :suffix="' '.$wallet->currency">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </x-slot>
        </x-stats-card>

        <x-stats-card title="إجمالي الدخل" color="green"
                      :value="number_format($wallet->totalIncome(), 2)"
                      prefix="+ ">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                </svg>
            </x-slot>
        </x-stats-card>

        <x-stats-card title="إجمالي المصروفات" color="red"
                      :value="number_format($wallet->totalExpenses(), 2)"
                      prefix="- ">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                </svg>
            </x-slot>
        </x-stats-card>
    </x-stat-grid>

    {{-- المعاملات --}}
    <x-card-section padding="p-0">
        <x-slot name="title">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                المعاملات
            </div>
        </x-slot>
        <x-slot name="action">
            <a href="{{ route('transactions.create') }}"
               class="inline-flex items-center gap-1 text-sm text-brand hover:text-brand-700 font-semibold transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                معاملة جديدة
            </a>
        </x-slot>

        @if($transactions->isEmpty())
            <div class="p-12 text-center">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-ink mb-1">لا توجد معاملات بعد</p>
                <p class="text-xs text-muted">أضف أول معاملة لهذا الصندوق</p>
            </div>
        @else
            <div class="overflow-x-auto scrollbar-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50/70 border-b border-subtle">
                            <x-table-th>التاريخ</x-table-th>
                            <x-table-th>الوصف</x-table-th>
                            <x-table-th>الفئة</x-table-th>
                            <x-table-th>المشروع</x-table-th>
                            <x-table-th align="left">المبلغ</x-table-th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-subtle/70">
                        @foreach($transactions as $tx)
                        <tr class="dash-row group">
                            <td class="dash-td">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0
                                                {{ $tx->type->value === 'income' ? 'bg-success-soft' : 'bg-error-soft' }}">
                                        <svg class="w-3.5 h-3.5 {{ $tx->type->value === 'income' ? 'text-success-700' : 'text-red-600' }}"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                            @if($tx->type->value === 'income')
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                                            @endif
                                        </svg>
                                    </div>
                                    <span class="text-muted nums text-xs">{{ $tx->transaction_date->format('d M Y') }}</span>
                                </div>
                            </td>
                            <td class="dash-td">
                                <p class="text-slate-800 font-medium">{{ $tx->description ?: '—' }}</p>
                                @if($tx->reference)
                                    <p class="text-xs text-muted">#{{ $tx->reference }}</p>
                                @endif
                            </td>
                            <td class="dash-td text-muted text-xs">
                                {{ $tx->category?->name ?: '—' }}
                            </td>
                            <td class="dash-td">
                                @if($tx->project)
                                    <a href="{{ route('projects.show', $tx->project) }}"
                                       class="text-xs text-brand hover:underline">{{ $tx->project->name }}</a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="dash-td text-left">
                                <span class="font-bold nums text-base {{ $tx->type->value === 'income' ? 'text-success-700' : 'text-red-600' }}">
                                    {{ $tx->type->value === 'income' ? '+' : '-' }}{{ number_format($tx->amount, 2) }}
                                </span>
                                <p class="text-[10px] text-muted nums">{{ $tx->currency }}</p>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($transactions->hasPages())
            <div class="px-5 py-3 border-t border-subtle">{{ $transactions->links() }}</div>
            @endif
        @endif
    </x-card-section>

    {{-- التحويلات --}}
    @if($transfers->isNotEmpty())
    <x-card-section padding="p-0">
        <x-slot name="title">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                التحويلات
            </div>
        </x-slot>

        <div class="overflow-x-auto scrollbar-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-subtle">
                        <x-table-th>التاريخ</x-table-th>
                        <x-table-th>من</x-table-th>
                        <x-table-th>إلى</x-table-th>
                        <x-table-th align="left">المبلغ</x-table-th>
                        <x-table-th align="left">الرسوم</x-table-th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-subtle/70">
                    @foreach($transfers as $transfer)
                    <tr class="dash-row">
                        <td class="dash-td text-muted nums text-xs">{{ $transfer->transferred_at->format('d M Y') }}</td>
                        <td class="dash-td">
                            <span class="font-medium text-ink">{{ $transfer->fromWallet->name }}</span>
                        </td>
                        <td class="dash-td">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3 h-3 text-muted shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                                </svg>
                                <span class="font-medium text-ink">{{ $transfer->toWallet->name }}</span>
                            </div>
                        </td>
                        <td class="dash-td text-left font-bold nums text-ink">
                            {{ number_format($transfer->amount, 2) }}
                        </td>
                        <td class="dash-td text-left nums {{ $transfer->fee > 0 ? 'text-red-600' : 'text-muted' }}">
                            {{ $transfer->fee > 0 ? number_format($transfer->fee, 2) : '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card-section>
    @endif

</div>
@endsection

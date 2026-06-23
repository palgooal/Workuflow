@extends('layouts.app')

@section('title', 'المعاملات')

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <x-page-header title="المعاملات" subtitle="سجل كامل لجميع الدخل والمصروفات">
        <x-slot name="actions">
            <a href="{{ route('transactions.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand hover:bg-brand-600
                      text-white text-sm font-semibold rounded-btn transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                معاملة جديدة
            </a>
        </x-slot>
    </x-page-header>

    {{-- Summary --}}
    @php $byCur = $summary['by_currency'] ?? []; $multi = $summary['multi_currency'] ?? false; @endphp

    @if($multi)
    {{-- عملات متعددة: جدول مدمج --}}
    <x-card-section title="الملخص حسب العملة" padding="p-0">
        <x-data-table>
            <x-slot name="head">
                <x-table-th>العملة</x-table-th>
                <x-table-th align="center">الدخل</x-table-th>
                <x-table-th align="center">المصروفات</x-table-th>
                <x-table-th align="center">الصافي</x-table-th>
            </x-slot>
            @foreach($byCur as $cur => $vals)
            <tr class="dash-row">
                <td class="dash-td font-semibold text-ink">{{ $cur }}</td>
                <td class="dash-td text-center font-bold text-success-700 nums">+{{ number_format($vals['income'], 2) }}</td>
                <td class="dash-td text-center font-bold text-red-600 nums">-{{ number_format($vals['expenses'], 2) }}</td>
                <td class="dash-td text-center font-bold nums {{ $vals['net'] >= 0 ? 'text-brand-600' : 'text-red-600' }}">
                    {{ $vals['net'] >= 0 ? '+' : '' }}{{ number_format($vals['net'], 2) }}
                </td>
            </tr>
            @endforeach
        </x-data-table>
    </x-card-section>
    <div class="flex items-center gap-2 text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded-xl px-4 py-2.5">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        المبالغ معروضة منفصلة لكل عملة — لا يتم دمجها تفادياً للخطأ في الحساب.
    </div>

    @else
    {{-- عملة واحدة: بطاقات إحصاء --}}
    @php
        $vals = array_values($byCur)[0] ?? ['income'=>0,'expenses'=>0,'net'=>0];
        $curCode = $byCur ? array_key_first($byCur) : '';
    @endphp
    <x-stat-grid cols="3">
        <x-stats-card title="إجمالي الدخل" color="green"
                      :value="number_format($vals['income'], 2)" prefix="+" :suffix="$curCode ? ' '.$curCode : ''">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                </svg>
            </x-slot>
        </x-stats-card>

        <x-stats-card title="إجمالي المصروفات" color="red"
                      :value="number_format($vals['expenses'], 2)" prefix="-" :suffix="$curCode ? ' '.$curCode : ''">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                </svg>
            </x-slot>
        </x-stats-card>

        <x-stats-card title="الصافي" :color="$vals['net'] >= 0 ? 'brand' : 'red'"
                      :value="($vals['net'] >= 0 ? '+' : '').number_format($vals['net'], 2)" :suffix="$curCode ? ' '.$curCode : ''">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-slot>
        </x-stats-card>
    </x-stat-grid>
    @endif

    {{-- Filters --}}
    <x-filter-bar :action="route('transactions.index')"
                  :reset="route('transactions.index')"
                  :active="request()->hasAny(['search','type','project','date_from','date_to','category'])">
        <div class="col-span-2 sm:col-span-3 lg:w-64">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="بحث في الوصف..." class="filter-field">
        </div>
        <select name="type" class="filter-field lg:w-40">
            <option value="">كل الأنواع</option>
            <option value="income"  {{ request('type') === 'income'  ? 'selected' : '' }}>دخل</option>
            <option value="expense" {{ request('type') === 'expense' ? 'selected' : '' }}>مصروف</option>
        </select>
        <select name="project" class="filter-field lg:w-48">
            <option value="">كل المشاريع</option>
            @foreach($projects as $project)
                <option value="{{ $project->id }}" {{ request('project') === $project->id ? 'selected' : '' }}>
                    {{ $project->name }}
                </option>
            @endforeach
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="filter-field lg:w-44">
    </x-filter-bar>

    {{-- Transactions List --}}
    @if($transactions->isEmpty())
        <div class="dash-card py-16">
            <x-empty-state
                title="لا توجد معاملات"
                description="ابدأ بتسجيل أول معاملة لتتبع دخلك ومصاريفك"
                :action="route('transactions.create')"
                actionLabel="إضافة معاملة" />
        </div>
    @else
        <div class="dash-card overflow-hidden">
            {{-- Table Header (desktop) --}}
            <div class="hidden sm:grid grid-cols-12 gap-4 px-5 py-3 bg-slate-50/70 border-b border-subtle dash-th">
                <div class="col-span-1">النوع</div>
                <div class="col-span-4">الوصف</div>
                <div class="col-span-2">المشروع</div>
                <div class="col-span-2">الفئة</div>
                <div class="col-span-2">التاريخ</div>
                <div class="col-span-1 text-left">المبلغ</div>
            </div>

            <div class="divide-y divide-subtle/70">
                @foreach($transactions as $tx)
                <div class="flex sm:grid sm:grid-cols-12 sm:gap-4 items-center px-5 py-3.5 dash-row group">

                    {{-- Type Icon --}}
                    <div class="sm:col-span-1 shrink-0 ml-3 sm:ml-0">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center
                                    {{ $tx->isIncome() ? 'bg-success-soft' : 'bg-error-soft' }}">
                            <svg class="w-4 h-4 {{ $tx->isIncome() ? 'text-success-700' : 'text-red-600' }}"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                @if($tx->isIncome())
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                                @endif
                            </svg>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="sm:col-span-4 flex-1 min-w-0">
                        <p class="text-sm font-semibold text-ink truncate">{{ $tx->description }}</p>
                        @if($tx->reference)
                            <p class="text-xs text-muted">{{ $tx->reference }}</p>
                        @endif
                    </div>

                    {{-- Project --}}
                    <div class="hidden sm:block sm:col-span-2">
                        @if($tx->project)
                            <span class="inline-flex items-center gap-1.5 text-xs text-slate-600">
                                <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $tx->project->color }}"></span>
                                {{ Str::limit($tx->project->name, 15) }}
                            </span>
                        @else
                            <span class="text-xs text-muted/60">—</span>
                        @endif
                    </div>

                    {{-- Category --}}
                    <div class="hidden sm:block sm:col-span-2">
                        @if($tx->category)
                            <span class="text-xs text-slate-600">
                                {{ $tx->category->icon }} {{ Str::limit($tx->category->name, 12) }}
                            </span>
                        @else
                            <span class="text-xs text-muted/60">—</span>
                        @endif
                    </div>

                    {{-- Date --}}
                    <div class="hidden sm:block sm:col-span-2">
                        <span class="text-xs text-muted nums">{{ $tx->transaction_date->format('d/m/Y') }}</span>
                    </div>

                    {{-- Amount + Actions --}}
                    <div class="sm:col-span-1 flex items-center justify-end gap-2 shrink-0">
                        <span class="text-sm font-bold nums {{ $tx->isIncome() ? 'text-success-700' : 'text-red-600' }}">
                            {{ $tx->isIncome() ? '+' : '-' }}{{ number_format($tx->amount, 2) }}
                        </span>
                        <div class="flex items-center gap-0.5 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                            <a href="{{ route('transactions.edit', $tx) }}"
                               class="row-action hover:text-brand hover:bg-brand-50" aria-label="تعديل">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('transactions.destroy', $tx) }}"
                                  onsubmit="return confirm('حذف هذه المعاملة؟')">
                                @csrf @method('DELETE')
                                <button type="submit" class="row-action hover:text-red-600 hover:bg-red-50" aria-label="حذف">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @if($transactions->hasPages())
                <div class="px-5 py-3 border-t border-subtle">{{ $transactions->links() }}</div>
            @endif
        </div>
    @endif

</div>
@endsection

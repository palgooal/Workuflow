@extends('layouts.app')

@section('title', 'المعاملات')

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">المعاملات</h1>
            <p class="mt-0.5 text-sm text-gray-500">سجل كامل لجميع الدخل والمصروفات</p>
        </div>
        <a href="{{ route('transactions.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700
                  text-white text-sm font-medium rounded-xl transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            معاملة جديدة
        </a>
    </div>

    {{-- Summary Cards --}}
    @php $byCur = $summary['by_currency'] ?? []; $multi = $summary['multi_currency'] ?? false; @endphp

    @if($multi)
    {{-- عملات متعددة: جدول مدمج --}}
    <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden">
        <div class="grid grid-cols-4 text-xs font-semibold text-gray-400 uppercase tracking-wide px-5 py-2.5 border-b border-gray-50 bg-gray-50">
            <div>العملة</div>
            <div class="text-center">الدخل</div>
            <div class="text-center">المصروفات</div>
            <div class="text-center">الصافي</div>
        </div>
        @foreach($byCur as $cur => $vals)
        <div class="grid grid-cols-4 items-center px-5 py-3 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
            <div class="text-sm font-semibold text-gray-600">{{ $cur }}</div>
            <div class="text-center font-bold text-green-700 text-sm">+{{ number_format($vals['income'], 2) }}</div>
            <div class="text-center font-bold text-red-600 text-sm">-{{ number_format($vals['expenses'], 2) }}</div>
            <div class="text-center font-bold text-sm {{ $vals['net'] >= 0 ? 'text-indigo-700' : 'text-red-600' }}">
                {{ $vals['net'] >= 0 ? '+' : '' }}{{ number_format($vals['net'], 2) }}
            </div>
        </div>
        @endforeach
    </div>
    <div class="flex items-center gap-2 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-4 py-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        المبالغ معروضة منفصلة لكل عملة — لا يتم دمجها تفادياً للخطأ في الحساب.
    </div>

    @else
    {{-- عملة واحدة: البطاقات الأصلية --}}
    @php $vals = array_values($byCur)[0] ?? ['income'=>0,'expenses'=>0,'net'=>0]; @endphp
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-green-50 border border-green-100 rounded-2xl p-4 text-center">
            <p class="text-xs text-gray-500 mb-1">إجمالي الدخل</p>
            <p class="text-xl font-bold text-green-700">+{{ number_format($vals['income'], 2) }}</p>
            @if($byCur) <p class="text-xs text-gray-400 mt-0.5">{{ array_key_first($byCur) }}</p> @endif
        </div>
        <div class="bg-red-50 border border-red-100 rounded-2xl p-4 text-center">
            <p class="text-xs text-gray-500 mb-1">إجمالي المصروفات</p>
            <p class="text-xl font-bold text-red-700">-{{ number_format($vals['expenses'], 2) }}</p>
            @if($byCur) <p class="text-xs text-gray-400 mt-0.5">{{ array_key_first($byCur) }}</p> @endif
        </div>
        <div class="{{ $vals['net'] >= 0 ? 'bg-indigo-50 border-indigo-100' : 'bg-red-50 border-red-100' }} border rounded-2xl p-4 text-center">
            <p class="text-xs text-gray-500 mb-1">الصافي</p>
            <p class="text-xl font-bold {{ $vals['net'] >= 0 ? 'text-indigo-700' : 'text-red-700' }}">
                {{ $vals['net'] >= 0 ? '+' : '' }}{{ number_format($vals['net'], 2) }}
            </p>
            @if($byCur) <p class="text-xs text-gray-400 mt-0.5">{{ array_key_first($byCur) }}</p> @endif
        </div>
    </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('transactions.index') }}"
          class="bg-white rounded-2xl border border-gray-100 p-4">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">

            {{-- Search --}}
            <div class="col-span-2 sm:col-span-3 lg:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="بحث في الوصف..."
                       class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200
                              focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            {{-- Type --}}
            <select name="type"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white
                           focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">كل الأنواع</option>
                <option value="income"  {{ request('type') === 'income'  ? 'selected' : '' }}>دخل</option>
                <option value="expense" {{ request('type') === 'expense' ? 'selected' : '' }}>مصروف</option>
            </select>

            {{-- Project --}}
            <select name="project"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white
                           focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">كل المشاريع</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ request('project') === $project->id ? 'selected' : '' }}>
                        {{ $project->name }}
                    </option>
                @endforeach
            </select>

            {{-- Date From --}}
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="px-3 py-2 text-sm rounded-xl border border-gray-200
                          focus:outline-none focus:ring-2 focus:ring-indigo-500">

            {{-- Actions --}}
            <div class="flex gap-2">
                <button type="submit"
                        class="flex-1 px-3 py-2 bg-indigo-600 text-white text-sm font-medium rounded-xl hover:bg-indigo-700 transition">
                    فلترة
                </button>
                @if(request()->hasAny(['search','type','project','date_from','date_to','category']))
                    <a href="{{ route('transactions.index') }}"
                       class="px-3 py-2 bg-gray-100 text-gray-600 text-sm rounded-xl hover:bg-gray-200 transition">
                        ✕
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- Transactions Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">

        @if($transactions->isEmpty())
            <div class="py-16">
                <x-empty-state
                    title="لا توجد معاملات"
                    description="ابدأ بتسجيل أول معاملة لتتبع دخلك ومصاريفك"
                    :action="route('transactions.create')"
                    actionLabel="إضافة معاملة"
                />
            </div>
        @else
            {{-- Table Header --}}
            <div class="hidden sm:grid grid-cols-12 gap-4 px-5 py-3 bg-gray-50 border-b border-gray-100
                        text-xs font-medium text-gray-500 uppercase tracking-wide">
                <div class="col-span-1">النوع</div>
                <div class="col-span-4">الوصف</div>
                <div class="col-span-2">المشروع</div>
                <div class="col-span-2">الفئة</div>
                <div class="col-span-2">التاريخ</div>
                <div class="col-span-1 text-left">المبلغ</div>
            </div>

            <div class="divide-y divide-gray-50">
                @foreach($transactions as $tx)
                <div class="flex sm:grid sm:grid-cols-12 sm:gap-4 items-center px-5 py-3.5
                            hover:bg-gray-50 transition group">

                    {{-- Type Icon --}}
                    <div class="sm:col-span-1 shrink-0 ml-3 sm:ml-0">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center
                                    {{ $tx->isIncome() ? 'bg-green-100' : 'bg-red-100' }}">
                            <svg class="w-4 h-4 {{ $tx->isIncome() ? 'text-green-600' : 'text-red-600' }}"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($tx->isIncome())
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                                @endif
                            </svg>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="sm:col-span-4 flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $tx->description }}</p>
                        @if($tx->reference)
                            <p class="text-xs text-gray-400">{{ $tx->reference }}</p>
                        @endif
                    </div>

                    {{-- Project --}}
                    <div class="hidden sm:block sm:col-span-2">
                        @if($tx->project)
                            <span class="inline-flex items-center gap-1 text-xs text-gray-600">
                                <span class="w-2 h-2 rounded-full" style="background-color: {{ $tx->project->color }}"></span>
                                {{ Str::limit($tx->project->name, 15) }}
                            </span>
                        @else
                            <span class="text-xs text-gray-300">—</span>
                        @endif
                    </div>

                    {{-- Category --}}
                    <div class="hidden sm:block sm:col-span-2">
                        @if($tx->category)
                            <span class="text-xs text-gray-600">
                                {{ $tx->category->icon }} {{ Str::limit($tx->category->name, 12) }}
                            </span>
                        @else
                            <span class="text-xs text-gray-300">—</span>
                        @endif
                    </div>

                    {{-- Date --}}
                    <div class="hidden sm:block sm:col-span-2">
                        <span class="text-xs text-gray-500">
                            {{ $tx->transaction_date->format('d/m/Y') }}
                        </span>
                    </div>

                    {{-- Amount + Actions --}}
                    <div class="sm:col-span-1 flex items-center gap-2 shrink-0">
                        <span class="text-sm font-bold {{ $tx->isIncome() ? 'text-green-600' : 'text-red-600' }}">
                            {{ $tx->isIncome() ? '+' : '-' }}{{ number_format($tx->amount, 2) }}
                        </span>
                        <div class="hidden group-hover:flex items-center gap-1">
                            <a href="{{ route('transactions.edit', $tx) }}"
                               class="p-1 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('transactions.destroy', $tx) }}"
                                  onsubmit="return confirm('حذف هذه المعاملة؟')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="p-1 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($transactions->hasPages())
                <div class="px-5 py-4 border-t border-gray-100">
                    {{ $transactions->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection

@extends('layouts.app')

@section('title', $project->name)

@section('breadcrumb')
    <span class="text-gray-300">/</span>
    <a href="{{ route('projects.index') }}" class="text-gray-500 hover:text-gray-700">المشاريع</a>
    <span class="text-gray-300">/</span>
    <span class="text-gray-700">{{ $project->name }}</span>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="h-2 w-full" style="background-color: {{ $project->color }}"></div>
        <div class="p-6 flex items-start justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl"
                     style="background-color: {{ $project->color }}1A; border: 2px solid {{ $project->color }}40">
                    {{ $project->type->icon() }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold text-gray-900">{{ $project->name }}</h1>
                        <x-badge :color="$project->is_active ? 'green' : 'gray'">
                            {{ $project->is_active ? 'نشط' : 'متوقف' }}
                        </x-badge>
                        <x-badge color="blue">{{ $project->type->label() }}</x-badge>
                    </div>
                    @if($project->description)
                        <p class="mt-1 text-sm text-gray-500">{{ $project->description }}</p>
                    @endif
                    <p class="mt-1 text-xs text-gray-400">
                        العملة: {{ $project->currency }} ·
                        أُنشئ {{ $project->created_at->diffForHumans() }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('projects.edit', $project) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium
                          text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    تعديل
                </a>
                <a href="{{ route('transactions.index') }}?project={{ $project->id }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium
                          text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    إضافة معاملة
                </a>
            </div>
        </div>
    </div>

    {{-- Financial Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stats-card
            title="إجمالي الدخل"
            :value="number_format($summary['income'], 2)"
            color="green"
        >
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                </svg>
            </x-slot>
        </x-stats-card>

        <x-stats-card
            title="إجمالي المصروفات"
            :value="number_format($summary['expenses'], 2)"
            color="red"
        >
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                </svg>
            </x-slot>
        </x-stats-card>

        <x-stats-card
            title="صافي الربح"
            :value="number_format(abs($summary['net_profit']), 2)"
            :color="$summary['net_profit'] >= 0 ? 'green' : 'red'"
            :prefix="$summary['net_profit'] >= 0 ? '+' : '-'"
        >
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </x-slot>
        </x-stats-card>

        <x-stats-card
            title="هامش الربح"
            :value="$summary['margin'] . '%'"
            :color="$summary['margin'] >= 30 ? 'green' : ($summary['margin'] >= 0 ? 'yellow' : 'red')"
        >
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                </svg>
            </x-slot>
        </x-stats-card>
    </div>

    {{-- Recent Transactions --}}
    <div class="bg-white rounded-2xl border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-900">آخر المعاملات</h2>
            <a href="{{ route('transactions.index') }}?project={{ $project->id }}"
               class="text-sm text-indigo-600 hover:text-indigo-700">
                عرض الكل ({{ $summary['tx_count'] }})
            </a>
        </div>

        @if($recentTransactions->isEmpty())
            <div class="py-12">
                <x-empty-state
                    title="لا توجد معاملات بعد"
                    description="ابدأ بإضافة دخل أو مصروف لهذا المشروع"
                />
            </div>
        @else
            <div class="divide-y divide-gray-50">
                @foreach($recentTransactions as $tx)
                <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50 transition">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0
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
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $tx->description }}</p>
                        <p class="text-xs text-gray-400">
                            {{ $tx->category?->name ?? 'بدون فئة' }} ·
                            {{ $tx->transaction_date->format('d/m/Y') }}
                        </p>
                    </div>
                    <span class="text-sm font-bold {{ $tx->isIncome() ? 'text-green-600' : 'text-red-600' }} shrink-0">
                        {{ $tx->isIncome() ? '+' : '-' }}{{ number_format($tx->amount, 2) }}
                    </span>
                </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection

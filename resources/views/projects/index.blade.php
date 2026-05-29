@extends('layouts.app')

@section('title', 'المشاريع')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">المشاريع</h1>
            <p class="mt-0.5 text-sm text-gray-500">إدارة مشاريعك وتتبع أرباحها الصافية</p>
        </div>
        @can('create', App\Models\Project::class)
            <a href="{{ route('projects.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700
                      text-white text-sm font-medium rounded-xl transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                مشروع جديد
            </a>
        @else
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-400
                        text-sm font-medium rounded-xl cursor-not-allowed"
                 title="وصلت للحد الأقصى من المشاريع في خطتك الحالية">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                الحد الأقصى مكتمل
            </div>
        @endcan
    </div>

    {{-- Portfolio Summary --}}
    @if($portfolio['projects_count'] > 0)
    @php $byCur = $portfolio['by_currency'] ?? []; $multi = $portfolio['multi_currency'] ?? false; @endphp

    @if($multi)
    {{-- عملات متعددة --}}
    <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden">
        <div class="grid grid-cols-4 text-xs font-semibold text-gray-400 uppercase tracking-wide px-5 py-2.5 bg-gray-50 border-b border-gray-100">
            <div>العملة</div>
            <div class="text-center">الدخل</div>
            <div class="text-center">المصروفات</div>
            <div class="text-center">الصافي</div>
        </div>
        @foreach($byCur as $cur => $vals)
        <div class="grid grid-cols-4 items-center px-5 py-3 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
            <div class="text-sm font-semibold text-gray-700">{{ $cur }}</div>
            <div class="text-center font-bold text-green-700 text-sm">+{{ number_format($vals['income'], 2) }}</div>
            <div class="text-center font-bold text-red-600 text-sm">-{{ number_format($vals['expenses'], 2) }}</div>
            <div class="text-center font-bold text-sm {{ $vals['net'] >= 0 ? 'text-indigo-700' : 'text-red-600' }}">
                {{ $vals['net'] >= 0 ? '+' : '' }}{{ number_format($vals['net'], 2) }}
            </div>
        </div>
        @endforeach
        {{-- صف المشاريع النشطة --}}
        <div class="px-5 py-2.5 bg-gray-50 border-t border-gray-100 flex items-center gap-2 text-sm text-gray-500">
            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
            المشاريع النشطة:
            <span class="font-semibold text-indigo-700">{{ $portfolio['active_count'] }} / {{ $portfolio['projects_count'] }}</span>
        </div>
    </div>
    <div class="flex items-center gap-2 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-4 py-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        معاملات بعملات متعددة — المبالغ معروضة منفصلة لكل عملة بدون دمج.
    </div>

    @else
    {{-- عملة واحدة --}}
    @php $vals = array_values($byCur)[0] ?? ['income'=>0,'expenses'=>0,'net'=>0]; $cur = array_key_first($byCur) ?? ''; @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stats-card title="إجمالي الدخل" :value="number_format($vals['income'], 2) . ' ' . $cur" color="green" prefix="">
            <x-slot name="icon"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg></x-slot>
        </x-stats-card>
        <x-stats-card title="إجمالي المصروفات" :value="number_format($vals['expenses'], 2) . ' ' . $cur" color="red">
            <x-slot name="icon"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg></x-slot>
        </x-stats-card>
        <x-stats-card title="صافي الربح" :value="number_format(abs($vals['net']), 2) . ' ' . $cur" :color="$vals['net'] >= 0 ? 'green' : 'red'" :prefix="$vals['net'] >= 0 ? '+' : '-'">
            <x-slot name="icon"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></x-slot>
        </x-stats-card>
        <x-stats-card title="المشاريع النشطة" :value="$portfolio['active_count'] . ' / ' . $portfolio['projects_count']" color="indigo">
            <x-slot name="icon"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg></x-slot>
        </x-stats-card>
    </div>
    @endif

    @endif

    {{-- Business Projects --}}
    @if(isset($projects['business']) && $projects['business']->isNotEmpty())
    <div>
        <div class="flex items-center gap-2 mb-3">
            <span class="text-lg">💼</span>
            <h2 class="text-base font-semibold text-gray-800">المشاريع التجارية</h2>
            <x-badge color="blue">{{ $projects['business']->count() }}</x-badge>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($projects['business'] as $project)
                @include('projects._card', ['project' => $project])
            @endforeach
        </div>
    </div>
    @endif

    {{-- Personal Projects --}}
    @if(isset($projects['personal']) && $projects['personal']->isNotEmpty())
    <div>
        <div class="flex items-center gap-2 mb-3">
            <span class="text-lg">🏠</span>
            <h2 class="text-base font-semibold text-gray-800">المشاريع الشخصية</h2>
            <x-badge color="purple">{{ $projects['personal']->count() }}</x-badge>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($projects['personal'] as $project)
                @include('projects._card', ['project' => $project])
            @endforeach
        </div>
    </div>
    @endif

    {{-- Empty State --}}
    @if($portfolio['projects_count'] === 0)
        <x-empty-state
            title="لا توجد مشاريع بعد"
            description="أنشئ مشروعك الأول لتبدأ بتتبع دخلك ومصاريفك وصافي ربحك"
            :action="route('projects.create')"
            actionLabel="إنشاء أول مشروع"
        />
    @endif

</div>
@endsection

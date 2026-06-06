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
    @php
        $byCur = $summary['by_currency'] ?? [];
        $multi = $summary['multi_currency'] ?? false;
        $projCur = $summary['project_currency'] ?? $project->currency ?? 'ILS';
    @endphp

    @if($multi)
    {{-- عملات متعددة: جدول --}}
    <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden">
        <div class="grid grid-cols-5 text-xs font-semibold text-gray-400 uppercase tracking-wide px-5 py-2.5 border-b border-gray-50 bg-gray-50">
            <div>العملة</div>
            <div class="text-center">الدخل</div>
            <div class="text-center">المصروفات</div>
            <div class="text-center">الصافي</div>
            <div class="text-center">الهامش</div>
        </div>
        @foreach($byCur as $cur => $vals)
        <div class="grid grid-cols-5 items-center px-5 py-3 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
            <div class="flex items-center gap-1.5">
                <span class="text-sm font-semibold text-gray-700">{{ $cur }}</span>
                @if($cur === $projCur)
                <span class="text-xs bg-indigo-100 text-indigo-600 px-1.5 py-0.5 rounded font-medium">أساسي</span>
                @endif
            </div>
            <div class="text-center font-bold text-green-700 text-sm">+{{ number_format($vals['income'], 2) }}</div>
            <div class="text-center font-bold text-red-600 text-sm">-{{ number_format($vals['expenses'], 2) }}</div>
            <div class="text-center font-bold text-sm {{ $vals['net'] >= 0 ? 'text-indigo-700' : 'text-red-600' }}">
                {{ $vals['net'] >= 0 ? '+' : '' }}{{ number_format($vals['net'], 2) }}
            </div>
            <div class="text-center text-sm font-semibold {{ $vals['margin'] >= 30 ? 'text-teal-600' : ($vals['margin'] >= 0 ? 'text-amber-600' : 'text-red-500') }}">
                {{ $vals['margin'] }}%
            </div>
        </div>
        @endforeach
    </div>
    <div class="flex items-center gap-2 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-4 py-2.5">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        معاملات بعملات متعددة — مقارنة قيمة العقد والميزانية تعتمد على عملة المشروع ({{ $projCur }}) فقط.
    </div>

    @else
    {{-- عملة واحدة: البطاقات الأصلية --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stats-card
            title="إجمالي الدخل"
            :value="number_format($summary['income'], 2) . ' ' . $projCur"
            color="green"
            tooltip="مجموع كل المبالغ المسجّلة كمعاملات «دخل» في هذا المشروع حتى اليوم."
        >
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                </svg>
            </x-slot>
        </x-stats-card>

        <x-stats-card
            title="إجمالي المصروفات"
            :value="number_format($summary['expenses'], 2) . ' ' . $projCur"
            color="red"
            tooltip="مجموع كل المبالغ المسجّلة كمعاملات «مصروف» في هذا المشروع."
        >
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                </svg>
            </x-slot>
        </x-stats-card>

        <x-stats-card
            title="صافي الربح"
            :value="number_format(abs($summary['net_profit']), 2) . ' ' . $projCur"
            :color="$summary['net_profit'] >= 0 ? 'green' : 'red'"
            :prefix="$summary['net_profit'] >= 0 ? '+' : '-'"
            tooltip="إجمالي الدخل ناقص إجمالي المصروفات."
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
            tooltip="نسبة صافي الربح من إجمالي الدخل. 30% فأكثر = ممتاز."
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
    @endif

    {{-- Contract Value & Expense Budget Progress --}}
    @if($summary['contract_value'] || $summary['expense_budget'])
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        {{-- قيمة العقد --}}
        @if($summary['contract_value'])
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-gray-900">قيمة العقد</span>
                    <div class="relative" x-data="{ show: false }">
                        <button type="button" @mouseenter="show=true" @mouseleave="show=false"
                                class="w-4 h-4 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center
                                       justify-center text-gray-500 text-xs font-bold cursor-help">?</button>
                        <div x-show="show" x-cloak
                             class="absolute bottom-full mb-2 right-0 z-50 w-60 p-3 bg-gray-900 text-white
                                    text-xs rounded-xl shadow-xl leading-relaxed">
                            المبلغ المتفق عليه مع العميل في العقد. الشريط يوضح كم استلمت منه فعلياً حتى الآن من خلال معاملات الدخل.
                            <div class="absolute top-full right-2 w-0 h-0"
                                 style="border-left:6px solid transparent;border-right:6px solid transparent;border-top:6px solid rgb(17 24 39)"></div>
                        </div>
                    </div>
                </div>
                <span class="text-xs font-bold
                    {{ $summary['contract_collected'] >= 100 ? 'text-green-600' : 'text-blue-600' }}">
                    {{ $summary['contract_collected'] }}%
                </span>
            </div>

            {{-- Progress Bar --}}
            <div class="w-full bg-gray-100 rounded-full h-2.5 mb-3">
                <div class="h-2.5 rounded-full transition-all duration-500
                    {{ $summary['contract_collected'] >= 100 ? 'bg-green-500' : 'bg-blue-500' }}"
                     style="width: {{ min($summary['contract_collected'], 100) }}%"></div>
            </div>

            <div class="flex justify-between text-xs text-gray-500">
                <span>مُستلم: <strong class="text-gray-800">{{ number_format($summary['income'], 2) }}</strong></span>
                <span>العقد: <strong class="text-gray-800">{{ number_format($summary['contract_value'], 2) }}</strong></span>
            </div>
            @if($summary['contract_remaining'] > 0)
            <p class="mt-2 text-xs text-amber-600 bg-amber-50 rounded-lg px-2.5 py-1.5">
                متبقي استلام: <strong>{{ number_format($summary['contract_remaining'], 2) }} {{ $project->currency }}</strong>
            </p>
            @else
            <p class="mt-2 text-xs text-green-600 bg-green-50 rounded-lg px-2.5 py-1.5">
                ✅ تم استلام قيمة العقد كاملاً
            </p>
            @endif
        </div>
        @endif

        {{-- ميزانية التكاليف --}}
        @if($summary['expense_budget'])
        <div class="bg-white rounded-2xl border border-gray-100 p-5
            {{ $summary['budget_overrun'] ? 'border-red-200' : '' }}">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center
                        {{ $summary['budget_overrun'] ? 'bg-red-100' : 'bg-orange-100' }}">
                        <svg class="w-4 h-4 {{ $summary['budget_overrun'] ? 'text-red-600' : 'text-orange-600' }}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-gray-900">ميزانية التكاليف</span>
                    <div class="relative" x-data="{ show: false }">
                        <button type="button" @mouseenter="show=true" @mouseleave="show=false"
                                class="w-4 h-4 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center
                                       justify-center text-gray-500 text-xs font-bold cursor-help">?</button>
                        <div x-show="show" x-cloak
                             class="absolute bottom-full mb-2 right-0 z-50 w-60 p-3 bg-gray-900 text-white
                                    text-xs rounded-xl shadow-xl leading-relaxed">
                            الحد الأقصى للمصروفات الذي خططت له مسبقاً. الشريط يتحول أحمر تلقائياً إذا تجاوزت هذا السقف.
                            <div class="absolute top-full right-2 w-0 h-0"
                                 style="border-left:6px solid transparent;border-right:6px solid transparent;border-top:6px solid rgb(17 24 39)"></div>
                        </div>
                    </div>
                </div>
                <span class="text-xs font-bold {{ $summary['budget_overrun'] ? 'text-red-600' : 'text-orange-600' }}">
                    {{ $summary['budget_used_percent'] }}%
                </span>
            </div>

            {{-- Progress Bar --}}
            <div class="w-full bg-gray-100 rounded-full h-2.5 mb-3">
                <div class="h-2.5 rounded-full transition-all duration-500
                    {{ $summary['budget_overrun'] ? 'bg-red-500' : ($summary['budget_used_percent'] >= 80 ? 'bg-amber-500' : 'bg-orange-400') }}"
                     style="width: {{ min($summary['budget_used_percent'], 100) }}%"></div>
            </div>

            <div class="flex justify-between text-xs text-gray-500">
                <span>مُنفَق: <strong class="text-gray-800">{{ number_format($summary['expenses'], 2) }}</strong></span>
                <span>الميزانية: <strong class="text-gray-800">{{ number_format($summary['expense_budget'], 2) }}</strong></span>
            </div>
            @if($summary['budget_overrun'])
            <p class="mt-2 text-xs text-red-600 bg-red-50 rounded-lg px-2.5 py-1.5">
                ⚠️ تجاوز الميزانية بـ
                <strong>{{ number_format($summary['expenses'] - $summary['expense_budget'], 2) }} {{ $project->currency }}</strong>
            </p>
            @elseif($summary['budget_remaining'] !== null)
            <p class="mt-2 text-xs text-green-600 bg-green-50 rounded-lg px-2.5 py-1.5">
                متبقي من الميزانية: <strong>{{ number_format($summary['budget_remaining'], 2) }} {{ $project->currency }}</strong>
            </p>
            @endif
        </div>
        @endif

    </div>
    @endif

    {{-- هامش الخدمات --}}
    @if(! empty($summary['services_margin']))
    <div class="bg-white rounded-2xl border border-gray-100 p-5">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                هامش الخدمات
            </h3>
            {{-- إجمالي سريع --}}
            @if($summary['total_services_revenue'] > 0)
            <div class="text-xs text-gray-500">
                إجمالي التكاليف:
                <strong class="text-gray-700">{{ number_format($summary['total_members_cost'], 2) }} {{ $project->currency }}</strong>
            </div>
            @endif
        </div>

        <div class="space-y-3">
            @foreach($summary['services_margin'] as $svcMargin)
            @php
                $pct = $svcMargin['margin_pct'];
                $marginClasses = match(true) {
                    $svcMargin['is_loss']      => ['badge' => 'bg-red-100 text-red-700',     'bar' => 'bg-red-500',    'border' => 'border-red-100'],
                    $pct !== null && $pct < 20 => ['badge' => 'bg-orange-100 text-orange-700', 'bar' => 'bg-orange-400', 'border' => 'border-orange-100'],
                    $pct !== null && $pct < 40 => ['badge' => 'bg-amber-100 text-amber-700',   'bar' => 'bg-amber-400',  'border' => 'border-amber-100'],
                    default                    => ['badge' => 'bg-emerald-100 text-emerald-700','bar' => 'bg-emerald-500','border' => 'border-emerald-100'],
                };
            @endphp
            <div class="rounded-xl border {{ $marginClasses['border'] }} bg-gray-50 overflow-hidden">

                {{-- Service Row --}}
                <div class="flex items-center justify-between px-4 py-3">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $svcMargin['name'] }}</p>
                        <div class="flex items-center gap-3 mt-0.5 text-xs text-gray-500">
                            <span>إيراد: <strong class="text-gray-700">{{ number_format($svcMargin['revenue'], 2) }}</strong></span>
                            @if($svcMargin['members_cost'] > 0)
                            <span>تكلفة: <strong class="text-gray-700">{{ number_format($svcMargin['members_cost'], 2) }}</strong></span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        @if($pct !== null)
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $marginClasses['badge'] }}">
                            {{ $svcMargin['is_loss'] ? '⚠ خسارة' : $pct . '%' }}
                        </span>
                        @endif
                        <span class="text-sm font-bold {{ $svcMargin['is_loss'] ? 'text-red-600' : 'text-gray-800' }}">
                            {{ number_format($svcMargin['margin'], 2) }} {{ $project->currency }}
                        </span>
                    </div>
                </div>

                {{-- Progress Bar --}}
                @if($svcMargin['revenue'] > 0 && $pct !== null)
                <div class="h-1 bg-gray-100">
                    <div class="{{ $marginClasses['bar'] }} h-1 transition-all duration-500"
                         style="width: {{ max(0, min($pct, 100)) }}%"></div>
                </div>
                @endif

                {{-- Members --}}
                @if(! empty($svcMargin['members']))
                <div class="px-4 py-2.5 space-y-1.5 bg-white border-t border-gray-100">
                    @foreach($svcMargin['members'] as $memberData)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-md bg-indigo-100 flex items-center justify-center
                                        text-indigo-600 font-bold text-xs flex-shrink-0">
                                {{ mb_substr($memberData['name'], 0, 1) }}
                            </div>
                            <span class="text-xs text-gray-700 font-medium">{{ $memberData['name'] }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($memberData['team_cost'] > 0)
                            <span class="text-xs font-semibold {{ $memberData['team_cost_paid'] ? 'text-green-600' : 'text-gray-700' }}">
                                {{ number_format($memberData['team_cost'], 2) }} {{ $project->currency }}
                            </span>
                            @if($memberData['team_cost_paid'])
                                <span class="text-xs text-green-500">✓ مدفوع</span>
                            @else
                                <form method="POST"
                                      action="{{ route('projects.pay-team', [$project, $memberData['id']]) }}"
                                      onsubmit="return confirm('تسجيل دفعة لـ {{ $memberData['name'] }}؟')">
                                    @csrf
                                    <button type="submit"
                                            class="px-2.5 py-1 bg-green-600 hover:bg-green-700 text-white
                                                   text-xs font-medium rounded-lg transition">
                                        دفع
                                    </button>
                                </form>
                            @endif
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

            </div>
            @endforeach
        </div>

    </div>
    @endif

    {{-- Quotes --}}
    <div class="bg-white rounded-2xl border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                <span>📋</span> عروض الأسعار
                @if($projectQuotes->count() > 0)
                    <span class="px-2 py-0.5 text-xs bg-indigo-100 text-indigo-700 rounded-full font-medium">
                        {{ $projectQuotes->count() }}
                    </span>
                @endif
            </h2>
            <a href="{{ route('quotes.create') }}?project_id={{ $project->id }}&client_id={{ $project->client_id }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm text-indigo-600
                      border border-indigo-200 bg-indigo-50 rounded-xl hover:bg-indigo-100 transition">
                + إنشاء عرض
            </a>
        </div>

        @if($projectQuotes->isEmpty())
            <div class="py-10 text-center text-gray-400 text-sm">
                لا توجد عروض أسعار مرتبطة بهذا المشروع
            </div>
        @else
            <div class="divide-y divide-gray-50">
                @foreach($projectQuotes as $q)
                @php
                    $isExp = $q->isExpired();
                @endphp
                <a href="{{ route('quotes.show', $q->ulid) }}"
                   class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50 transition">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-900">{{ $q->number }}</span>
                            @if($q->title)
                                <span class="text-xs text-gray-400 truncate">— {{ $q->title }}</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $q->issue_date->format('d/m/Y') }}</p>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $isExp ? 'bg-orange-100 text-orange-700' : $q->status->badgeClass() }}">
                        {{ $q->status->icon() }} {{ $isExp ? 'منتهي' : $q->status->label() }}
                    </span>
                    <span class="text-sm font-semibold text-gray-700 shrink-0">
                        {{ number_format($q->total, 2) }} {{ $q->currency }}
                    </span>
                </a>
                @endforeach
            </div>
        @endif
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

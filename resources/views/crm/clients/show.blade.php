@extends('layouts.app')

@section('title', $client->name)

@section('content')
<div class="space-y-5" x-data="clientProfile()">

    {{-- ==================== Header ==================== --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('clients.index') }}"
               class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            {{-- Avatar --}}
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl font-bold flex-shrink-0
                        {{ $client->is_archived ? 'bg-gray-100 text-gray-400' : 'bg-indigo-100 text-indigo-700' }}">
                {{ mb_substr($client->name, 0, 1) }}
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl font-bold text-gray-900">{{ $client->name }}</h1>
                    @if($client->status)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $client->status->badgeClass() }}">
                        {{ $client->status->label() }}
                    </span>
                    @endif
                    @if($client->is_archived)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                        📦 مؤرشف
                    </span>
                    @endif
                </div>
                <div class="flex items-center gap-3 mt-1 flex-wrap">
                    @if($client->company)
                    <span class="text-sm text-gray-500">{{ $client->company }}</span>
                    @endif
                    @if($client->email)
                    <a href="mailto:{{ $client->email }}" class="text-sm text-indigo-600 hover:underline">{{ $client->email }}</a>
                    @endif
                    @if($client->phone)
                    <a href="tel:{{ $client->phone }}" class="text-sm text-gray-500 hover:text-gray-700">{{ $client->phone }}</a>
                    @php
                        $waNumber = preg_replace('/[^0-9]/', '', $client->phone);
                        // أرقام محلية تبدأ بـ 0 → أضف 970
                        if (str_starts_with($waNumber, '0')) {
                            $waNumber = '970' . substr($waNumber, 1);
                        }
                    @endphp
                    <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener"
                       title="تواصل عبر واتساب"
                       class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-green-50 hover:bg-green-100
                              text-green-600 transition flex-shrink-0">
                        <svg viewBox="0 0 24 24" class="w-4 h-4" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                    </a>
                    @endif
                </div>
                {{-- الوسوم --}}
                @if($client->tags->isNotEmpty())
                <div class="flex flex-wrap gap-1 mt-2">
                    @foreach($client->tags as $tag)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium text-white"
                          style="background-color: {{ $tag->color ?? '#6366f1' }}">
                        @if($tag->icon){{ $tag->icon }} @endif{{ $tag->name }}
                    </span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- أزرار الإجراءات --}}
        <div class="flex items-center gap-2 flex-shrink-0">
            @if(!empty($tagSuggestions) && count($tagSuggestions) > 0)
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-sm text-amber-700 bg-amber-50
                               border border-amber-200 rounded-xl hover:bg-amber-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    اقتراحات وسوم ({{ count($tagSuggestions) }})
                </button>
                <div x-show="open" @click.away="open = false" x-cloak
                     class="absolute left-0 mt-1 w-56 bg-white border border-gray-100 rounded-xl shadow-lg z-10 p-2">
                    @foreach($tagSuggestions as $suggestion)
                    <form method="POST" action="{{ route('clients.tags.assign', [$client->public_id, $suggestion->id]) }}"
                          @submit.prevent="
                            fetch($el.action, {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            })
                            .then(r => r.json())
                            .then(d => { open = false; window.location.reload(); })
                            .catch(() => window.location.reload())
                          ">
                        @csrf
                        <button type="submit"
                                class="w-full text-right flex items-center gap-2 px-3 py-2 text-sm text-gray-700
                                       hover:bg-gray-50 rounded-lg transition">
                            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                                  style="background-color: {{ $suggestion->color ?? '#6366f1' }}"></span>
                            تعيين: {{ $suggestion->name }}
                        </button>
                    </form>
                    @endforeach
                </div>
            </div>
            @endif

            @can('update', $client)
            <a href="{{ route('clients.edit', $client->public_id) }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 text-sm text-gray-600 bg-white
                      border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                تعديل
            </a>

            @endcan

            @if($client->is_archived)
            @can('restore', $client)
            <form method="POST" action="{{ route('clients.restore', $client->public_id) }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-sm text-teal-600
                               bg-teal-50 border border-teal-200 rounded-xl hover:bg-teal-100 transition">
                    استعادة
                </button>
            </form>
            @endcan
            @else
            @can('archive', $client)
            <form method="POST" action="{{ route('clients.archive', $client->public_id) }}"
                  onsubmit="return confirm('هل تريد أرشفة هذا العميل؟')">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-sm text-gray-500
                               bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                    أرشفة
                </button>
            </form>
            @endcan
            @endif
        </div>
    </div>

    {{-- ==================== KPI Cards ==================== --}}
    @php
        $multiCurrency = count($revenueByCurrency) > 1;
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">

        {{-- إجمالي الإيراد --}}
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي الإيراد</p>
            @if(empty($revenueByCurrency))
                <p class="text-2xl font-bold text-gray-300">—</p>
            @else
                @foreach($revenueByCurrency as $cur => $amount)
                <p class="font-bold text-gray-900 {{ $multiCurrency ? 'text-lg' : 'text-2xl' }}">
                    {{ number_format($amount, 0) }}
                    <span class="text-sm font-normal text-gray-400">{{ $cur }}</span>
                </p>
                @endforeach
            @endif
        </div>

        {{-- إجمالي المدفوع --}}
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي المدفوع</p>
            @if(empty($paidByCurrency))
                <p class="text-2xl font-bold text-gray-300">—</p>
            @else
                @foreach($paidByCurrency as $cur => $amount)
                <p class="font-bold text-teal-600 {{ $multiCurrency ? 'text-lg' : 'text-2xl' }}">
                    {{ number_format($amount, 0) }}
                    <span class="text-sm font-normal text-gray-400">{{ $cur }}</span>
                </p>
                @endforeach
            @endif
        </div>

        {{-- المستحق --}}
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-gray-500 mb-1">المستحق</p>
            @if(empty($outstandingByCurrency))
                <p class="text-2xl font-bold text-gray-300">✓</p>
                <p class="text-xs text-teal-500 mt-0.5">لا يوجد مستحق</p>
            @else
                @foreach($outstandingByCurrency as $cur => $amount)
                <p class="font-bold text-red-600 {{ count($outstandingByCurrency) > 1 ? 'text-lg' : 'text-2xl' }}">
                    {{ number_format($amount, 0) }}
                    <span class="text-sm font-normal text-gray-400">{{ $cur }}</span>
                </p>
                @endforeach
            @endif
        </div>

        {{-- نقاط الصحة --}}
        @php
            $hs = $client->latestHealthScore;
            $hsScore = $hs?->score ?? $client->health_score;
            $hsColor = !$hsScore ? 'text-gray-300' : ($hsScore >= 75 ? 'text-teal-600' : ($hsScore >= 50 ? 'text-amber-600' : 'text-red-500'));
            $hsBg    = !$hsScore ? '' : ($hsScore >= 75 ? 'bg-teal-50' : ($hsScore >= 50 ? 'bg-amber-50' : 'bg-red-50'));
            $factorLabels = [
                'payment_rate'      => ['معدل الدفع', '35%'],
                'recurrence'        => ['تكرار العمل', '25%'],
                'revenue_value'     => ['قيمة الإيراد', '20%'],
                'contact_regularity'=> ['انتظام التواصل', '10%'],
                'response_rate'     => ['معدل الاستجابة', '10%'],
            ];
            $factors = $hs?->factorBreakdown() ?? [];
        @endphp
        <div class="bg-white rounded-xl border border-gray-100 p-4 col-span-1">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs text-gray-500">نقاط الصحة</p>
                @if($hsScore !== null)
                <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $hsBg }} {{ $hsColor }}">
                    {{ $hs?->grade()->label() ?? '' }}
                </span>
                @endif
            </div>
            <p class="text-3xl font-bold {{ $hsColor }}">
                {{ $hsScore ?? '—' }}<span class="text-sm font-normal text-gray-400">/100</span>
            </p>
            @if(!empty($factors))
            <div class="mt-3 space-y-1.5">
                @foreach($factorLabels as $key => [$label, $weight])
                @php $val = $factors[$key] ?? 0; @endphp
                <div>
                    <div class="flex justify-between text-xs text-gray-500 mb-0.5">
                        <span>{{ $label }}</span>
                        <span class="font-medium text-gray-700">{{ $val }}%</span>
                    </div>
                    <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500
                            {{ $val >= 75 ? 'bg-teal-500' : ($val >= 50 ? 'bg-amber-400' : 'bg-red-400') }}"
                             style="width: {{ $val }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- تنبيه عند وجود عملات متعددة --}}
    @if($multiCurrency)
    <div class="flex items-center gap-2 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-4 py-2.5">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>هذا العميل لديه فواتير بعملات متعددة — المبالغ معروضة منفصلة لكل عملة بدون تحويل.</span>
    </div>
    @endif

    {{-- ==================== التبويبات ==================== --}}
    <div x-data="{ tab: '{{ request()->get('tab', 'activity') }}' }">

        {{-- شريط التبويبات --}}
        <div class="flex gap-1 border-b border-gray-200 overflow-x-auto">
            @php
                $tabs = [
                    'activity'   => ['label' => 'النشاط',   'icon' => '📋'],
                    'projects'   => ['label' => 'المشاريع', 'icon' => '📁', 'badge' => $projects->count()],
                    'invoices'   => ['label' => 'الفواتير', 'icon' => '🧾', 'badge' => $clientInvoices->count()],
                    'quotes'     => ['label' => 'عروض الأسعار', 'icon' => '📋', 'badge' => $clientQuotes->count()],
                    'followups'  => ['label' => 'المتابعات','icon' => '⏰'],
                    'tags'       => ['label' => 'الوسوم',    'icon' => '🏷️', 'badge' => $client->tags->count()],
                    'info'       => ['label' => 'المعلومات','icon' => '📝'],
                ];
            @endphp
            @foreach($tabs as $key => $tab)
            <button @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}'
                        ? 'border-b-2 border-indigo-600 text-indigo-600 font-medium'
                        : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-2.5 text-sm whitespace-nowrap transition flex items-center gap-1.5">
                <span>{{ $tab['icon'] }}</span>
                {{ $tab['label'] }}
                @if(!empty($tab['badge']) && $tab['badge'] > 0)
                <span class="inline-flex items-center justify-center w-4 h-4 text-xs bg-indigo-100 text-indigo-700 rounded-full">{{ $tab['badge'] }}</span>
                @endif
            </button>
            @endforeach
        </div>

        {{-- ==================== تبويب النشاط ==================== --}}
        <div x-show="tab === 'activity'" class="pt-4" x-on:click.away="" x-init="loadTimeline(false)">
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700">سجل النشاط</h3>
                    <button x-on:click="loadTimeline(false)"
                            class="text-xs text-indigo-600 hover:underline flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        تحديث
                    </button>
                </div>
                <div id="timeline-container">
                    <div class="flex items-center justify-center py-8 text-gray-400">
                        <svg class="w-5 h-5 animate-spin ml-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        جاري التحميل…
                    </div>
                </div>
                {{-- زر تحميل المزيد --}}
                <div x-show="timelineHasMore" class="mt-2 text-center">
                    <button id="load-more-btn"
                            x-on:click="loadTimeline(true)"
                            :disabled="timelineLoading"
                            class="text-xs text-indigo-600 hover:text-indigo-800 px-4 py-2 border border-indigo-200
                                   rounded-lg hover:bg-indigo-50 transition disabled:opacity-50">
                        <span x-show="!timelineLoading">تحميل المزيد</span>
                        <span x-show="timelineLoading">جاري التحميل…</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ==================== تبويب المشاريع ==================== --}}
        <div x-show="tab === 'projects'" class="pt-4">
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700">
                        المشاريع ({{ $projects->count() }})
                    </h3>
                    <a href="{{ route('projects.create') }}"
                       class="inline-flex items-center gap-1.5 text-xs text-indigo-600 hover:text-indigo-800 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        مشروع جديد
                    </a>
                </div>

                @if($projects->isEmpty())
                <div class="flex flex-col items-center justify-center py-10 text-gray-400">
                    <svg class="w-10 h-10 mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                    </svg>
                    <p class="text-sm">لا توجد مشاريع مرتبطة بهذا العميل</p>
                    <a href="{{ route('projects.create') }}"
                       class="mt-3 text-xs text-indigo-600 hover:underline">إضافة مشروع</a>
                </div>
                @else
                <div class="space-y-3">
                    @foreach($projects as $project)
                    @php
                        $profit = $project->netProfit();
                    @endphp
                    <a href="{{ route('projects.show', $project) }}"
                       class="flex items-center justify-between p-3 rounded-xl border border-gray-100
                              hover:border-indigo-200 hover:bg-indigo-50/30 transition group">
                        <div class="flex items-center gap-3 min-w-0">
                            {{-- لون المشروع --}}
                            <span class="w-3 h-3 rounded-full flex-shrink-0"
                                  style="background-color: {{ $project->color ?? '#6366f1' }}"></span>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate group-hover:text-indigo-700">
                                    {{ $project->name }}
                                </p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    @if($project->type)
                                    <span class="text-xs text-gray-400">
                                        {{ $project->type->label() ?? $project->type->value }}
                                    </span>
                                    @endif
                                    <span class="text-xs {{ $project->is_active ? 'text-teal-600' : 'text-gray-400' }}">
                                        {{ $project->is_active ? '● نشط' : '○ منتهي' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 flex-shrink-0 text-left">
                            @if($project->contract_value)
                            <div class="text-right">
                                <p class="text-xs text-gray-400">قيمة العقد</p>
                                <p class="text-sm font-semibold text-gray-700">
                                    {{ number_format($project->contract_value, 0) }} ₪
                                </p>
                            </div>
                            @endif
                            @if($profit != 0)
                            <div class="text-right">
                                <p class="text-xs text-gray-400">الربح</p>
                                <p class="text-sm font-semibold {{ $profit >= 0 ? 'text-teal-600' : 'text-red-500' }}">
                                    {{ number_format(abs($profit), 0) }} ₪
                                </p>
                            </div>
                            @endif
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-indigo-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </div>
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- ==================== تبويب الفواتير ==================== --}}
        <div x-show="tab === 'invoices'" class="pt-4">
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700">
                        الفواتير ({{ $clientInvoices->count() }})
                    </h3>
                    <a href="{{ route('invoices.create', ['client_id' => $client->id]) }}"
                       class="inline-flex items-center gap-1.5 text-xs text-indigo-600 hover:text-indigo-800 font-medium transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        إنشاء فاتورة
                    </a>
                </div>

                @if($clientInvoices->isEmpty())
                <div class="flex flex-col items-center justify-center py-10 text-gray-400">
                    <svg class="w-10 h-10 mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-sm">لا توجد فواتير لهذا العميل</p>
                    <a href="{{ route('invoices.create', ['client_id' => $client->id]) }}"
                       class="mt-3 text-xs text-indigo-600 hover:underline">إنشاء أول فاتورة</a>
                </div>
                @else
                <div class="space-y-2">
                    @foreach($clientInvoices as $inv)
                    <a href="{{ route('invoices.show', $inv->ulid) }}"
                       class="flex items-center justify-between p-3 rounded-xl border border-gray-100
                              hover:border-indigo-200 hover:bg-indigo-50/30 transition group">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-medium text-gray-900 group-hover:text-indigo-700">
                                        {{ $inv->number }}
                                    </p>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $inv->status->badgeClass() }}">
                                        {{ $inv->status->icon() }} {{ $inv->status->label() }}
                                    </span>
                                    @if($inv->isOverdue())
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">⚠️ متأخرة</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $inv->issue_date->format('Y/m/d') }}
                                    @if($inv->project) — {{ $inv->project->name }} @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0">
                            <p class="text-sm font-semibold text-gray-800">
                                {{ number_format($inv->total, 0) }} {{ $inv->currency }}
                            </p>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-indigo-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </div>
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- ==================== تبويب عروض الأسعار ==================== --}}
        <div x-show="tab === 'quotes'" class="pt-4">
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700">
                        عروض الأسعار ({{ $clientQuotes->count() }})
                    </h3>
                    <a href="{{ route('quotes.create', ['client_id' => $client->id]) }}"
                       class="inline-flex items-center gap-1.5 text-xs text-indigo-600 hover:text-indigo-800 font-medium transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        إنشاء عرض سعر
                    </a>
                </div>

                @if($clientQuotes->isEmpty())
                <div class="flex flex-col items-center justify-center py-10 text-gray-400">
                    <div class="text-4xl mb-2">📋</div>
                    <p class="text-sm">لا توجد عروض أسعار بعد</p>
                </div>
                @else
                <div class="space-y-2">
                    @foreach($clientQuotes as $q)
                    <a href="{{ route('quotes.show', $q->ulid) }}"
                       class="flex items-center justify-between p-3 rounded-xl border border-gray-100
                              hover:border-indigo-200 hover:bg-indigo-50/30 transition group">
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-semibold text-indigo-600">{{ $q->number }}</span>
                            @if($q->title)
                                <span class="text-xs text-gray-500 truncate max-w-40">{{ $q->title }}</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-medium {{ $q->status->badgeClass() }} px-2 py-0.5 rounded-full">
                                {{ $q->status->icon() }} {{ $q->status->label() }}
                            </span>
                            <span class="text-xs font-semibold text-gray-700">
                                {{ number_format($q->total, 2) }} {{ $q->currency }}
                            </span>
                        </div>
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- ==================== تبويب المتابعات ==================== --}}
        <div x-show="tab === 'followups'" class="pt-4 space-y-4">
            {{-- إضافة متابعة --}}
            @can('update', $client)
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">إضافة متابعة جديدة</h3>
                <form method="POST"
                      action="{{ route('clients.client-follow-ups.store', $client->public_id) }}"
                      class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @csrf
                    {{-- client_id مطلوب للـ Validation --}}
                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">العنوان *</label>
                        <input type="text" name="title" required placeholder="موضوع المتابعة"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg
                                      focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">الموعد *</label>
                        <input type="datetime-local" name="due_at" required
                               min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg
                                      focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">الأولوية</label>
                        <select name="priority"
                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg
                                       focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                            <option value="1">🔴 عاجل</option>
                            <option value="2">🟠 مرتفع</option>
                            <option value="3" selected>🟡 متوسط</option>
                            <option value="4">🟢 منخفض</option>
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <button type="submit"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm
                                       font-medium rounded-lg transition">
                            إضافة متابعة
                        </button>
                    </div>
                </form>
            </div>
            @endcan

            {{-- قائمة المتابعات --}}
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">المتابعات</h3>
                @php $followUps = $client->followUps()->orderBy('due_at')->get() @endphp
                @if($followUps->isEmpty())
                <p class="text-sm text-gray-400 text-center py-6">لا توجد متابعات بعد</p>
                @else
                <div class="space-y-2">
                    @foreach($followUps as $followUp)
                    @php
                        // status هو Enum — نستخدم value للمقارنة
                        $statusVal   = $followUp->status instanceof \App\Modules\CRM\Enums\FollowUpStatus
                                          ? $followUp->status->value
                                          : (string) $followUp->status;
                        $isPast      = $followUp->due_at < now() && $statusVal === 'pending';
                        $isDone      = $statusVal === 'completed';
                        $isCancelled = $statusVal === 'cancelled';
                    @endphp
                    <div class="flex items-center justify-between p-3 rounded-lg
                                {{ $isDone ? 'bg-gray-50 opacity-60' : ($isPast ? 'bg-red-50' : 'bg-gray-50') }}">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="text-base">
                                {{ $isDone ? '✅' : ($isCancelled ? '❌' : ($isPast ? '⚠️' : '⏰')) }}
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate
                                          {{ $isDone || $isCancelled ? 'line-through text-gray-400' : '' }}">
                                    {{ $followUp->title }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $followUp->due_at->format('Y-m-d H:i') }}
                                    @if($isPast) <span class="text-red-500">(متأخرة)</span> @endif
                                </p>
                            </div>
                        </div>
                        @if(!$isDone && !$isCancelled)
                        @can('update', $client)
                        <div class="flex gap-1 flex-shrink-0">
                            <form method="POST"
                                  action="{{ route('clients.client-follow-ups.complete', [$client->public_id, $followUp->id]) }}">
                                @csrf
                                <button type="submit"
                                        class="px-2 py-1 text-xs text-teal-600 hover:bg-teal-50 border border-teal-200
                                               rounded-lg transition">
                                    إتمام
                                </button>
                            </form>
                            <form method="POST"
                                  action="{{ route('clients.client-follow-ups.cancel', [$client->public_id, $followUp->id]) }}">
                                @csrf
                                <button type="submit"
                                        class="px-2 py-1 text-xs text-gray-500 hover:bg-gray-100 border border-gray-200
                                               rounded-lg transition">
                                    إلغاء
                                </button>
                            </form>
                        </div>
                        @endcan
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- ==================== تبويب الوسوم ==================== --}}
        <div x-show="tab === 'tags'" class="pt-4 space-y-4">

            {{-- الوسوم الحالية --}}
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">الوسوم المُعيَّنة</h3>
                @if($client->tags->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    @foreach($client->tags as $tag)
                    <div class="flex items-center gap-1.5 pl-2 pr-3 py-1.5 rounded-full text-sm font-medium text-white"
                         style="background-color: {{ $tag->color ?? '#6366f1' }}">
                        @if($tag->icon)<span>{{ $tag->icon }}</span>@endif
                        <span>{{ $tag->name }}</span>
                        @can('update', $client)
                        <form method="POST" action="{{ route('clients.tags.remove', [$client->public_id, $tag->id]) }}"
                              x-on:submit.prevent="
                                fetch($el.action, {
                                    method: 'POST',
                                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                                }).then(() => window.location.reload())">
                            @csrf
                            <button type="submit" class="ml-1 opacity-70 hover:opacity-100 transition" title="إزالة الوسم">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </form>
                        @endcan
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-gray-400">لا توجد وسوم مُعيَّنة بعد.</p>
                @endif
            </div>

            {{-- إضافة وسم --}}
            @can('update', $client)
            @php $availableTags = $allTags->whereNotIn('id', $client->tags->pluck('id')); @endphp
            @if($availableTags->isNotEmpty())
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">إضافة وسم</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($availableTags as $tag)
                    <form method="POST" action="{{ route('clients.tags.assign', [$client->public_id, $tag->id]) }}"
                          x-on:submit.prevent="
                            fetch($el.action, {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                            }).then(() => window.location.reload())">
                        @csrf
                        <button type="submit"
                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm border-2 transition hover:opacity-80"
                                style="border-color: {{ $tag->color ?? '#6366f1' }}; color: {{ $tag->color ?? '#6366f1' }}">
                            @if($tag->icon)<span>{{ $tag->icon }}</span>@endif
                            {{ $tag->name }}
                        </button>
                    </form>
                    @endforeach
                </div>
            </div>
            @endif
            @endcan

            {{-- الاقتراحات الذكية --}}
            @if(!empty($tagSuggestions))
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-amber-800 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    اقتراحات ذكية
                </h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($tagSuggestions as $suggestion)
                    <form method="POST" action="{{ route('clients.tags.assign', [$client->public_id, $suggestion->id]) }}"
                          x-on:submit.prevent="
                            fetch($el.action, {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                            }).then(() => window.location.reload())">
                        @csrf
                        <button type="submit"
                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium
                                       bg-white border border-amber-300 text-amber-700 hover:bg-amber-100 transition">
                            <span class="w-2.5 h-2.5 rounded-full" style="background:{{ $suggestion->color ?? '#6366f1' }}"></span>
                            + {{ $suggestion->name }}
                        </button>
                    </form>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        {{-- ==================== تبويب المعلومات ==================== --}}
        <div x-show="tab === 'info'" class="pt-4">
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                    @php
                        // تحويل Enum إلى label نصي قابل للعرض
                        $sourceLabel = $client->source instanceof \App\Modules\CRM\Enums\ClientSource
                            ? $client->source->label()
                            : $client->source;

                        $fields = [
                            'البريد الإلكتروني' => $client->email,
                            'الهاتف'            => $client->phone,
                            'الشركة'            => $client->company,
                            'المنصب'            => $client->position,
                            'الموقع الإلكتروني' => $client->website,
                            'العنوان'           => $client->address,
                            'المدينة'           => $client->city,
                            'الدولة'            => $client->country,
                            'المصدر'            => $sourceLabel,
                            'تاريخ الإضافة'     => $client->created_at?->format('Y-m-d'),
                            'آخر تواصل'         => $client->last_contact_at?->format('Y-m-d'),
                            'آخر دفعة'          => $client->last_payment_at?->format('Y-m-d'),
                        ];
                    @endphp
                    @foreach($fields as $label => $value)
                    @if($value)
                    <div>
                        <dt class="text-xs font-medium text-gray-500">{{ $label }}</dt>
                        <dd class="mt-0.5 text-sm text-gray-900">
                            @if(str_starts_with($value ?? '', 'http'))
                            <a href="{{ $value }}" target="_blank" class="text-indigo-600 hover:underline break-all">{{ $value }}</a>
                            @else
                            {{ $value }}
                            @endif
                        </dd>
                    </div>
                    @endif
                    @endforeach

                    @if($client->notes)
                    <div class="md:col-span-2">
                        <dt class="text-xs font-medium text-gray-500">ملاحظات</dt>
                        <dd class="mt-0.5 text-sm text-gray-900 whitespace-pre-line">{{ $client->notes }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>

    </div>{{-- /tabs --}}

</div>

@push('scripts')
<script>
function clientProfile() {
    return {
        timelineCursor: null,
        timelineHasMore: false,
        timelineLoading: false,

        async loadTimeline(append) {
            const container = document.getElementById('timeline-container');
            const btn       = document.getElementById('load-more-btn');
            if (!append) {
                container.innerHTML = '<div class="flex items-center justify-center py-8 text-gray-400"><svg class="w-5 h-5 animate-spin ml-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>جاري التحميل…</div>';
            }
            this.timelineLoading = true;
            try {
                let url = '{{ route('clients.timeline', $client->public_id) }}';
                if (append && this.timelineCursor) url += '?cursor=' + this.timelineCursor;
                const res  = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();

                if (!append && (!data.data || data.data.length === 0)) {
                    container.innerHTML = '<p class="text-sm text-gray-400 text-center py-6">لا يوجد نشاط بعد</p>';
                    return;
                }

                const html = (data.data || []).map(item => `
                    <div class="flex gap-3 pb-4">
                        <div class="flex-shrink-0 flex flex-col items-center">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-base"
                                 style="background-color:${item.color}22; color:${item.color}">
                                ${item.icon}
                            </div>
                            <div class="w-px flex-1 bg-gray-100 mt-1"></div>
                        </div>
                        <div class="flex-1 pt-0.5 pb-2">
                            <p class="text-sm text-gray-800">${item.description}</p>
                            <p class="text-xs text-gray-400 mt-0.5">${item.actor} · ${item.occurred_ago}</p>
                        </div>
                    </div>`).join('');

                if (!append) {
                    container.innerHTML = '<div class="space-y-0">' + html + '</div>';
                } else {
                    container.querySelector('div')?.insertAdjacentHTML('beforeend', html);
                }

                this.timelineCursor  = data.next_cursor ?? null;
                this.timelineHasMore = data.has_more ?? false;
            } catch(e) {
                if (!append) container.innerHTML = '<p class="text-sm text-red-400 text-center py-6">تعذّر تحميل النشاط</p>';
            } finally {
                this.timelineLoading = false;
            }
        },
    }
}

// تحميل التلقائي عند فتح الصفحة
document.addEventListener('DOMContentLoaded', () => {
    const profile = document.querySelector('[x-data]')?.__x?.$data;
    if (profile) profile.loadTimeline(false);
});
</script>
@endpush
@endsection

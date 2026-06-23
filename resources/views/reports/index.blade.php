@extends('layouts.app')

@section('title', 'التقارير والتحليلات')

@section('content')
<div class="space-y-6">

    {{-- Header + Filters --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-ink tracking-tight">التقارير والتحليلات</h1>
            <p class="mt-1 text-sm text-muted">تحليل مالي شامل للفترة المحددة</p>

            {{-- أزرار التصدير --}}
            <div class="flex items-center gap-2 mt-3">
                @if(auth()->user()->currentPlan()->canExport())
                    {{-- PDF --}}
                    <a href="{{ route('reports.export.pdf', ['from' => $from, 'to' => $to]) }}"
                       target="_blank"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 hover:bg-red-100
                              text-red-700 text-xs font-medium rounded-lg border border-red-200 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        تصدير PDF
                    </a>

                    {{-- Excel --}}
                    <a href="{{ route('reports.export.excel', ['from' => $from, 'to' => $to]) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-50 hover:bg-green-100
                              text-green-700 text-xs font-medium rounded-lg border border-green-200 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        تصدير Excel
                    </a>
                @else
                    {{-- مستخدم Free — رسالة ترقية --}}
                    <div x-data="{ show: false }" class="relative">
                        <button @click="show = !show"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 text-slate-400
                                       text-xs font-medium rounded-lg border border-slate-200 cursor-not-allowed">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            تصدير PDF / Excel
                        </button>
                        <div x-show="show" @click.outside="show = false"
                             class="absolute top-full mt-2 right-0 bg-white border border-slate-200 rounded-xl
                                    shadow-lg p-4 w-64 z-10 text-right">
                            <p class="text-sm font-semibold text-slate-800 mb-1">ميزة مدفوعة 🔒</p>
                            <p class="text-xs text-slate-500 mb-3">
                                تصدير التقارير متاح لمشتركي <strong>Pro</strong> و<strong>Business</strong> فقط.
                            </p>
                            <a href="{{ route('billing.index') }}"
                               class="block text-center px-3 py-2 bg-brand hover:bg-brand-600
                                      text-white text-xs font-medium rounded-lg transition">
                                ترقية الخطة الآن
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Period Filter --}}
        <form method="GET" action="{{ route('reports.index') }}"
              class="flex items-center gap-2 flex-wrap">
            <input type="date" name="from" value="{{ $from }}"
                   class="filter-field w-auto">
            <span class="text-muted text-sm">—</span>
            <input type="date" name="to" value="{{ $to }}"
                   class="filter-field w-auto">
            <input type="hidden" name="cat_type" value="{{ $catType }}">
            <button type="submit"
                    class="px-4 py-2 bg-brand hover:bg-brand-600 text-white text-sm font-semibold rounded-btn transition-colors">
                تطبيق
            </button>
            {{-- Quick Year Buttons --}}
            @foreach(array_reverse($years) as $yr)
                @if($loop->index < 3)
                    <a href="{{ route('reports.index', ['from' => $yr.'-01-01', 'to' => $yr.'-12-31']) }}"
                       class="px-3 py-2 text-sm rounded-xl border transition
                              {{ substr($from,0,4) == $yr && $to >= $yr.'-12-31' ? 'border-brand bg-brand-50 text-brand-600' : 'border-slate-200 text-slate-600 hover:border-slate-300' }}">
                        {{ $yr }}
                    </a>
                @endif
            @endforeach
        </form>
    </div>

    {{-- ==================== KPI Cards ==================== --}}
    <x-stat-grid cols="4">
        <x-stats-card title="إجمالي الدخل" color="green" :value="number_format($summary['income'], 2)" prefix="+">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                </svg>
            </x-slot>
            <span class="text-xs text-muted nums">متوسط شهري: {{ number_format($summary['avg_income'], 2) }}</span>
        </x-stats-card>

        <x-stats-card title="إجمالي المصروفات" color="red" :value="number_format($summary['expenses'], 2)" prefix="-">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                </svg>
            </x-slot>
            <span class="text-xs text-muted nums">متوسط شهري: {{ number_format($summary['avg_expenses'], 2) }}</span>
        </x-stats-card>

        <x-stats-card title="صافي الربح" :color="$summary['net'] >= 0 ? 'brand' : 'red'"
                      :value="($summary['net'] >= 0 ? '+' : '').number_format($summary['net'], 2)">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-slot>
            <span class="text-xs text-muted nums">هامش الربح: {{ $summary['profit_margin'] }}%</span>
        </x-stats-card>

        <x-stats-card title="عدد المعاملات" color="brand" :value="number_format($summary['count'])">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </x-slot>
            @if($bestWorst['best'])
                <span class="text-xs text-muted truncate">أفضل: {{ $bestWorst['best']['label'] }}</span>
            @endif
        </x-stats-card>
    </x-stat-grid>

    {{-- ==================== Trend Chart ==================== --}}
    <div class="dash-card p-5">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-base font-bold text-ink">الاتجاه الشهري</h2>
                <p class="text-xs text-muted mt-0.5">دخل ومصروفات شهر بشهر</p>
            </div>
            <div class="flex items-center gap-4 text-xs text-slate-500">
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-brand inline-block"></span> دخل
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-red-400 inline-block"></span> مصروفات
                </span>
            </div>
        </div>
        <div class="relative h-64">
            <canvas id="trendChart"></canvas>
        </div>
    </div>

    {{-- ==================== Category + Projects Row ==================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Category Breakdown --}}
        <div class="dash-card p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-ink">توزيع حسب الفئة</h2>
                <div class="flex gap-1">
                    <a href="{{ route('reports.index', array_merge(request()->query(), ['cat_type' => 'expense', 'from' => $from, 'to' => $to])) }}"
                       class="px-2.5 py-1 text-xs rounded-lg transition
                              {{ $catType === 'expense' ? 'bg-red-100 text-red-700' : 'text-slate-500 hover:bg-slate-100' }}">
                        مصروفات
                    </a>
                    <a href="{{ route('reports.index', array_merge(request()->query(), ['cat_type' => 'income', 'from' => $from, 'to' => $to])) }}"
                       class="px-2.5 py-1 text-xs rounded-lg transition
                              {{ $catType === 'income' ? 'bg-green-100 text-green-700' : 'text-slate-500 hover:bg-slate-100' }}">
                        دخل
                    </a>
                </div>
            </div>

            @if($categories->isEmpty())
                <div class="py-10 text-center text-sm text-muted">لا توجد بيانات للفترة المحددة</div>
            @else
                {{-- Donut Chart --}}
                <div class="flex items-center gap-5">
                    <div class="relative w-36 h-36 shrink-0">
                        <canvas id="donutChart"></canvas>
                    </div>
                    {{-- Category List --}}
                    <div class="flex-1 space-y-2 min-w-0">
                        @php $catTotal = $categories->sum('total'); @endphp
                        @foreach($categories->take(6) as $cat)
                            @php $pct = $catTotal > 0 ? round(($cat['total'] / $catTotal) * 100, 1) : 0; @endphp
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs text-slate-700 truncate flex items-center gap-1">
                                        <span>{{ $cat['icon'] }}</span>
                                        <span>{{ $cat['name'] }}</span>
                                    </span>
                                    <span class="text-xs font-semibold text-slate-800 shrink-0 mr-2">
                                        {{ $pct }}%
                                    </span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full"
                                         style="width:{{ min($pct,100) }}%; background-color:{{ $cat['color'] }}">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Project Profitability --}}
        <div class="dash-card p-5">
            <h2 class="text-base font-semibold text-slate-900 mb-4">ربحية المشاريع</h2>

            @if($projects->isEmpty())
                <div class="py-10 text-center text-sm text-muted">لا توجد مشاريع نشطة في هذه الفترة</div>
            @else
                <div class="space-y-3">
                    @foreach($projects as $proj)
                        <div class="flex items-center gap-3">
                            <div class="w-2.5 h-2.5 rounded-full shrink-0"
                                 style="background-color:{{ $proj['color'] }}"></div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm text-slate-700 truncate font-medium">{{ $proj['name'] }}</span>
                                    <span class="text-sm font-bold shrink-0 mr-2
                                                 {{ $proj['net'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $proj['net'] >= 0 ? '+' : '' }}{{ number_format($proj['net'], 0) }}
                                    </span>
                                </div>
                                <div class="flex gap-3 text-xs text-slate-400">
                                    <span class="text-green-500">↑ {{ number_format($proj['income'], 0) }}</span>
                                    <span class="text-red-400">↓ {{ number_format($proj['expenses'], 0) }}</span>
                                    <span>{{ $proj['tx_count'] }} معاملة</span>
                                    @if($proj['income'] > 0)
                                        <span class="text-brand/70">هامش {{ $proj['margin'] }}%</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

    {{-- ==================== Category Detail Table ==================== --}}
    @if($categories->isNotEmpty())
    <div class="dash-card overflow-hidden">
        <div class="px-5 py-4 border-b border-subtle">
            <h2 class="text-base font-bold text-ink">
                تفصيل الفئات — {{ $catType === 'income' ? 'الدخل' : 'المصروفات' }}
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-5 py-3 text-right font-medium">الفئة</th>
                        <th class="px-5 py-3 text-right font-medium">عدد المعاملات</th>
                        <th class="px-5 py-3 text-right font-medium">الإجمالي</th>
                        <th class="px-5 py-3 text-right font-medium">النسبة</th>
                        <th class="px-5 py-3 text-right font-medium">التوزيع</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-subtle/70">
                    @php $catTotal = $categories->sum('total'); @endphp
                    @foreach($categories as $cat)
                        @php $pct = $catTotal > 0 ? round(($cat['total'] / $catTotal) * 100, 1) : 0; @endphp
                        <tr class="dash-row">
                            <td class="px-5 py-3">
                                <span class="flex items-center gap-2">
                                    <span>{{ $cat['icon'] }}</span>
                                    <span class="font-medium text-slate-800">{{ $cat['name'] }}</span>
                                </span>
                            </td>
                            <td class="px-5 py-3 text-slate-500">{{ $cat['count'] }}</td>
                            <td class="px-5 py-3 font-semibold
                                       {{ $catType === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($cat['total'], 2) }}
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $pct }}%</td>
                            <td class="px-5 py-3 w-40">
                                <div class="w-full bg-slate-100 rounded-full h-2">
                                    <div class="h-2 rounded-full"
                                         style="width:{{ min($pct,100) }}%; background-color:{{ $cat['color'] }}">
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 border-t border-slate-100">
                    <tr>
                        <td class="px-5 py-3 font-semibold text-slate-700">الإجمالي</td>
                        <td class="px-5 py-3 text-slate-500">{{ $categories->sum('count') }}</td>
                        <td class="px-5 py-3 font-bold
                                   {{ $catType === 'income' ? 'text-green-700' : 'text-red-700' }}">
                            {{ number_format($catTotal, 2) }}
                        </td>
                        <td class="px-5 py-3 text-slate-500">100%</td>
                        <td class="px-5 py-3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif

</div>

{{-- ==================== ربحية الخدمات ==================== --}}
@if($serviceMargins->isNotEmpty())
<div class="dash-card overflow-hidden">
    <div class="px-6 py-4 border-b border-subtle flex items-center justify-between">
        <h2 class="font-bold text-ink flex items-center gap-2">
            <span>📊</span> ربحية الخدمات
        </h2>
        <span class="text-xs text-muted">جميع المشاريع — بدون فلتر زمني</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-right text-sm">
            <thead class="bg-slate-50 text-xs font-semibold text-slate-500">
                <tr>
                    <th class="px-6 py-3">الخدمة</th>
                    <th class="px-6 py-3 text-center">المشاريع</th>
                    <th class="px-6 py-3">الإيراد</th>
                    <th class="px-6 py-3">تكلفة الفريق</th>
                    <th class="px-6 py-3">الهامش</th>
                    <th class="px-6 py-3 text-center">النسبة</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-subtle/70">
                @foreach($serviceMargins as $svc)
                @php
                    $pct = $svc['margin_pct'];
                    $badgeClass = match(true) {
                        $svc['is_loss']            => 'bg-red-100 text-red-700',
                        $pct !== null && $pct < 20  => 'bg-orange-100 text-orange-700',
                        $pct !== null && $pct < 40  => 'bg-amber-100 text-amber-700',
                        default                     => 'bg-emerald-100 text-emerald-700',
                    };
                @endphp
                <tr class="dash-row">
                    <td class="px-6 py-3.5 font-medium text-slate-900">{{ $svc['name'] }}</td>
                    <td class="px-6 py-3.5 text-center text-slate-500">{{ $svc['project_count'] }}</td>
                    <td class="px-6 py-3.5 text-slate-700">{{ number_format($svc['revenue'], 2) }}</td>
                    <td class="px-6 py-3.5 text-slate-700">
                        {{ $svc['cost'] > 0 ? number_format($svc['cost'], 2) : '—' }}
                    </td>
                    <td class="px-6 py-3.5 font-semibold {{ $svc['is_loss'] ? 'text-red-600' : 'text-slate-900' }}">
                        {{ number_format($svc['margin'], 2) }}
                    </td>
                    <td class="px-6 py-3.5 text-center">
                        @if($pct !== null)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $badgeClass }}">
                            {{ $svc['is_loss'] ? 'خسارة' : $pct . '%' }}
                        </span>
                        @else
                        <span class="text-slate-400 text-xs">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ==================== كفاءة الفريق ==================== --}}
@if($teamEfficiency->isNotEmpty())
<div class="dash-card overflow-hidden">
    <div class="px-6 py-4 border-b border-subtle">
        <h2 class="font-bold text-ink flex items-center gap-2">
            <span>👥</span> تكاليف الفريق على الخدمات
        </h2>
    </div>
    <div class="divide-y divide-subtle/70">
        @foreach($teamEfficiency as $member)
        @php
            $share = $member['cost_share_pct'];
            $barColor = match(true) {
                $share !== null && $share > 80 => 'bg-red-500',
                $share !== null && $share > 60 => 'bg-amber-400',
                default                        => 'bg-emerald-500',
            };
        @endphp
        <div class="px-6 py-4">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-brand-100 flex items-center justify-center
                                text-brand-600 font-bold text-sm flex-shrink-0">
                        {{ mb_substr($member['name'], 0, 1) }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ $member['name'] }}</p>
                        <p class="text-xs text-slate-400">{{ $member['services_count'] }} خدمة</p>
                    </div>
                </div>
                <div class="text-left">
                    <p class="text-sm font-bold text-slate-800">{{ number_format($member['total_cost'], 2) }}</p>
                    @if($share !== null)
                    <p class="text-xs text-slate-400">{{ $share }}% من إيراد خدماته</p>
                    @endif
                </div>
            </div>
            @if($share !== null)
            <div class="h-1.5 rounded-full bg-slate-100">
                <div class="{{ $barColor }} h-1.5 rounded-full transition-all"
                     style="width: {{ min($share, 100) }}%"></div>
            </div>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ==================== Chart.js Scripts ==================== --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ---- Trend Chart (Bar) ----
    const trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'bar',
            data: {
                labels: @json($trend['labels']),
                datasets: [
                    {
                        label: 'الدخل',
                        data: @json($trend['income']),
                        backgroundColor: 'rgba(99, 102, 241, 0.8)',
                        borderRadius: 6,
                        borderSkipped: false,
                    },
                    {
                        label: 'المصروفات',
                        data: @json($trend['expenses']),
                        backgroundColor: 'rgba(248, 113, 113, 0.8)',
                        borderRadius: 6,
                        borderSkipped: false,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' ' + ctx.dataset.label + ': ' + ctx.parsed.y.toLocaleString('en', {minimumFractionDigits: 2})
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: {
                            font: { size: 11 },
                            callback: v => v.toLocaleString('en')
                        }
                    }
                }
            }
        });
    }

    // ---- Donut Chart ----
    const donutCtx = document.getElementById('donutChart');
    @if($categories->isNotEmpty())
    if (donutCtx) {
        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: @json($categories->take(6)->pluck('name')),
                datasets: [{
                    data: @json($categories->take(6)->pluck('total')),
                    backgroundColor: @json($categories->take(6)->pluck('color')),
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' ' + ctx.label + ': ' + ctx.parsed.toLocaleString('en', {minimumFractionDigits: 2})
                        }
                    }
                }
            }
        });
    }
    @endif

});
</script>
@endpush

@endsection

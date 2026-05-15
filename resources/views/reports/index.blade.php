@extends('layouts.app')

@section('title', 'التقارير والتحليلات')

@section('content')
<div class="space-y-6">

    {{-- Header + Filters --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900">التقارير والتحليلات</h1>
            <p class="mt-0.5 text-sm text-gray-500">تحليل مالي شامل للفترة المحددة</p>

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
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-50 text-gray-400
                                       text-xs font-medium rounded-lg border border-gray-200 cursor-not-allowed">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            تصدير PDF / Excel
                        </button>
                        <div x-show="show" @click.outside="show = false"
                             class="absolute top-full mt-2 right-0 bg-white border border-gray-200 rounded-xl
                                    shadow-lg p-4 w-64 z-10 text-right">
                            <p class="text-sm font-semibold text-gray-800 mb-1">ميزة مدفوعة 🔒</p>
                            <p class="text-xs text-gray-500 mb-3">
                                تصدير التقارير متاح لمشتركي <strong>Pro</strong> و<strong>Business</strong> فقط.
                            </p>
                            <a href="{{ route('billing.index') }}"
                               class="block text-center px-3 py-2 bg-indigo-600 hover:bg-indigo-700
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
                   class="px-3 py-2 text-sm rounded-xl border border-gray-200
                          focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <span class="text-gray-400 text-sm">—</span>
            <input type="date" name="to" value="{{ $to }}"
                   class="px-3 py-2 text-sm rounded-xl border border-gray-200
                          focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <input type="hidden" name="cat_type" value="{{ $catType }}">
            <button type="submit"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white
                           text-sm font-medium rounded-xl transition">
                تطبيق
            </button>
            {{-- Quick Year Buttons --}}
            @foreach(array_reverse($years) as $yr)
                @if($loop->index < 3)
                    <a href="{{ route('reports.index', ['from' => $yr.'-01-01', 'to' => $yr.'-12-31']) }}"
                       class="px-3 py-2 text-sm rounded-xl border transition
                              {{ substr($from,0,4) == $yr && $to >= $yr.'-12-31' ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}">
                        {{ $yr }}
                    </a>
                @endif
            @endforeach
        </form>
    </div>

    {{-- ==================== KPI Cards ==================== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Income --}}
        <div class="bg-white border border-gray-100 rounded-2xl p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 bg-green-100 rounded-xl flex items-center justify-center text-lg">📈</div>
                <p class="text-xs text-gray-500">إجمالي الدخل</p>
            </div>
            <p class="text-xl font-bold text-green-600">
                +{{ number_format($summary['income'], 2) }}
            </p>
            <p class="text-xs text-gray-400 mt-1">
                متوسط شهري: {{ number_format($summary['avg_income'], 2) }}
            </p>
        </div>

        {{-- Expenses --}}
        <div class="bg-white border border-gray-100 rounded-2xl p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 bg-red-100 rounded-xl flex items-center justify-center text-lg">📉</div>
                <p class="text-xs text-gray-500">إجمالي المصروفات</p>
            </div>
            <p class="text-xl font-bold text-red-600">
                -{{ number_format($summary['expenses'], 2) }}
            </p>
            <p class="text-xs text-gray-400 mt-1">
                متوسط شهري: {{ number_format($summary['avg_expenses'], 2) }}
            </p>
        </div>

        {{-- Net --}}
        <div class="bg-white border border-gray-100 rounded-2xl p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 {{ $summary['net'] >= 0 ? 'bg-indigo-100' : 'bg-red-100' }} rounded-xl flex items-center justify-center text-lg">
                    {{ $summary['net'] >= 0 ? '💰' : '⚠️' }}
                </div>
                <p class="text-xs text-gray-500">صافي الربح</p>
            </div>
            <p class="text-xl font-bold {{ $summary['net'] >= 0 ? 'text-indigo-600' : 'text-red-600' }}">
                {{ $summary['net'] >= 0 ? '+' : '' }}{{ number_format($summary['net'], 2) }}
            </p>
            <p class="text-xs text-gray-400 mt-1">
                هامش الربح: {{ $summary['profit_margin'] }}%
            </p>
        </div>

        {{-- Transactions Count --}}
        <div class="bg-white border border-gray-100 rounded-2xl p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 bg-purple-100 rounded-xl flex items-center justify-center text-lg">🔢</div>
                <p class="text-xs text-gray-500">عدد المعاملات</p>
            </div>
            <p class="text-xl font-bold text-purple-600">
                {{ number_format($summary['count']) }}
            </p>
            @if($bestWorst['best'])
                <p class="text-xs text-gray-400 mt-1 truncate">
                    أفضل: {{ $bestWorst['best']['label'] }}
                </p>
            @endif
        </div>

    </div>

    {{-- ==================== Trend Chart ==================== --}}
    <div class="bg-white border border-gray-100 rounded-2xl p-5">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-base font-semibold text-gray-900">الاتجاه الشهري</h2>
                <p class="text-xs text-gray-400 mt-0.5">دخل ومصروفات شهر بشهر</p>
            </div>
            <div class="flex items-center gap-4 text-xs text-gray-500">
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-indigo-500 inline-block"></span> دخل
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
        <div class="bg-white border border-gray-100 rounded-2xl p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold text-gray-900">توزيع حسب الفئة</h2>
                <div class="flex gap-1">
                    <a href="{{ route('reports.index', array_merge(request()->query(), ['cat_type' => 'expense', 'from' => $from, 'to' => $to])) }}"
                       class="px-2.5 py-1 text-xs rounded-lg transition
                              {{ $catType === 'expense' ? 'bg-red-100 text-red-700' : 'text-gray-500 hover:bg-gray-100' }}">
                        مصروفات
                    </a>
                    <a href="{{ route('reports.index', array_merge(request()->query(), ['cat_type' => 'income', 'from' => $from, 'to' => $to])) }}"
                       class="px-2.5 py-1 text-xs rounded-lg transition
                              {{ $catType === 'income' ? 'bg-green-100 text-green-700' : 'text-gray-500 hover:bg-gray-100' }}">
                        دخل
                    </a>
                </div>
            </div>

            @if($categories->isEmpty())
                <div class="py-10 text-center text-sm text-gray-400">لا توجد بيانات للفترة المحددة</div>
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
                                    <span class="text-xs text-gray-700 truncate flex items-center gap-1">
                                        <span>{{ $cat['icon'] }}</span>
                                        <span>{{ $cat['name'] }}</span>
                                    </span>
                                    <span class="text-xs font-semibold text-gray-800 shrink-0 mr-2">
                                        {{ $pct }}%
                                    </span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5">
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
        <div class="bg-white border border-gray-100 rounded-2xl p-5">
            <h2 class="text-base font-semibold text-gray-900 mb-4">ربحية المشاريع</h2>

            @if($projects->isEmpty())
                <div class="py-10 text-center text-sm text-gray-400">لا توجد مشاريع نشطة في هذه الفترة</div>
            @else
                <div class="space-y-3">
                    @foreach($projects as $proj)
                        <div class="flex items-center gap-3">
                            <div class="w-2.5 h-2.5 rounded-full shrink-0"
                                 style="background-color:{{ $proj['color'] }}"></div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm text-gray-700 truncate font-medium">{{ $proj['name'] }}</span>
                                    <span class="text-sm font-bold shrink-0 mr-2
                                                 {{ $proj['net'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $proj['net'] >= 0 ? '+' : '' }}{{ number_format($proj['net'], 0) }}
                                    </span>
                                </div>
                                <div class="flex gap-3 text-xs text-gray-400">
                                    <span class="text-green-500">↑ {{ number_format($proj['income'], 0) }}</span>
                                    <span class="text-red-400">↓ {{ number_format($proj['expenses'], 0) }}</span>
                                    <span>{{ $proj['tx_count'] }} معاملة</span>
                                    @if($proj['income'] > 0)
                                        <span class="text-indigo-400">هامش {{ $proj['margin'] }}%</span>
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
    <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50">
            <h2 class="text-base font-semibold text-gray-900">
                تفصيل الفئات — {{ $catType === 'income' ? 'الدخل' : 'المصروفات' }}
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-5 py-3 text-right font-medium">الفئة</th>
                        <th class="px-5 py-3 text-right font-medium">عدد المعاملات</th>
                        <th class="px-5 py-3 text-right font-medium">الإجمالي</th>
                        <th class="px-5 py-3 text-right font-medium">النسبة</th>
                        <th class="px-5 py-3 text-right font-medium">التوزيع</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @php $catTotal = $categories->sum('total'); @endphp
                    @foreach($categories as $cat)
                        @php $pct = $catTotal > 0 ? round(($cat['total'] / $catTotal) * 100, 1) : 0; @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3">
                                <span class="flex items-center gap-2">
                                    <span>{{ $cat['icon'] }}</span>
                                    <span class="font-medium text-gray-800">{{ $cat['name'] }}</span>
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-500">{{ $cat['count'] }}</td>
                            <td class="px-5 py-3 font-semibold
                                       {{ $catType === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($cat['total'], 2) }}
                            </td>
                            <td class="px-5 py-3 text-gray-600">{{ $pct }}%</td>
                            <td class="px-5 py-3 w-40">
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="h-2 rounded-full"
                                         style="width:{{ min($pct,100) }}%; background-color:{{ $cat['color'] }}">
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-100">
                    <tr>
                        <td class="px-5 py-3 font-semibold text-gray-700">الإجمالي</td>
                        <td class="px-5 py-3 text-gray-500">{{ $categories->sum('count') }}</td>
                        <td class="px-5 py-3 font-bold
                                   {{ $catType === 'income' ? 'text-green-700' : 'text-red-700' }}">
                            {{ number_format($catTotal, 2) }}
                        </td>
                        <td class="px-5 py-3 text-gray-500">100%</td>
                        <td class="px-5 py-3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif

</div>

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

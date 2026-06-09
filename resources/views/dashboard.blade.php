@extends('layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
<div class="space-y-6">

    {{-- Onboarding Widget — يظهر فقط للمستخدمين الجدد --}}
    @if($showOnboarding)
        <x-onboarding-widget
            :steps="$onboardingSteps"
            :progress="$onboardingProgress"
            :completed="$onboardingCompleted"
            :total="$onboardingTotal"
        />
    @endif

    {{-- Welcome + Date --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">
                مرحباً، {{ explode(' ', auth()->user()->name)[0] }} 👋
            </h1>
            <p class="mt-0.5 text-sm text-gray-500">{{ now()->translatedFormat('l، d F Y') }}</p>
        </div>
        <a href="{{ route('transactions.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-white text-sm font-medium rounded-xl transition"
           style="background: #320E8E;" onmouseover="this.style.background='#26096e'" onmouseout="this.style.background='#320E8E'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            معاملة جديدة
        </a>
    </div>

    {{-- Wallets + Pending Invoices --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        {{-- إجمالي رصيد الصناديق --}}
        <a href="{{ route('wallets.index') }}"
           class="bg-gradient-to-l from-indigo-600 to-indigo-500 rounded-2xl p-5 hover:from-indigo-700 hover:to-indigo-600 transition group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-indigo-200">إجمالي رصيد الصناديق</p>
                    <p class="mt-1.5 text-3xl font-bold text-white">
                        {{ number_format($walletsSummary['total'], 2) }}
                    </p>
                    <p class="mt-1 text-xs text-indigo-200">{{ $walletsSummary['count'] }} {{ $walletsSummary['count'] == 1 ? 'صندوق' : 'صناديق' }} نشطة</p>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center shrink-0 group-hover:bg-white/30 transition">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
            </div>
        </a>

        {{-- الفواتير المعلّقة --}}
        <a href="{{ route('invoices.index') }}"
           class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-sm transition group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500">فواتير معلّقة</p>
                    <p class="mt-1.5 text-3xl font-bold text-gray-900">{{ $pendingInvoices['count'] }}</p>
                    <p class="mt-1 text-xs text-gray-400">
                        بقيمة {{ number_format($pendingInvoices['total'], 2) }}
                        @if($pendingInvoices['overdue'] > 0)
                            · <span class="text-red-500 font-medium">{{ $pendingInvoices['overdue'] }} متأخرة</span>
                        @endif
                    </p>
                </div>
                <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            @if($pendingInvoices['count'] == 0)
                <p class="mt-3 text-xs text-green-600 font-medium">✓ لا توجد فواتير معلّقة</p>
            @else
                <p class="mt-3 text-xs text-indigo-600 font-medium group-hover:underline">عرض الفواتير ←</p>
            @endif
        </a>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-sm transition">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500">دخل الشهر</p>
                    <p class="mt-1.5 text-2xl font-bold text-gray-900">+{{ number_format($kpis['income']['value'], 2) }}</p>
                </div>
                <div class="w-11 h-11 bg-green-50 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                    </svg>
                </div>
            </div>
            @if($kpis['income']['change'] !== null)
            <div class="mt-3 flex items-center gap-1.5">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                    {{ $kpis['income']['change'] >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $kpis['income']['change'] >= 0 ? '↑' : '↓' }} {{ abs($kpis['income']['change']) }}%
                </span>
                <span class="text-xs text-gray-400">عن الشهر الماضي</span>
            </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-sm transition">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500">مصروفات الشهر</p>
                    <p class="mt-1.5 text-2xl font-bold text-gray-900">-{{ number_format($kpis['expenses']['value'], 2) }}</p>
                </div>
                <div class="w-11 h-11 bg-red-50 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                    </svg>
                </div>
            </div>
            @if($kpis['expenses']['change'] !== null)
            <div class="mt-3 flex items-center gap-1.5">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                    {{ $kpis['expenses']['change'] <= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $kpis['expenses']['change'] >= 0 ? '↑' : '↓' }} {{ abs($kpis['expenses']['change']) }}%
                </span>
                <span class="text-xs text-gray-400">عن الشهر الماضي</span>
            </div>
            @endif
        </div>

        @php $net = $kpis['net']['value']; $inc = $kpis['income']['value']; $pct = $inc > 0 ? min(round(($kpis['expenses']['value'] / $inc) * 100), 100) : 0; @endphp
        <div class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-sm transition">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500">صافي الشهر</p>
                    <p class="mt-1.5 text-2xl font-bold {{ $net >= 0 ? 'text-indigo-700' : 'text-red-700' }}">
                        {{ $net >= 0 ? '+' : '' }}{{ number_format($net, 2) }}
                    </p>
                </div>
                <div class="w-11 h-11 {{ $net >= 0 ? 'bg-indigo-50' : 'bg-red-50' }} rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 {{ $net >= 0 ? 'text-indigo-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="w-full bg-gray-100 rounded-full h-1.5">
                    <div class="h-1.5 rounded-full {{ $pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-yellow-500' : 'bg-green-500') }}"
                         style="width: {{ $pct }}%"></div>
                </div>
                <p class="text-xs text-gray-400 mt-1">{{ $pct }}% من الدخل مصاريف</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-sm transition">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500">المشاريع النشطة</p>
                    <p class="mt-1.5 text-2xl font-bold text-gray-900">{{ $kpis['projects_active']['value'] }}</p>
                </div>
                <div class="w-11 h-11 bg-purple-50 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <a href="{{ route('projects.index') }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">
                    عرض جميع المشاريع ←
                </a>
            </div>
        </div>
    </div>

    {{-- Chart + Debts --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-900">التدفق النقدي — آخر 6 أشهر</h2>
                <div class="flex items-center gap-3 text-xs text-gray-500">
                    <span class="flex items-center gap-1"><span class="w-3 h-1 bg-green-500 rounded inline-block"></span> دخل</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-1 bg-red-400 rounded inline-block"></span> مصروف</span>
                </div>
            </div>
            <canvas id="cashFlowChart" height="180"></canvas>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">ديون مستحقة قريباً</h2>
                <a href="{{ route('debts.index') }}" class="text-xs text-indigo-600 hover:text-indigo-700">الكل</a>
            </div>
            @if($debtsDue->isEmpty())
                <div class="py-6 text-center">
                    <p class="text-2xl mb-1">✅</p>
                    <p class="text-xs text-gray-400">لا توجد ديون مستحقة</p>
                </div>
            @else
                <ul class="divide-y divide-gray-50">
                    @foreach($debtsDue as $debt)
                    <li class="px-5 py-3.5 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0
                                    {{ $debt->type->value === 'borrowed' ? 'bg-red-100' : 'bg-green-100' }}">
                            <span class="text-sm">{{ $debt->type->value === 'borrowed' ? '⬆' : '⬇' }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $debt->party_name }}</p>
                            <p class="text-xs text-gray-400">{{ $debt->due_date->format('d/m/Y') }}</p>
                        </div>
                        <span class="text-sm font-bold text-gray-700 shrink-0">{{ number_format($debt->remaining_amount, 0) }}</span>
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- Recent + Projects --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">آخر المعاملات</h2>
                <a href="{{ route('transactions.index') }}" class="text-xs text-indigo-600 hover:text-indigo-700">عرض الكل</a>
            </div>
            @if($recent->isEmpty())
                <div class="py-12">
                    <x-empty-state title="لا توجد معاملات بعد" description="ابدأ بإضافة معاملتك الأولى"
                        :action="route('transactions.create')" actionLabel="إضافة معاملة"/>
                </div>
            @else
                <div class="divide-y divide-gray-50">
                    @foreach($recent as $tx)
                    <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50 transition">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0
                                    {{ $tx->isIncome() ? 'bg-green-100' : 'bg-red-100' }}">
                            <svg class="w-4 h-4 {{ $tx->isIncome() ? 'text-green-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($tx->isIncome())
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                                @endif
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $tx->description }}</p>
                            <p class="text-xs text-gray-400">{{ $tx->category?->icon }} {{ $tx->category?->name ?? 'بدون فئة' }} · {{ $tx->transaction_date->format('d/m/Y') }}</p>
                        </div>
                        @if($tx->project)
                        <span class="hidden sm:flex items-center gap-1 text-xs text-gray-500 shrink-0">
                            <span class="w-2 h-2 rounded-full" style="background-color:{{ $tx->project->color }}"></span>
                            {{ Str::limit($tx->project->name, 12) }}
                        </span>
                        @endif
                        <span class="text-sm font-bold {{ $tx->isIncome() ? 'text-green-600' : 'text-red-600' }} shrink-0">
                            {{ $tx->isIncome() ? '+' : '-' }}{{ number_format($tx->amount, 2) }}
                        </span>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">المشاريع النشطة</h2>
                <a href="{{ route('projects.index') }}" class="text-xs text-indigo-600 hover:text-indigo-700">الكل</a>
            </div>
            @if($projects->isEmpty())
                <div class="py-8 text-center px-4">
                    <p class="text-2xl mb-2">📁</p>
                    <p class="text-sm text-gray-500 mb-3">لا توجد مشاريع نشطة</p>
                    <a href="{{ route('projects.create') }}" class="text-xs text-indigo-600 font-medium">إنشاء مشروع ←</a>
                </div>
            @else
                <ul class="divide-y divide-gray-50">
                    @foreach($projects as $project)
                    <li class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-base shrink-0"
                                 style="background-color: {{ $project->color }}1A">
                                {{ $project->type->icon() }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('projects.show', $project) }}"
                                   class="text-sm font-medium text-gray-900 hover:text-indigo-600 truncate block">
                                    {{ $project->name }}
                                </a>
                                <p class="text-xs text-gray-400">{{ $project->transactions_count }} معاملة</p>
                            </div>
                        </div>
                        @php $pnet = $project->netProfit(); @endphp
                        <div class="mt-2 flex items-center justify-between text-xs">
                            <span class="text-gray-400">الصافي</span>
                            <span class="font-bold {{ $pnet >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $pnet >= 0 ? '+' : '' }}{{ number_format($pnet, 0) }} {{ $project->currency }}
                            </span>
                        </div>
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>

    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('cashFlowChart');
    if (!ctx) return;

    const income   = @json($chart['income']);
    const expenses = @json($chart['expenses']);
    const labels   = @json($chart['months']);

    // أشهر فيها بيانات فعلية
    const hasData = income.some(v => v > 0) || expenses.some(v => v > 0);

    if (!hasData) {
        ctx.closest('div').insertAdjacentHTML('beforeend',
            '<div class="absolute inset-0 flex flex-col items-center justify-center bg-white/80 rounded-xl">' +
            '<p class="text-2xl mb-1">📊</p>' +
            '<p class="text-sm text-gray-400">أضف معاملات لرؤية التدفق النقدي</p></div>'
        );
        ctx.closest('div').style.position = 'relative';
    }

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'دخل',    data: income,   backgroundColor: 'rgba(20,198,152,0.85)', borderRadius: 6, borderSkipped: false },
                { label: 'مصروف', data: expenses, backgroundColor: 'rgba(239,68,68,0.65)',  borderRadius: 6, borderSkipped: false }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { family: 'Tajawal' } } },
                y: {
                    grid: { color: '#f3f4f6' },
                    ticks: { font: { family: 'Tajawal' }, callback: v => v.toLocaleString() },
                    beginAtZero: true,
                }
            }
        }
    });
});
</script>
@endsection

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
    <div class="flex items-center justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl font-bold text-ink truncate tracking-tight">
                مرحباً، {{ explode(' ', auth()->user()->name)[0] }} <span class="inline-block">👋</span>
            </h1>
            <p class="mt-1 text-sm text-muted">{{ now()->translatedFormat('l، d F Y') }}</p>
        </div>
        <a href="{{ route('transactions.create') }}"
           class="group inline-flex items-center gap-2 px-5 py-2.5 bg-brand hover:bg-brand-600 text-white text-sm font-semibold rounded-btn shadow-card hover:shadow-card-hover transition-all duration-150 shrink-0">
            <svg class="w-4 h-4 transition-transform group-hover:rotate-90 duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            معاملة جديدة
        </a>
    </div>

    {{-- Wallets + Pending Invoices --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        {{-- إجمالي رصيد الصناديق — لحظة الطاقة الوحيدة بالصفحة (gradient brand→accent مقصود) --}}
        <a href="{{ route('wallets.index') }}"
           class="group relative overflow-hidden rounded-2xl p-5 shadow-card hover:shadow-card-hover transition-shadow duration-200"
           style="background: linear-gradient(125deg, #180645 0%, #310E8E 55%, #0C8567 130%);">
            {{-- توهّج تركوازي زاوي --}}
            <div class="pointer-events-none absolute -top-16 -left-12 w-48 h-48 rounded-full bg-accent/25 blur-3xl group-hover:bg-accent/35 transition-colors duration-300"></div>
            {{-- شبكة نقطية خفيفة للعمق --}}
            <div class="pointer-events-none absolute inset-0 opacity-[0.07]"
                 style="background-image: radial-gradient(circle, #fff 1px, transparent 1px); background-size: 16px 16px;"></div>
            <div class="relative flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-[13px] font-medium text-white/75">إجمالي رصيد الصناديق</p>
                    <p class="mt-2.5 text-[32px] leading-none font-bold text-white nums tracking-tight">
                        {{ number_format($walletsSummary['total'], 2) }}
                    </p>
                    <p class="mt-2.5 inline-flex items-center gap-1.5 text-xs text-white/70">
                        <span class="w-1.5 h-1.5 rounded-full bg-accent shadow-[0_0_8px_2px_rgba(19,197,151,0.6)]"></span>
                        {{ $walletsSummary['count'] }} {{ $walletsSummary['count'] == 1 ? 'صندوق' : 'صناديق' }} نشطة
                    </p>
                </div>
                <div class="w-12 h-12 bg-white/15 backdrop-blur-sm rounded-xl flex items-center justify-center shrink-0 ring-1 ring-white/15 group-hover:bg-white/25 transition-colors">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
            </div>
        </a>

        {{-- الفواتير المعلّقة --}}
        <a href="{{ route('invoices.index') }}"
           class="dash-card dash-card-hover p-5 group">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-[13px] font-medium text-muted">فواتير معلّقة</p>
                    <p class="mt-2 text-[28px] leading-none font-bold text-ink nums tracking-tight">{{ $pendingInvoices['count'] }}</p>
                    <p class="mt-2 text-xs text-muted nums">
                        بقيمة {{ number_format($pendingInvoices['total'], 2) }}
                        @if($pendingInvoices['overdue'] > 0)
                            · <span class="text-red-600 font-semibold">{{ $pendingInvoices['overdue'] }} متأخرة</span>
                        @endif
                    </p>
                </div>
                <div class="w-11 h-11 bg-amber-50 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            @if($pendingInvoices['count'] == 0)
                <p class="mt-3 inline-flex items-center gap-1 text-xs text-success-700 font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    لا توجد فواتير معلّقة
                </p>
            @else
                <p class="mt-3 text-xs text-brand font-semibold group-hover:underline">عرض الفواتير ←</p>
            @endif
        </a>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- دخل الشهر --}}
        <div class="dash-card dash-card-hover p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-[13px] font-medium text-muted">دخل الشهر</p>
                    <p class="mt-2 text-2xl leading-none font-bold text-ink nums tracking-tight">+{{ number_format($kpis['income']['value'], 2) }}</p>
                </div>
                <div class="w-11 h-11 bg-success-soft rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-success-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                    </svg>
                </div>
            </div>
            @if($kpis['income']['change'] !== null)
            <div class="mt-3 flex items-center gap-1.5">
                <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-xs font-semibold nums
                    {{ $kpis['income']['change'] >= 0 ? 'bg-success-soft text-success-700' : 'bg-error-soft text-red-700' }}">
                    {{ $kpis['income']['change'] >= 0 ? '↑' : '↓' }} {{ abs($kpis['income']['change']) }}%
                </span>
                <span class="text-xs text-muted">عن الشهر الماضي</span>
            </div>
            @endif
        </div>

        {{-- مصروفات الشهر --}}
        <div class="dash-card dash-card-hover p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-[13px] font-medium text-muted">مصروفات الشهر</p>
                    <p class="mt-2 text-2xl leading-none font-bold text-ink nums tracking-tight">-{{ number_format($kpis['expenses']['value'], 2) }}</p>
                </div>
                <div class="w-11 h-11 bg-error-soft rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                    </svg>
                </div>
            </div>
            @if($kpis['expenses']['change'] !== null)
            <div class="mt-3 flex items-center gap-1.5">
                <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-xs font-semibold nums
                    {{ $kpis['expenses']['change'] <= 0 ? 'bg-success-soft text-success-700' : 'bg-error-soft text-red-700' }}">
                    {{ $kpis['expenses']['change'] >= 0 ? '↑' : '↓' }} {{ abs($kpis['expenses']['change']) }}%
                </span>
                <span class="text-xs text-muted">عن الشهر الماضي</span>
            </div>
            @endif
        </div>

        {{-- صافي الشهر --}}
        @php $net = $kpis['net']['value']; $inc = $kpis['income']['value']; $pct = $inc > 0 ? min(round(($kpis['expenses']['value'] / $inc) * 100), 100) : 0; @endphp
        <div class="dash-card dash-card-hover p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-[13px] font-medium text-muted">صافي الشهر</p>
                    <p class="mt-2 text-2xl leading-none font-bold nums tracking-tight {{ $net >= 0 ? 'text-brand' : 'text-red-700' }}">
                        {{ $net >= 0 ? '+' : '' }}{{ number_format($net, 2) }}
                    </p>
                </div>
                <div class="w-11 h-11 {{ $net >= 0 ? 'bg-brand-50' : 'bg-error-soft' }} rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 {{ $net >= 0 ? 'text-brand' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="h-1.5 rounded-full {{ $pct >= 90 ? 'bg-error' : ($pct >= 70 ? 'bg-amber-500' : 'bg-accent') }}"
                         style="width: {{ $pct }}%"></div>
                </div>
                <p class="text-xs text-muted mt-1.5 nums">{{ $pct }}% من الدخل مصاريف</p>
            </div>
        </div>

        {{-- المشاريع النشطة --}}
        <div class="dash-card dash-card-hover p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-[13px] font-medium text-muted">المشاريع النشطة</p>
                    <p class="mt-2 text-2xl leading-none font-bold text-ink nums tracking-tight">{{ $kpis['projects_active']['value'] }}</p>
                </div>
                <div class="w-11 h-11 bg-accent-50 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-accent-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <a href="{{ route('projects.index') }}" class="text-xs text-brand hover:text-brand-600 font-semibold">
                    عرض جميع المشاريع ←
                </a>
            </div>
        </div>
    </div>

    {{-- Chart + Debts --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <div class="lg:col-span-2 dash-card p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-ink">التدفق النقدي — آخر 6 أشهر</h2>
                <div class="flex items-center gap-3 text-xs text-muted">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 bg-accent rounded-sm inline-block"></span> دخل</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 bg-red-400 rounded-sm inline-block"></span> مصروف</span>
                </div>
            </div>
            <div class="relative">
                <canvas id="cashFlowChart" height="180"></canvas>
            </div>
        </div>

        <div class="dash-card overflow-hidden">
            <div class="px-5 py-4 border-b border-subtle flex items-center justify-between">
                <h2 class="font-semibold text-ink">ديون مستحقة قريباً</h2>
                <a href="{{ route('debts.index') }}" class="text-xs text-brand hover:text-brand-600 font-medium">الكل</a>
            </div>
            @if($debtsDue->isEmpty())
                <div class="py-10 text-center">
                    <div class="w-12 h-12 mx-auto bg-success-soft rounded-2xl flex items-center justify-center mb-2">
                        <svg class="w-6 h-6 text-success-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <p class="text-xs text-muted">لا توجد ديون مستحقة</p>
                </div>
            @else
                <ul class="divide-y divide-subtle">
                    @foreach($debtsDue as $debt)
                    <li class="px-5 py-3.5 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0
                                    {{ $debt->type->value === 'borrowed' ? 'bg-error-soft text-red-600' : 'bg-success-soft text-success-700' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($debt->type->value === 'borrowed')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                                @endif
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-ink truncate">{{ $debt->party_name }}</p>
                            <p class="text-xs text-muted nums">{{ $debt->due_date->format('d/m/Y') }}</p>
                        </div>
                        <span class="text-sm font-bold text-ink shrink-0 nums">{{ number_format($debt->remaining_amount, 0) }}</span>
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- Recent + Projects --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <div class="lg:col-span-2 dash-card overflow-hidden">
            <div class="px-5 py-4 border-b border-subtle flex items-center justify-between">
                <h2 class="font-semibold text-ink">آخر المعاملات</h2>
                <a href="{{ route('transactions.index') }}" class="text-xs text-brand hover:text-brand-600 font-medium">عرض الكل</a>
            </div>
            @if($recent->isEmpty())
                <div class="py-12">
                    <x-empty-state title="لا توجد معاملات بعد" description="ابدأ بإضافة معاملتك الأولى"
                        :action="route('transactions.create')" actionLabel="إضافة معاملة"/>
                </div>
            @else
                <div class="divide-y divide-subtle">
                    @foreach($recent as $tx)
                    <div class="flex items-center gap-4 px-5 py-3.5 dash-row">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0
                                    {{ $tx->isIncome() ? 'bg-success-soft text-success-700' : 'bg-error-soft text-red-600' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($tx->isIncome())
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                                @endif
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-ink truncate">{{ $tx->description }}</p>
                            <p class="text-xs text-muted truncate">{{ $tx->category?->icon }} {{ $tx->category?->name ?? 'بدون فئة' }} · {{ $tx->transaction_date->format('d/m/Y') }}</p>
                        </div>
                        @if($tx->project)
                        <span class="hidden sm:flex items-center gap-1.5 text-xs text-muted shrink-0">
                            <span class="w-2 h-2 rounded-full" style="background-color:{{ $tx->project->color }}"></span>
                            {{ Str::limit($tx->project->name, 12) }}
                        </span>
                        @endif
                        <span class="text-sm font-bold shrink-0 nums {{ $tx->isIncome() ? 'text-success-700' : 'text-red-600' }}">
                            {{ $tx->isIncome() ? '+' : '-' }}{{ number_format($tx->amount, 2) }}
                        </span>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="dash-card overflow-hidden">
            <div class="px-5 py-4 border-b border-subtle flex items-center justify-between">
                <h2 class="font-semibold text-ink">المشاريع النشطة</h2>
                <a href="{{ route('projects.index') }}" class="text-xs text-brand hover:text-brand-600 font-medium">الكل</a>
            </div>
            @if($projects->isEmpty())
                <div class="py-10 text-center px-4">
                    <div class="w-12 h-12 mx-auto bg-brand-50 rounded-2xl flex items-center justify-center mb-2">
                        <svg class="w-6 h-6 text-brand/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    </div>
                    <p class="text-sm text-muted mb-3">لا توجد مشاريع نشطة</p>
                    <a href="{{ route('projects.create') }}" class="text-xs text-brand font-semibold">إنشاء مشروع ←</a>
                </div>
            @else
                <ul class="divide-y divide-subtle">
                    @foreach($projects as $project)
                    <li class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-base shrink-0"
                                 style="background-color: {{ $project->color }}1A">
                                {{ $project->type->icon() }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('projects.show', $project) }}"
                                   class="text-sm font-medium text-ink hover:text-brand truncate block">
                                    {{ $project->name }}
                                </a>
                                <p class="text-xs text-muted nums">{{ $project->transactions_count }} معاملة</p>
                            </div>
                        </div>
                        @php $pnet = $project->netProfit(); @endphp
                        <div class="mt-2 flex items-center justify-between text-xs">
                            <span class="text-muted">الصافي</span>
                            <span class="font-bold nums {{ $pnet >= 0 ? 'text-success-700' : 'text-red-600' }}">
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
        const wrap = ctx.closest('.relative');
        if (wrap) {
            wrap.insertAdjacentHTML('beforeend',
                '<div class="absolute inset-0 flex flex-col items-center justify-center bg-white/80 rounded-xl">' +
                '<p class="text-2xl mb-1">📊</p>' +
                '<p class="text-sm text-muted">أضف معاملات لرؤية التدفق النقدي</p></div>'
            );
        }
    }

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'دخل',    data: income,   backgroundColor: 'rgba(19,197,151,0.85)', borderRadius: 6, borderSkipped: false },
                { label: 'مصروف', data: expenses, backgroundColor: 'rgba(239,68,68,0.6)',   borderRadius: 6, borderSkipped: false }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { family: 'Readex Pro' } } },
                y: {
                    grid: { color: '#EEF1F6' },
                    ticks: { font: { family: 'Readex Pro' }, callback: v => v.toLocaleString() },
                    beginAtZero: true,
                }
            }
        }
    });
});
</script>
@endsection

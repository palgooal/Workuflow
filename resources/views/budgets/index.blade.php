@extends('layouts.app')
@section('title', 'الميزانيات')

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <x-page-header title="الميزانيات" subtitle="تتبع إنفاقك مقابل ميزانياتك المحددة">
        <x-slot name="actions">
            <a href="{{ route('budget.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand text-white rounded-btn font-semibold hover:bg-brand-600 transition-colors text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                ميزانية جديدة
            </a>
        </x-slot>
    </x-page-header>

    {{-- Flash --}}
    @if(session('success'))
        <div class="bg-success-soft border border-success/30 text-success-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('budget.index') }}"
          class="dash-card flex flex-wrap items-center gap-3 px-4 py-3">
        <div class="flex items-center gap-2 text-muted shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="text-sm font-medium text-slate-600">عرض فترة:</span>
        </div>
        <select name="month" class="filter-field w-auto py-2">
            @foreach($months as $num => $name)
                <option value="{{ $num }}" @selected($num == $month)>{{ $name }}</option>
            @endforeach
        </select>
        <select name="year" class="filter-field w-auto py-2">
            @foreach($years as $y)
                <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
            @endforeach
        </select>
        <button type="submit"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand text-white text-sm font-semibold rounded-btn hover:bg-brand-600 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            </svg>
            تطبيق
        </button>
    </form>

    {{-- Summary Cards --}}
    <x-stat-grid cols="4">
        <x-stats-card title="إجمالي الميزانيات" color="brand" :value="$summary['total']">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </x-slot>
        </x-stats-card>
        <x-stats-card title="تجاوزت الحد" color="red" :value="$summary['over']">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.74-2.99l-6.93-12a2 2 0 00-3.48 0l-6.93 12A2 2 0 005.07 19z"/>
                </svg>
            </x-slot>
        </x-stats-card>
        <x-stats-card title="قريبة من الحد" color="yellow" :value="$summary['warning']">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-slot>
        </x-stats-card>
        <x-stats-card title="ضمن الحد" color="green" :value="$summary['ok']">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-slot>
        </x-stats-card>
    </x-stat-grid>

    {{-- Budget Cards --}}
    @if($budgets->isEmpty())
        <div class="dash-card py-16">
            <x-empty-state
                title="لا توجد ميزانيات لهذه الفترة"
                description="أنشئ ميزانية لتتبع إنفاقك"
                :action="route('budget.create')"
                actionLabel="إنشاء ميزانية" />
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($budgets as $budget)
                @php
                    $pct    = min($budget->usage_percentage, 100);
                    $status = match($budget->status) {
                        'over'    => [
                            'bar'   => 'bg-red-500',
                            'badge' => 'bg-error-soft text-red-700',
                            'label' => 'تجاوزت الحد',
                            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.74-2.99l-6.93-12a2 2 0 00-3.48 0l-6.93 12A2 2 0 005.07 19z"/>',
                        ],
                        'warning' => [
                            'bar'   => 'bg-amber-400',
                            'badge' => 'bg-amber-100 text-amber-700',
                            'label' => 'اقتربت من الحد',
                            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                        ],
                        default   => [
                            'bar'   => 'bg-success',
                            'badge' => 'bg-success-soft text-success-700',
                            'label' => 'ضمن الحد',
                            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                        ],
                    };
                @endphp
                <div class="dash-card overflow-hidden">

                    {{-- شريط لون الحالة --}}
                    <div class="h-1 {{ $status['bar'] }}"></div>

                    <div class="p-5 space-y-4">
                        {{-- Header --}}
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-ink truncate text-base">
                                    {{ $budget->category?->name ?? ($budget->project?->name ?? 'عام') }}
                                </p>
                                <p class="text-xs text-muted mt-0.5 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $budget->period === 'monthly'
                                        ? ($months[$budget->month] . ' ' . $budget->year)
                                        : ('سنة ' . $budget->year) }}
                                </p>
                            </div>
                            <span class="inline-flex items-center gap-1 text-[11px] px-2.5 py-1 rounded-full font-semibold {{ $status['badge'] }} whitespace-nowrap shrink-0">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    {!! $status['icon'] !!}
                                </svg>
                                {{ $status['label'] }}
                            </span>
                        </div>

                        {{-- الأرقام --}}
                        <div class="flex items-end justify-between gap-2">
                            <div>
                                <p class="text-xs text-muted mb-0.5">المنفق</p>
                                <p class="text-xl font-bold nums text-ink">{{ number_format($budget->spent_amount, 2) }}</p>
                            </div>
                            <div class="text-left">
                                <p class="text-xs text-muted mb-0.5">الإجمالي</p>
                                <p class="text-xl font-bold nums text-muted">{{ number_format($budget->amount, 2) }}</p>
                            </div>
                        </div>

                        {{-- شريط التقدم --}}
                        <div class="space-y-1.5">
                            <div class="h-3 bg-slate-100 rounded-full overflow-hidden">
                                <div class="{{ $status['bar'] }} h-full rounded-full transition-all duration-500"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold nums {{ $budget->status === 'over' ? 'text-red-600' : ($budget->status === 'warning' ? 'text-amber-600' : 'text-success-700') }}">
                                    {{ $budget->usage_percentage }}%
                                </span>
                                @if($budget->status !== 'over')
                                    <span class="text-muted nums">متبقي: <span class="font-semibold text-ink">{{ number_format($budget->remaining_amount, 2) }}</span></span>
                                @else
                                    <span class="text-red-600 font-semibold nums">تجاوز: {{ number_format($budget->spent_amount - $budget->amount, 2) }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-2 pt-1 border-t border-subtle">
                            <a href="{{ route('budget.edit', $budget) }}"
                               class="flex-1 inline-flex items-center justify-center gap-1.5 text-xs py-2 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 transition-colors font-semibold">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                تعديل
                            </a>
                            <form action="{{ route('budget.destroy', $budget) }}" method="POST"
                                  onsubmit="return confirm('حذف هذه الميزانية؟')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 text-xs bg-error-soft text-red-600 rounded-lg hover:bg-red-100 transition-colors font-semibold">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    حذف
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection

@extends('layouts.app')

@section('title', 'الميزانيات')

@section('content')
<div class="space-y-6" x-data="{}">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">الميزانيات</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">تتبع إنفاقك مقابل ميزانياتك المحددة</p>
        </div>
        <a href="{{ route('budget.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            ميزانية جديدة
        </a>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('budget.index') }}"
          class="flex flex-wrap items-center gap-3 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-4 py-3">
        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">عرض:</span>

        <select name="month"
                class="text-sm bg-gray-100 dark:bg-gray-800 border-0 rounded-lg px-3 py-1.5 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500">
            @foreach($months as $num => $name)
                <option value="{{ $num }}" @selected($num == $month)>{{ $name }}</option>
            @endforeach
        </select>

        <select name="year"
                class="text-sm bg-gray-100 dark:bg-gray-800 border-0 rounded-lg px-3 py-1.5 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500">
            @foreach($years as $y)
                <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
            @endforeach
        </select>

        <button type="submit"
                class="px-4 py-1.5 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
            تطبيق
        </button>
    </form>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $summary['total'] }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">إجمالي الميزانيات</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $summary['over'] }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">تجاوزت الحد</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $summary['warning'] }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">قريبة من الحد</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $summary['ok'] }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">ضمن الحد</p>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Budget Cards --}}
    @if($budgets->isEmpty())
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 p-12 text-center">
            <svg class="w-12 h-12 text-gray-300 dark:text-gray-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <p class="text-gray-500 dark:text-gray-400 font-medium">لا توجد ميزانيات لهذه الفترة</p>
            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1 mb-4">أنشئ ميزانية لتتبع إنفاقك</p>
            <a href="{{ route('budget.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition">
                إنشاء ميزانية
            </a>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($budgets as $budget)
                @php
                    $pct   = min($budget->usage_percentage, 100);
                    $color = match($budget->status) {
                        'over'    => ['bar' => 'bg-red-500',   'badge' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',   'label' => 'تجاوزت'],
                        'warning' => ['bar' => 'bg-amber-500', 'badge' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400','label' => 'تحذير'],
                        default   => ['bar' => 'bg-emerald-500','badge' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400','label' => 'ضمن الحد'],
                    };
                @endphp
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 space-y-4">

                    {{-- Header --}}
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-900 dark:text-white truncate">
                                {{ $budget->category?->name ?? ($budget->project?->name ?? 'عام') }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ $budget->period === 'monthly' ? ($months[$budget->month] . ' ' . $budget->year) : ('سنة ' . $budget->year) }}
                            </p>
                        </div>
                        <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $color['badge'] }} whitespace-nowrap">
                            {{ $color['label'] }}
                        </span>
                    </div>

                    {{-- Progress --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">
                                {{ number_format($budget->spent_amount, 2) }} منفق
                            </span>
                            <span class="font-semibold text-gray-900 dark:text-white">
                                {{ number_format($budget->amount, 2) }}
                            </span>
                        </div>
                        <div class="h-2.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                            <div class="{{ $color['bar'] }} h-full rounded-full transition-all duration-500"
                                 style="width: {{ $pct }}%"></div>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span>{{ $budget->usage_percentage }}%</span>
                            @if($budget->status !== 'over')
                                <span>متبقي: {{ number_format($budget->remaining_amount, 2) }}</span>
                            @else
                                <span class="text-red-500">تجاوز بـ {{ number_format($budget->spent_amount - $budget->amount, 2) }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 pt-1 border-t border-gray-100 dark:border-gray-800">
                        <a href="{{ route('budget.edit', $budget) }}"
                           class="flex-1 text-center text-xs py-1.5 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                            تعديل
                        </a>
                        <form action="{{ route('budget.destroy', $budget) }}" method="POST"
                              onsubmit="return confirm('حذف هذه الميزانية؟')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="px-3 py-1.5 text-xs bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/40 transition">
                                حذف
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection

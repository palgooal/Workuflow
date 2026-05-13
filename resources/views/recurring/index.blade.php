@extends('layouts.app')

@section('title', 'الالتزامات المتكررة')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">الالتزامات المتكررة</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">دفعات ثابتة تتكرر تلقائياً بشكل دوري</p>
        </div>
        <a href="{{ route('recurring.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            التزام جديد
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $summary['total'] }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">إجمالي الالتزامات</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $summary['active'] }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">نشطة</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($summary['monthly_expense'], 2) }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">مصروف شهري ثابت</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($summary['monthly_income'], 2) }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">دخل شهري ثابت</p>
        </div>
    </div>

    {{-- Filter tabs --}}
    <div class="flex gap-2">
        @foreach(['all' => 'الكل', 'active' => 'النشطة فقط'] as $val => $label)
            <a href="{{ route('recurring.index', ['filter' => $val]) }}"
               class="px-4 py-1.5 rounded-lg text-sm font-medium transition
                      {{ $filter === $val
                          ? 'bg-indigo-600 text-white'
                          : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Cards --}}
    @if($recurrings->isEmpty())
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 p-12 text-center">
            <svg class="w-12 h-12 text-gray-300 dark:text-gray-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <p class="text-gray-500 dark:text-gray-400 font-medium">لا توجد التزامات متكررة</p>
            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1 mb-4">أنشئ التزاماً متكرراً لتسجيل الدفعات الثابتة تلقائياً</p>
            <a href="{{ route('recurring.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition">
                إنشاء التزام
            </a>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($recurrings as $rec)
                @php
                    $typeColor = $rec->type->value === 'income'
                        ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20'
                        : 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20';
                    $typeLabel = $rec->type->value === 'income' ? 'دخل' : 'مصروف';
                    $isDue     = $rec->isDue();
                @endphp
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 space-y-4
                            {{ ! $rec->is_active ? 'opacity-60' : '' }}">

                    {{-- Header --}}
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-900 dark:text-white truncate">{{ $rec->description }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ $rec->category?->name ?? 'بدون فئة' }}
                                @if($rec->project) · {{ $rec->project->name }} @endif
                            </p>
                        </div>
                        <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $typeColor }}">
                            {{ $typeLabel }}
                        </span>
                    </div>

                    {{-- Amount & Frequency --}}
                    <div class="flex items-center justify-between">
                        <p class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($rec->amount, 2) }}
                            <span class="text-sm font-normal text-gray-500">{{ $rec->currency }}</span>
                        </p>
                        <span class="text-xs px-2.5 py-1 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded-full">
                            {{ $frequencies[$rec->frequency->value] }}
                        </span>
                    </div>

                    {{-- Next due --}}
                    <div class="flex items-center gap-2 text-sm {{ $isDue ? 'text-amber-600 dark:text-amber-400' : 'text-gray-500 dark:text-gray-400' }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>
                            {{ $isDue ? 'مستحق الآن —' : 'الاستحقاق القادم:' }}
                            {{ $rec->next_due_date->translatedFormat('d M Y') }}
                        </span>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 pt-1 border-t border-gray-100 dark:border-gray-800">
                        {{-- Toggle --}}
                        <form action="{{ route('recurring.toggle', $rec) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="px-3 py-1.5 text-xs rounded-lg transition
                                           {{ $rec->is_active
                                               ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 hover:bg-amber-100'
                                               : 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100' }}">
                                {{ $rec->is_active ? 'إيقاف' : 'تفعيل' }}
                            </button>
                        </form>

                        {{-- Process Now (only if active and due) --}}
                        @if($rec->is_active && $isDue)
                            <form action="{{ route('recurring.process-now', $rec) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        class="px-3 py-1.5 text-xs bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 rounded-lg hover:bg-indigo-100 transition">
                                    تنفيذ الآن
                                </button>
                            </form>
                        @endif

                        <div class="flex-1"></div>

                        {{-- Edit --}}
                        <a href="{{ route('recurring.edit', $rec) }}"
                           class="px-3 py-1.5 text-xs bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                            تعديل
                        </a>

                        {{-- Delete --}}
                        <form action="{{ route('recurring.destroy', $rec) }}" method="POST"
                              onsubmit="return confirm('حذف هذا الالتزام؟')">
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

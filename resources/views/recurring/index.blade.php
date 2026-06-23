@extends('layouts.app')
@section('title', 'الالتزامات المتكررة')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <x-page-header title="الالتزامات المتكررة" subtitle="دفعات ثابتة تتكرر تلقائياً بشكل دوري">
        <x-slot name="actions">
            <a href="{{ route('recurring.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand text-white rounded-btn font-semibold hover:bg-brand-600 transition-colors text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                التزام جديد
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

    {{-- Summary Cards --}}
    <x-stat-grid cols="4">
        <x-stats-card title="إجمالي الالتزامات" color="brand" :value="$summary['total']">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </x-slot>
        </x-stats-card>
        <x-stats-card title="نشطة" color="green" :value="$summary['active']">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-slot>
        </x-stats-card>
        <x-stats-card title="مصروف شهري ثابت" color="red" :value="number_format($summary['monthly_expense'], 2)">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                </svg>
            </x-slot>
        </x-stats-card>
        <x-stats-card title="دخل شهري ثابت" color="green" :value="number_format($summary['monthly_income'], 2)">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                </svg>
            </x-slot>
        </x-stats-card>
    </x-stat-grid>

    {{-- Filter tabs --}}
    <div class="flex gap-2">
        @foreach(['all' => 'الكل', 'active' => 'النشطة فقط'] as $val => $label)
            <a href="{{ route('recurring.index', ['filter' => $val]) }}"
               class="px-4 py-2 rounded-btn text-sm font-medium transition-colors
                      {{ $filter === $val
                          ? 'bg-brand text-white'
                          : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Cards --}}
    @if($recurrings->isEmpty())
        <div class="dash-card py-16">
            <x-empty-state
                title="لا توجد التزامات متكررة"
                description="أنشئ التزاماً متكرراً لتسجيل الدفعات الثابتة تلقائياً"
                :action="route('recurring.create')"
                actionLabel="إنشاء التزام" />
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($recurrings as $rec)
                @php
                    $isIncome = $rec->type->value === 'income';
                    $isDue    = $rec->isDue();
                @endphp
                <div class="dash-card overflow-hidden {{ !$rec->is_active ? 'opacity-60' : '' }}">

                    {{-- شريط لون الحالة --}}
                    <div class="h-1 {{ $isIncome ? 'bg-emerald-500' : 'bg-red-500' }}"></div>

                    <div class="p-5 space-y-4">
                        {{-- Header --}}
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                                            {{ $isIncome ? 'bg-emerald-50 text-emerald-600' : 'bg-error-soft text-red-600' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        @if($isIncome)
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                                        @endif
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-ink truncate">{{ $rec->description }}</p>
                                    <p class="text-xs text-muted mt-0.5 truncate">
                                        {{ $rec->category?->name ?? 'بدون فئة' }}
                                        @if($rec->project)
                                            <span class="text-muted/60">·</span> {{ $rec->project->name }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="shrink-0">
                                @if(!$rec->is_active)
                                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-slate-100 text-muted font-medium">موقوف</span>
                                @elseif($isDue)
                                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 font-medium">مستحق</span>
                                @else
                                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-success-soft text-success-700 font-medium">نشط</span>
                                @endif
                            </div>
                        </div>

                        {{-- المبلغ + التكرار --}}
                        <div class="flex items-center justify-between">
                            <p class="text-2xl font-bold nums {{ $isIncome ? 'text-emerald-600' : 'text-ink' }}">
                                {{ number_format($rec->amount, 2) }}
                                <span class="text-sm font-normal text-muted">{{ $rec->currency }}</span>
                            </p>
                            <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 bg-slate-100 text-slate-600 rounded-full font-medium">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                {{ $frequencies[$rec->frequency->value] }}
                            </span>
                        </div>

                        {{-- الاستحقاق القادم --}}
                        <div class="flex items-center gap-2 text-sm px-3 py-2 rounded-xl
                                    {{ $isDue ? 'bg-amber-50 text-amber-700' : 'bg-slate-50 text-muted' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-xs font-medium">
                                {{ $isDue ? 'مستحق الآن —' : 'الاستحقاق القادم:' }}
                                {{ $rec->next_due_date->translatedFormat('d M Y') }}
                            </span>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-2 pt-1 border-t border-subtle">
                            {{-- Toggle --}}
                            <form action="{{ route('recurring.toggle', $rec) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs rounded-lg font-semibold transition-colors
                                               {{ $rec->is_active
                                                   ? 'bg-amber-50 text-amber-700 hover:bg-amber-100'
                                                   : 'bg-success-soft text-success-700 hover:bg-emerald-100' }}">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        @if($rec->is_active)
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        @endif
                                    </svg>
                                    {{ $rec->is_active ? 'إيقاف' : 'تفعيل' }}
                                </button>
                            </form>

                            {{-- Process Now --}}
                            @if($rec->is_active && $isDue)
                                <form action="{{ route('recurring.process-now', $rec) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs bg-brand-50 text-brand rounded-lg hover:bg-brand-100 transition-colors font-semibold">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                        تنفيذ الآن
                                    </button>
                                </form>
                            @endif

                            <div class="flex-1"></div>

                            {{-- Edit --}}
                            <a href="{{ route('recurring.edit', $rec) }}"
                               class="inline-flex items-center gap-1 px-3 py-1.5 text-xs bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 transition-colors font-semibold">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                تعديل
                            </a>

                            {{-- Delete --}}
                            <form action="{{ route('recurring.destroy', $rec) }}" method="POST"
                                  onsubmit="return confirm('حذف هذا الالتزام؟')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs bg-error-soft text-red-600 rounded-lg hover:bg-red-100 transition-colors font-semibold">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
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

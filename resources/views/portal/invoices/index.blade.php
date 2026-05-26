@extends('portal.layouts.portal')
@php $pageTitle = 'الفواتير'; @endphp

@section('content')
<div class="space-y-5">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-bold text-gray-800">الفواتير والمعاملات</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $transactions->count() }} معاملة مسجلة</p>
        </div>
        <a href="{{ route('portal.dashboard') }}"
           class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            الرئيسية
        </a>
    </div>

    @if($transactions->isEmpty())
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm py-16 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm text-gray-400">لا توجد فواتير بعد</p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-right px-5 py-3 font-semibold text-gray-600 text-xs">الوصف</th>
                        <th class="text-right px-4 py-3 font-semibold text-gray-600 text-xs hidden sm:table-cell">المشروع</th>
                        <th class="text-right px-4 py-3 font-semibold text-gray-600 text-xs">التاريخ</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 text-xs">المبلغ</th>
                        @if($canDownload)
                            <th class="text-center px-4 py-3 font-semibold text-gray-600 text-xs">تنزيل</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($transactions as $tx)
                        @php
                            $type = $tx->type instanceof \App\Enums\TransactionType
                                ? $tx->type
                                : \App\Enums\TransactionType::tryFrom($tx->type);
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3.5">
                                <a href="{{ route('portal.invoices.show', $tx->id) }}"
                                   class="font-medium text-gray-800 hover:text-indigo-600 truncate max-w-xs block transition">
                                    {{ $tx->description ?? 'معاملة #' . $tx->id }}
                                </a>
                            </td>
                            <td class="px-4 py-3.5 text-gray-500 hidden sm:table-cell text-xs">
                                {{ $tx->project->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3.5 text-gray-500 text-xs whitespace-nowrap">
                                {{ $tx->transaction_date?->format('Y/m/d') ?? '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-left font-semibold {{ ($tx->amount ?? 0) >= 0 ? 'text-emerald-600' : 'text-red-600' }} font-mono">
                                {{ number_format(abs((float)($tx->amount ?? 0)), 2) }}
                            </td>
                            @if($canDownload)
                                <td class="px-4 py-3.5 text-center">
                                    <span class="text-xs text-gray-300" title="PDF غير متاح بعد">—</span>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Summary --}}
        @php
            $totalIncome  = $transactions->where('type', 'income')->sum('amount');
            $totalExpense = $transactions->where('type', 'expense')->sum('amount');
        @endphp
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-emerald-50 rounded-xl border border-emerald-100 p-4">
                <div class="text-xs text-emerald-600 mb-1">إجمالي الدخل</div>
                <div class="text-lg font-bold text-emerald-700">{{ number_format($totalIncome, 2) }}</div>
            </div>
            <div class="bg-amber-50 rounded-xl border border-amber-100 p-4">
                <div class="text-xs text-amber-600 mb-1">إجمالي المصروفات</div>
                <div class="text-lg font-bold text-amber-700">{{ number_format($totalExpense, 2) }}</div>
            </div>
        </div>
    @endif

</div>
@endsection

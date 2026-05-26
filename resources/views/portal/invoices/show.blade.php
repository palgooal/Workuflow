@extends('portal.layouts.portal')
@php $pageTitle = 'تفاصيل المعاملة'; @endphp

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h1 class="text-lg font-bold text-gray-800">تفاصيل المعاملة</h1>
        <a href="{{ route('portal.invoices') }}"
           class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            الفواتير
        </a>
    </div>

    @php
        $type = $transaction->type instanceof \App\Support\Enums\TransactionType
            ? $transaction->type
            : \App\Support\Enums\TransactionType::tryFrom($transaction->type);

        $isIncome = $transaction->isIncome();
    @endphp

    {{-- Amount Card --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 text-center">
        <div class="text-xs text-gray-400 mb-1">
            {{ $isIncome ? 'دخل' : 'مصروف' }}
        </div>
        <div class="text-4xl font-bold {{ $isIncome ? 'text-emerald-600' : 'text-red-500' }} font-mono">
            {{ $isIncome ? '+' : '-' }}{{ number_format(abs((float)$transaction->amount), 2) }}
            @if($transaction->currency)
                <span class="text-lg font-normal text-gray-400 ms-1">{{ $transaction->currency }}</span>
            @endif
        </div>
        @if($transaction->transaction_date)
            <p class="text-xs text-gray-400 mt-2">
                {{ $transaction->transaction_date->format('Y/m/d') }}
            </p>
        @endif
    </div>

    {{-- Details --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-gray-50 bg-gray-50">
            <h2 class="text-xs font-semibold text-gray-600 uppercase tracking-wide">تفاصيل المعاملة</h2>
        </div>
        <div class="divide-y divide-gray-50">

            @php
                $rows = array_filter([
                    'الوصف'          => $transaction->description,
                    'المشروع'        => $transaction->project?->name,
                    'المستفيد/الدافع'=> $transaction->payee,
                    'المرجع'         => $transaction->reference,
                    'التاريخ'        => $transaction->transaction_date?->format('Y/m/d'),
                ]);
            @endphp

            @foreach($rows as $label => $value)
                <div class="px-5 py-3.5 flex items-start gap-4">
                    <div class="text-xs text-gray-400 w-36 shrink-0 pt-0.5">{{ $label }}</div>
                    <div class="text-sm text-gray-800 flex-1">{{ $value }}</div>
                </div>
            @endforeach

            @if($transaction->notes)
                <div class="px-5 py-3.5 flex items-start gap-4">
                    <div class="text-xs text-gray-400 w-36 shrink-0 pt-0.5">ملاحظات</div>
                    <div class="text-sm text-gray-600 flex-1 leading-relaxed">{{ $transaction->notes }}</div>
                </div>
            @endif

        </div>
    </div>

    {{-- Download Button (if permitted) --}}
    @if($canDownload)
        <div class="bg-indigo-50 rounded-xl border border-indigo-100 p-4 flex items-center justify-between">
            <div class="flex items-center gap-3 text-sm text-indigo-700">
                <svg class="w-5 h-5 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>تنزيل الفاتورة بصيغة PDF</span>
            </div>
            <span class="text-xs text-indigo-400 bg-indigo-100 px-2.5 py-1 rounded-full">قريباً</span>
        </div>
    @endif

    {{-- Token Info --}}
    <div class="bg-gray-50 rounded-xl border border-gray-200 p-4 flex items-start gap-3 text-sm text-gray-600">
        <svg class="w-4 h-4 text-gray-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        <p>جلستك الحالية صالحة حتى
            <strong>{{ $portalToken->expires_at->format('Y/m/d الساعة H:i') }}</strong>
        </p>
    </div>

</div>
@endsection

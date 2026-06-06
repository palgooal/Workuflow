@extends('layouts.app')
@section('title', 'الصناديق')
@section('content')
<div class="space-y-6">

    {{-- ── Header ── --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">الصناديق والخزائن</h1>
            <p class="mt-0.5 text-sm text-gray-500">تتبع كاشك وحساباتك البنكية ومحافظك</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('wallets.transfer.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                تحويل
            </a>
            <a href="{{ route('wallets.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                صندوق جديد
            </a>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
            <span>✅</span> {{ session('success') }}
        </div>
    @endif

    {{-- ── ملخص per-currency ── --}}
    @if($summary->count() > 1)
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
        @foreach($summary as $s)
        <div class="bg-white rounded-2xl border border-gray-100 p-4 text-center">
            <p class="text-xs text-gray-400 mb-1">إجمالي {{ $s['currency'] }}</p>
            <p class="text-xl font-bold {{ $s['balance'] >= 0 ? 'text-gray-900' : 'text-red-600' }}">
                {{ number_format($s['balance'], 2) }}
            </p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $s['count'] }} صندوق</p>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ── بطاقات الصناديق ── --}}
    @if($wallets->isEmpty())
        <div class="bg-white rounded-2xl border border-dashed border-gray-200 p-16 text-center">
            <div class="text-5xl mb-4">💼</div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">لا يوجد صناديق بعد</h3>
            <p class="text-sm text-gray-400 mb-6">أضف صندوقك الأول لتتبع رصيدك</p>
            <a href="{{ route('wallets.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition">
                + صندوق جديد
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($wallets as $wallet)
                @include('wallets._card', ['wallet' => $wallet])
            @endforeach
        </div>
    @endif

</div>
@endsection

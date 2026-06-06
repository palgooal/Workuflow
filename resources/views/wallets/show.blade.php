@extends('layouts.app')
@section('title', $wallet->name)
@section('content')
<div class="space-y-6">

    {{-- ── Header ── --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-3xl"
                 style="background-color: {{ $wallet->color }}22; border: 2px solid {{ $wallet->color }}55">
                {{ $wallet->icon ?: $wallet->type->icon() }}
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $wallet->name }}</h1>
                <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $wallet->type->tailwindBadge() }}">
                    {{ $wallet->type->label() }} · {{ $wallet->currency }}
                </span>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('wallets.transfer.create') }}"
               class="px-3 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm hover:bg-gray-50 transition">
                تحويل
            </a>
            <a href="{{ route('wallets.edit', $wallet) }}"
               class="px-3 py-2 bg-indigo-600 text-white rounded-xl text-sm hover:bg-indigo-700 transition">
                تعديل
            </a>
        </div>
    </div>

    {{-- ── KPIs ── --}}
    @php $balance = $wallet->balance(); @endphp
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center">
            <p class="text-xs text-gray-400 mb-2">الرصيد الحالي</p>
            <p class="text-2xl font-bold {{ $balance >= 0 ? 'text-gray-900' : 'text-red-600' }}">
                {{ number_format($balance, 2) }}
            </p>
            <p class="text-xs text-gray-400 mt-1">{{ $wallet->currency }}</p>
        </div>
        <div class="bg-green-50 rounded-2xl border border-green-100 p-5 text-center">
            <p class="text-xs text-gray-400 mb-2">إجمالي الدخل</p>
            <p class="text-2xl font-bold text-green-700">+{{ number_format($wallet->totalIncome(), 2) }}</p>
        </div>
        <div class="bg-red-50 rounded-2xl border border-red-100 p-5 text-center">
            <p class="text-xs text-gray-400 mb-2">إجمالي المصروفات</p>
            <p class="text-2xl font-bold text-red-700">-{{ number_format($wallet->totalExpenses(), 2) }}</p>
        </div>
    </div>

    {{-- ── المعاملات ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-900">المعاملات</h2>
            <a href="{{ route('transactions.create') }}"
               class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">+ معاملة جديدة</a>
        </div>

        @if($transactions->isEmpty())
            <div class="p-10 text-center text-gray-400 text-sm">لا توجد معاملات بعد</div>
        @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-5 py-3 text-right">التاريخ</th>
                        <th class="px-5 py-3 text-right">الوصف</th>
                        <th class="px-5 py-3 text-right">المشروع</th>
                        <th class="px-5 py-3 text-right">المبلغ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($transactions as $tx)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3 text-gray-500">{{ $tx->transaction_date->format('d M') }}</td>
                        <td class="px-5 py-3 text-gray-800">{{ $tx->description ?: '—' }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $tx->project?->name ?: '—' }}</td>
                        <td class="px-5 py-3 font-medium {{ $tx->type->value === 'income' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $tx->type->value === 'income' ? '+' : '-' }}{{ number_format($tx->amount, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-5 py-3 border-t border-gray-100">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    {{-- ── التحويلات ── --}}
    @if($transfers->isNotEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">التحويلات</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-5 py-3 text-right">التاريخ</th>
                    <th class="px-5 py-3 text-right">من</th>
                    <th class="px-5 py-3 text-right">إلى</th>
                    <th class="px-5 py-3 text-right">المبلغ</th>
                    <th class="px-5 py-3 text-right">الرسوم</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($transfers as $transfer)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 text-gray-500">{{ $transfer->transferred_at->format('d M Y') }}</td>
                    <td class="px-5 py-3">{{ $transfer->fromWallet->name }}</td>
                    <td class="px-5 py-3">{{ $transfer->toWallet->name }}</td>
                    <td class="px-5 py-3 font-medium text-gray-800">{{ number_format($transfer->amount, 2) }}</td>
                    <td class="px-5 py-3 text-gray-400">{{ $transfer->fee > 0 ? number_format($transfer->fee, 2) : '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

</div>
@endsection

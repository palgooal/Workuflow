@extends('layouts.app')

@section('title', 'لوحة الشركاء')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-ink tracking-tight">برنامج الشركاء</h1>
            <p class="mt-1 text-sm text-muted">مرحباً، {{ $affiliate->name }}</p>
        </div>

        {{-- Status Badge --}}
        @php
            $statusConfig = [
                'pending'   => ['label' => 'قيد المراجعة',  'class' => 'bg-amber-100 text-amber-700'],
                'active'    => ['label' => 'نشط',           'class' => 'bg-emerald-100 text-emerald-700'],
                'suspended' => ['label' => 'موقوف',         'class' => 'bg-red-100 text-red-700'],
            ];
            $sc = $statusConfig[$affiliate->status->value] ?? $statusConfig['pending'];
        @endphp
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $sc['class'] }}">
            {{ $sc['label'] }}
        </span>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-sm text-emerald-700">
        {{ session('success') }}
    </div>
    @endif

    {{-- Pending Notice --}}
    @if($affiliate->status->value === 'pending')
    <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 space-y-1">
        <p class="font-medium text-amber-800 text-sm">طلبك قيد المراجعة</p>
        <p class="text-amber-700 text-sm">سنتواصل معك خلال 1-3 أيام عمل بعد اعتماد حسابك.</p>
    </div>
    @endif

    {{-- Stats Grid --}}
    @if($affiliate->status->value === 'active')
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @foreach([
            ['label' => 'مُحالون',   'value' => number_format($affiliate->total_referrals), 'color' => 'text-ink'],
            ['label' => 'مُشتركون',  'value' => number_format($affiliate->total_converted), 'color' => 'text-brand'],
            ['label' => 'مكتسَب',    'value' => '$'.number_format($affiliate->total_earned, 2), 'color' => 'text-emerald-600'],
            ['label' => 'الرصيد',    'value' => '$'.number_format($affiliate->balance, 2),      'color' => 'text-violet-600'],
        ] as $stat)
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-4">
            <div class="text-xl font-bold {{ $stat['color'] }}">{{ $stat['value'] }}</div>
            <div class="text-xs text-muted mt-1">{{ $stat['label'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Commission Rate + Tier --}}
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5 flex items-center justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-ink">نسبة العمولة الحالية</p>
            <p class="text-2xl font-bold text-brand mt-1">{{ $affiliate->commission_rate }}٪</p>
        </div>
        <div class="text-left">
            <p class="text-xs text-muted">مستواك</p>
            @php
                $tierLabels = ['standard' => 'Standard', 'silver' => 'Silver', 'gold' => 'Gold', 'platinum' => 'Platinum'];
                $tierColors = ['standard' => 'text-slate-600', 'silver' => 'text-slate-400', 'gold' => 'text-amber-500', 'platinum' => 'text-violet-500'];
            @endphp
            <p class="text-lg font-bold {{ $tierColors[$affiliate->tier->value] ?? 'text-slate-600' }}">
                {{ $tierLabels[$affiliate->tier->value] ?? $affiliate->tier->value }}
            </p>
        </div>
    </div>

    {{-- Referral Link --}}
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5 space-y-3"
         x-data="{ copied: false }">
        <p class="text-sm font-medium text-ink">رابط الإحالة الخاص بك</p>
        <div class="flex gap-2">
            <input type="text" readonly value="{{ $referralUrl }}"
                   class="flex-1 rounded-lg border border-slate-200 px-3 py-2 text-sm text-muted bg-slate-50
                          focus:outline-none" id="referral-link"/>
            <button type="button"
                    @click="navigator.clipboard.writeText('{{ $referralUrl }}'); copied = true; setTimeout(() => copied = false, 2000)"
                    class="px-4 py-2 bg-brand hover:bg-brand-600 text-white text-sm font-medium rounded-lg transition">
                <span x-show="!copied">نسخ</span>
                <span x-show="copied" x-cloak>✓ تم</span>
            </button>
        </div>
        <p class="text-xs text-muted">شارك هذا الرابط — كل مَن يشترك عبره تكسب عمولته</p>
    </div>

    {{-- Quick Links --}}
    <div class="grid grid-cols-2 gap-4">
        <a href="{{ route('affiliates.commissions') }}"
           class="bg-white rounded-xl border border-slate-100 shadow-sm p-5 hover:border-brand/30
                  hover:shadow-md transition group">
            <p class="text-sm font-medium text-ink group-hover:text-brand">العمولات</p>
            <p class="text-xs text-muted mt-1">{{ number_format($affiliate->total_converted) }} عمولة</p>
        </a>
        <a href="{{ route('affiliates.payouts') }}"
           class="bg-white rounded-xl border border-slate-100 shadow-sm p-5 hover:border-brand/30
                  hover:shadow-md transition group">
            <p class="text-sm font-medium text-ink group-hover:text-brand">طلبات الصرف</p>
            <p class="text-xs text-muted mt-1">
                @if($canPayout)
                    رصيدك ${{ number_format($affiliate->balance, 2) }} — جاهز للصرف
                @else
                    رصيدك ${{ number_format($affiliate->balance, 2) }}
                @endif
            </p>
        </a>
    </div>
    @endif

</div>
@endsection

@extends('layouts.app')

@section('title', 'عمولاتي')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-ink tracking-tight">العمولات</h1>
            <p class="mt-1 text-sm text-muted">{{ number_format($commissions->total()) }} عمولة مسجَّلة</p>
        </div>
        <a href="{{ route('affiliates.dashboard') }}"
           class="text-sm text-muted hover:text-ink flex items-center gap-1.5 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            العودة
        </a>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        @if($commissions->isEmpty())
        <div class="text-center py-16 text-muted">
            <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
            </svg>
            <p class="text-sm">لا توجد عمولات بعد</p>
            <p class="text-xs mt-1">شارك رابطك لتبدأ الكسب</p>
        </div>
        @else
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="text-right text-xs font-medium text-muted px-5 py-3">التاريخ</th>
                    <th class="text-right text-xs font-medium text-muted px-5 py-3">الخطة</th>
                    <th class="text-right text-xs font-medium text-muted px-5 py-3">قيمة الاشتراك</th>
                    <th class="text-right text-xs font-medium text-muted px-5 py-3">العمولة</th>
                    <th class="text-right text-xs font-medium text-muted px-5 py-3">النسبة</th>
                    <th class="text-right text-xs font-medium text-muted px-5 py-3">الحالة</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($commissions as $commission)
                @php
                    $statusConfig = [
                        'pending'  => ['label' => 'معلّقة',   'class' => 'bg-amber-100 text-amber-700'],
                        'approved' => ['label' => 'معتمدة',   'class' => 'bg-emerald-100 text-emerald-700'],
                        'paid'     => ['label' => 'مدفوعة',   'class' => 'bg-blue-100 text-blue-700'],
                        'rejected' => ['label' => 'مرفوضة',   'class' => 'bg-red-100 text-red-700'],
                        'on_hold'  => ['label' => 'موقوفة',   'class' => 'bg-slate-100 text-slate-600'],
                    ];
                    $sc = $statusConfig[$commission->status->value] ?? $statusConfig['pending'];
                @endphp
                <tr class="hover:bg-slate-50/50 transition">
                    <td class="px-5 py-3.5 text-muted text-xs">
                        {{ $commission->created_at->format('Y/m/d') }}
                    </td>
                    <td class="px-5 py-3.5 font-medium text-ink capitalize">
                        {{ $commission->subscription_plan }}
                        <span class="text-xs text-muted font-normal">
                            ({{ $commission->subscription_cycle === 'annual' ? 'سنوي' : 'شهري' }})
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-ink">
                        ${{ number_format($commission->subscription_amount, 2) }}
                    </td>
                    <td class="px-5 py-3.5 font-semibold text-emerald-600">
                        ${{ number_format($commission->amount, 2) }}
                    </td>
                    <td class="px-5 py-3.5 text-muted">
                        {{ $commission->rate }}٪
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $sc['class'] }}">
                                {{ $sc['label'] }}
                            </span>
                            @if($commission->fraud_flagged)
                            <span title="مشتبه به" class="text-amber-500">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Pagination --}}
        @if($commissions->hasPages())
        <div class="px-5 py-3 border-t border-slate-100">
            {{ $commissions->links() }}
        </div>
        @endif
        @endif
    </div>

</div>
@endsection

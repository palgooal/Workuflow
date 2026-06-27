@extends('layouts.app')

@section('title', 'طلبات الصرف')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-ink tracking-tight">طلبات الصرف</h1>
            <p class="mt-1 text-sm text-muted">
                الرصيد المتاح:
                <span class="font-semibold text-ink">${{ number_format($affiliate->balance, 2) }}</span>
            </p>
        </div>
        <a href="{{ route('affiliates.dashboard') }}"
           class="text-sm text-muted hover:text-ink flex items-center gap-1.5 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            العودة
        </a>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-sm text-emerald-700">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
        {{ session('error') }}
    </div>
    @endif

    {{-- Payout Request Form --}}
    @if($canPayout)
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
        <div>
            <h2 class="text-base font-semibold text-ink">طلب صرف جديد</h2>
            <p class="text-sm text-muted mt-1">
                ستُصرف كامل رصيدك البالغ
                <strong class="text-ink">${{ number_format($affiliate->balance, 2) }}</strong>
            </p>
        </div>

        <form method="POST" action="{{ route('affiliates.payout.request') }}" class="space-y-4">
            @csrf

            @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
                <ul class="space-y-1 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="space-y-1">
                <label class="block text-sm font-medium text-ink">طريقة الاستلام</label>
                <select name="method" required
                        class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-ink
                               focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand transition
                               @error('method') border-red-400 @enderror">
                    <option value="">اختر طريقة الاستلام</option>
                    @foreach($payoutMethods as $pm)
                    @php
                        $methodLabels = ['bank' => 'تحويل بنكي', 'whatsapp' => 'واتساب باي', 'credit' => 'رصيد اشتراك'];
                    @endphp
                    <option value="{{ $pm->value }}" {{ old('method') === $pm->value ? 'selected' : '' }}>
                        {{ $methodLabels[$pm->value] ?? $pm->value }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-1">
                <label class="block text-sm font-medium text-ink">
                    ملاحظات <span class="text-muted font-normal">(اختياري)</span>
                </label>
                <textarea name="notes" rows="2"
                          placeholder="أي تفاصيل إضافية لمعالجة الطلب..."
                          class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-ink
                                 focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand
                                 resize-none transition">{{ old('notes') }}</textarea>
            </div>

            <button type="submit"
                    class="w-full py-3 bg-brand hover:bg-brand-600 text-white font-semibold rounded-xl
                           transition text-sm">
                تقديم طلب الصرف
            </button>
        </form>
    </div>
    @elseif($affiliate->isActive())
    <div class="bg-slate-50 border border-slate-200 rounded-xl px-5 py-4 text-sm text-slate-600">
        @if($affiliate->balance < 20)
            رصيدك الحالي <strong>${{ number_format($affiliate->balance, 2) }}</strong> أقل من الحد الأدنى للصرف (20$).
            استمر في جلب المشتركين لرفع رصيدك.
        @else
            يوجد طلب صرف قيد المعالجة. يمكنك تقديم طلب جديد بعد إتمامه.
        @endif
    </div>
    @endif

    {{-- Payouts History --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-sm font-semibold text-ink">سجل الصرف</h2>
        </div>

        @if($payouts->isEmpty())
        <div class="text-center py-12 text-muted">
            <svg class="w-10 h-10 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/>
            </svg>
            <p class="text-sm">لا توجد طلبات صرف بعد</p>
        </div>
        @else
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="text-right text-xs font-medium text-muted px-5 py-3">التاريخ</th>
                    <th class="text-right text-xs font-medium text-muted px-5 py-3">المبلغ</th>
                    <th class="text-right text-xs font-medium text-muted px-5 py-3">الطريقة</th>
                    <th class="text-right text-xs font-medium text-muted px-5 py-3">الحالة</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($payouts as $payout)
                @php
                    $statusConfig = [
                        'requested'  => ['label' => 'معلّق',     'class' => 'bg-amber-100 text-amber-700'],
                        'processing' => ['label' => 'قيد المعالجة','class' => 'bg-blue-100 text-blue-700'],
                        'paid'       => ['label' => 'مدفوع',     'class' => 'bg-emerald-100 text-emerald-700'],
                        'rejected'   => ['label' => 'مرفوض',     'class' => 'bg-red-100 text-red-700'],
                    ];
                    $sc = $statusConfig[$payout->status->value] ?? $statusConfig['requested'];
                    $methodLabels = ['bank' => 'تحويل بنكي', 'whatsapp' => 'واتساب باي', 'credit' => 'رصيد اشتراك'];
                @endphp
                <tr class="hover:bg-slate-50/50 transition">
                    <td class="px-5 py-3.5 text-muted text-xs">
                        {{ $payout->requested_at->format('Y/m/d') }}
                    </td>
                    <td class="px-5 py-3.5 font-semibold text-ink">
                        ${{ number_format($payout->amount, 2) }}
                    </td>
                    <td class="px-5 py-3.5 text-muted">
                        {{ $methodLabels[$payout->method->value] ?? $payout->method->value }}
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $sc['class'] }}">
                            {{ $sc['label'] }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($payouts->hasPages())
        <div class="px-5 py-3 border-t border-slate-100">
            {{ $payouts->links() }}
        </div>
        @endif
        @endif
    </div>

</div>
@endsection

@extends('layouts.app')

@section('title', 'تحصيلاتي')

@section('breadcrumb')
    <span class="text-muted/60">/</span>
    <span class="text-ink">تحصيلاتي</span>
@endsection

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <x-page-header title="تحصيلاتي" subtitle="عمليات تحصيل فواتيرك عبر بوابة الدفع نيابةً عنك">
        @if($hasEligibleForSettlement)
        <x-slot name="actions">
            <form method="POST" action="{{ route('settlement-requests.store') }}"
                  onsubmit="return confirm('سيُرسَل طلب تسوية لكل المبالغ الجاهزة حالياً إلى فريق دراهم للمراجعة. لن يتم تحويل أي مبلغ تلقائياً. متابعة؟')">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand hover:bg-brand-600
                               text-white text-sm font-semibold rounded-btn transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    طلب تسوية
                </button>
            </form>
        </x-slot>
        @endif
    </x-page-header>

    @if($hasPendingSettlementRequest)
    <div class="flex items-start gap-2 text-xs text-blue-800 bg-blue-50 border border-blue-200 rounded-xl px-4 py-2.5">
        <svg class="w-4 h-4 shrink-0 mt-0.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        لديك طلب تسوية قيد المراجعة بالفعل — سيصلك تحديث بمجرد أن يراجعه فريق دراهم.
    </div>
    @endif

    {{-- إحصائيات --}}
    <x-stat-grid cols="4">
        <x-stats-card title="إجمالي المحصّل للتسوية" color="brand"
                      :value="number_format($summary['collected_amount'], 2)" :suffix="' '.$settlementCurrency"
                      tooltip="إجمالي ما حصّلته بوابة الدفع بالشيكل من عملائك، وما زال بانتظار التسوية معك.">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                </svg>
            </x-slot>
        </x-stats-card>

        <x-stats-card title="الصافي بانتظار التسوية" color="accent"
                      :value="number_format($summary['collected_net'], 2)" :suffix="' '.$settlementCurrency"
                      tooltip="المبلغ بالشيكل الذي سيُحوَّل إليك بعد خصم عمولة بوابة الدفع من التحصيلات التي لم تُسوَّ بعد ومبلغ تسويتها معروف.">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-slot>
        </x-stats-card>

        <x-stats-card title="إجمالي العمولة المخصومة" color="yellow"
                      :value="number_format($summary['total_fee'], 2)" :suffix="' '.$settlementCurrency"
                      tooltip="مجموع عمولة بوابة الدفع بالشيكل، المخصومة من كل ما تم تحصيله (المُسوَّى وغير المُسوَّى).">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-slot>
        </x-stats-card>

        <x-stats-card title="تمت تسويته معي" color="green"
                      :value="number_format($summary['settled_net'], 2)" :suffix="' '.$settlementCurrency"
                      tooltip="صافي المبالغ بالشيكل التي تمت تسويتها فعلياً معك خارج النظام من قِبل فريق دراهم.">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-slot>
        </x-stats-card>
    </x-stat-grid>

    {{-- تنبيه توضيحي --}}
    <div class="flex items-start gap-2 text-xs text-slate-600 bg-slate-50 border border-subtle rounded-xl px-4 py-2.5">
        <svg class="w-4 h-4 shrink-0 mt-0.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        هذه الصفحة للاطّلاع فقط. بوابة الدفع تُحصِّل وتُسوِّي الأموال بالشيكل (ILS) دائماً، حتى لو كانت فاتورتك بعملة أخرى — لذلك الأرقام أعلاه بالشيكل. التسوية النهائية (تحويل المبلغ إليك) تتم يدوياً من فريق دراهم بعد التحقق، ولا يمكن تنفيذها من هنا.
    </div>

    @if($pendingSettlementCount > 0)
    <div class="flex items-start gap-2 text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded-xl px-4 py-2.5">
        <svg class="w-4 h-4 shrink-0 mt-0.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.74-2.99l-6.93-12a2 2 0 00-3.48 0l-6.93 12A2 2 0 005.07 19z"/>
        </svg>
        لديك {{ $pendingSettlementCount }} {{ $pendingSettlementCount === 1 ? 'عملية تحصيل' : 'عمليات تحصيل' }} بفواتير بعملة غير الشيكل، بانتظار أن يُحدِّد فريق دراهم مبلغ التسوية النهائي بالشيكل لها — لن تظهر ضمن الأرقام أعلاه حتى يُحدَّد.
    </div>
    @endif

    {{-- فلتر الحالة --}}
    <x-filter-bar :action="route('collections.index')"
                  :reset="route('collections.index')"
                  :active="request()->filled('status') && request('status') !== 'all'">
        <select name="status" class="filter-field lg:w-48">
            <option value="all" {{ !request('status') || request('status') === 'all' ? 'selected' : '' }}>كل الحالات</option>
            @foreach(\App\Support\Enums\PaymentCollectionStatus::cases() as $status)
                <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
                    {{ $status->label() }}
                </option>
            @endforeach
        </select>
    </x-filter-bar>

    {{-- الجدول --}}
    @if($collections->isEmpty())
        <div class="dash-card py-16">
            <x-empty-state
                title="لا توجد تحصيلات بعد"
                description="ستظهر هنا عمليات تحصيل فواتيرك بمجرد أن يدفع أحد عملائك عبر رابط الدفع." />
        </div>
    @else
        <x-data-table>
            <x-slot name="head">
                <x-table-th>رقم الفاتورة</x-table-th>
                <x-table-th>العميل</x-table-th>
                <x-table-th align="left">مبلغ الفاتورة الأصلي</x-table-th>
                <x-table-th align="left">صافي التسوية (بالشيكل)</x-table-th>
                <x-table-th>الحالة</x-table-th>
                <x-table-th class="hidden lg:table-cell">تاريخ التحصيل</x-table-th>
                <x-table-th class="hidden lg:table-cell">تاريخ التسوية</x-table-th>
            </x-slot>

            @foreach($collections as $collection)
            <tr class="dash-row">
                <td class="dash-td">
                    @if($collection->invoice)
                        <a href="{{ route('invoices.show', $collection->invoice->ulid) }}"
                           class="font-semibold text-brand hover:text-brand-700 hover:underline nums">
                            {{ $collection->invoice->number }}
                        </a>
                    @else
                        <span class="text-muted/60">—</span>
                    @endif
                </td>
                <td class="dash-td">
                    <span class="text-slate-800 font-medium">{{ $collection->client->name ?? '—' }}</span>
                </td>
                <td class="dash-td text-left">
                    <span class="font-medium text-ink nums">{{ number_format($collection->amount, 2) }}</span>
                    <span class="text-xs text-muted mr-0.5">{{ $collection->currency }}</span>
                </td>
                <td class="dash-td text-left">
                    @if($collection->settlement_net_amount !== null)
                        <span class="font-semibold text-ink nums">{{ number_format($collection->settlement_net_amount, 2) }}</span>
                        <span class="text-xs text-muted mr-0.5">{{ $collection->settlement_currency }}</span>
                    @elseif($collection->status->value === 'collected')
                        <span class="inline-flex items-center gap-1 text-xs text-amber-700">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.74-2.99l-6.93-12a2 2 0 00-3.48 0l-6.93 12A2 2 0 005.07 19z"/>
                            </svg>
                            بانتظار تحديد المبلغ
                        </span>
                    @else
                        <span class="text-muted/60">—</span>
                    @endif
                </td>
                <td class="dash-td">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium w-fit {{ $collection->status->badgeClass() }}">
                        {{ $collection->status->icon() }} {{ $collection->status->label() }}
                    </span>
                </td>
                <td class="dash-td hidden lg:table-cell text-slate-600 nums">
                    {{ $collection->collected_at?->format('Y/m/d') ?? '—' }}
                </td>
                <td class="dash-td hidden lg:table-cell text-slate-600 nums">
                    {{ $collection->settled_at?->format('Y/m/d') ?? '—' }}
                </td>
            </tr>
            @endforeach

            @if($collections->hasPages())
                <x-slot name="pagination">{{ $collections->links() }}</x-slot>
            @endif
        </x-data-table>
    @endif

    {{-- طلبات التسوية --}}
    <x-card-section title="طلبات التسوية" padding="p-0">
        @if($settlementRequests->isEmpty())
            <div class="py-10 text-center text-sm text-muted">
                لم تُرسِل أي طلب تسوية بعد.
            </div>
        @else
            <x-data-table>
                <x-slot name="head">
                    <x-table-th>الرقم</x-table-th>
                    <x-table-th align="left">المبلغ</x-table-th>
                    <x-table-th>الحالة</x-table-th>
                    <x-table-th class="hidden lg:table-cell">تاريخ الطلب</x-table-th>
                    <x-table-th class="hidden lg:table-cell">تاريخ الدفع</x-table-th>
                </x-slot>

                @foreach($settlementRequests as $sr)
                <tr class="dash-row">
                    <td class="dash-td">
                        <span class="font-semibold text-ink nums">#{{ $sr->id }}</span>
                    </td>
                    <td class="dash-td text-left">
                        <span class="font-semibold text-ink nums">{{ number_format($sr->total_amount, 2) }}</span>
                        <span class="text-xs text-muted mr-0.5">{{ $sr->currency }}</span>
                    </td>
                    <td class="dash-td">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium w-fit {{ $sr->status->badgeClass() }}">
                            {{ $sr->status->icon() }} {{ $sr->status->label() }}
                        </span>
                        @if($sr->status->value === 'rejected' && $sr->admin_notes)
                            <p class="text-xs text-red-600 mt-1 max-w-xs">{{ $sr->admin_notes }}</p>
                        @endif
                    </td>
                    <td class="dash-td hidden lg:table-cell text-slate-600 nums">
                        {{ $sr->requested_at?->format('Y/m/d') ?? '—' }}
                    </td>
                    <td class="dash-td hidden lg:table-cell text-slate-600 nums">
                        {{ $sr->paid_at?->format('Y/m/d') ?? '—' }}
                    </td>
                </tr>
                @endforeach
            </x-data-table>
        @endif
    </x-card-section>

</div>
@endsection

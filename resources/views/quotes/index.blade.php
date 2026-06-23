@extends('layouts.app')

@section('title', 'عروض الأسعار')

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <x-page-header title="عروض الأسعار" subtitle="إدارة عروض الأسعار المرسلة للعملاء">
        <x-slot name="actions">
            <a href="{{ route('quotes.create') }}"
               class="inline-flex items-center gap-2 bg-brand text-white text-sm font-semibold
                      px-4 py-2.5 rounded-btn hover:bg-brand-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                عرض سعر جديد
            </a>
        </x-slot>
    </x-page-header>

    {{-- إحصائيات --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <div class="dash-card p-4 text-center">
            <div class="text-2xl font-bold text-ink nums">{{ $stats->total ?? 0 }}</div>
            <div class="text-xs text-muted mt-0.5">إجمالي العروض</div>
        </div>
        <div class="dash-card p-4 text-center">
            <div class="text-2xl font-bold text-blue-700 nums">{{ $stats->pending ?? 0 }}</div>
            <div class="text-xs text-muted mt-0.5">في الانتظار</div>
        </div>
        <div class="dash-card p-4 text-center">
            <div class="text-2xl font-bold text-teal-700 nums">{{ $stats->accepted ?? 0 }}</div>
            <div class="text-xs text-muted mt-0.5">مقبولة</div>
        </div>
        <div class="dash-card p-4 text-center">
            <div class="text-2xl font-bold text-red-600 nums">{{ $stats->rejected ?? 0 }}</div>
            <div class="text-xs text-muted mt-0.5">مرفوضة</div>
        </div>
        <div class="dash-card p-4 text-center">
            <div class="text-2xl font-bold text-purple-700 nums">{{ $stats->converted ?? 0 }}</div>
            <div class="text-xs text-muted mt-0.5">محوّلة لفاتورة</div>
        </div>
    </div>

    {{-- الجدول --}}
    @if($quotes->isEmpty())
        <div class="dash-card py-16">
            <x-empty-state
                title="لا توجد عروض أسعار بعد"
                description="ابدأ بإنشاء أول عرض سعر لعملائك"
                :action="route('quotes.create')"
                actionLabel="إنشاء عرض سعر" />
        </div>
    @else
        <x-data-table>
            <x-slot name="head">
                <x-table-th>رقم العرض</x-table-th>
                <x-table-th>العميل</x-table-th>
                <x-table-th>المشروع</x-table-th>
                <x-table-th>الحالة</x-table-th>
                <x-table-th>تاريخ الإصدار</x-table-th>
                <x-table-th>صالح حتى</x-table-th>
                <x-table-th align="left">الإجمالي</x-table-th>
                <x-table-th><span class="sr-only">إجراءات</span></x-table-th>
            </x-slot>
            @foreach($quotes as $quote)
            @php
                $isExpired = $quote->isExpired();
                $statusLabel = $isExpired ? 'منتهي الصلاحية' : $quote->status->label();
                $statusClass  = $isExpired ? 'bg-orange-100 text-orange-700' : $quote->status->badgeClass();
            @endphp
            <tr class="dash-row">
                <td class="dash-td">
                    <a href="{{ route('quotes.show', $quote->ulid) }}"
                       class="font-semibold text-brand hover:text-brand-700 nums">{{ $quote->number }}</a>
                    @if($quote->title)
                        <div class="text-xs text-muted truncate max-w-32">{{ $quote->title }}</div>
                    @endif
                </td>
                <td class="dash-td text-slate-700">{{ $quote->client->name }}</td>
                <td class="dash-td text-muted text-xs">{{ $quote->project?->name ?? '—' }}</td>
                <td class="dash-td">
                    <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full {{ $statusClass }}">
                        {{ $quote->status->icon() }} {{ $statusLabel }}
                    </span>
                </td>
                <td class="dash-td text-muted text-xs nums">{{ $quote->issue_date->format('d/m/Y') }}</td>
                <td class="dash-td text-xs nums {{ $isExpired ? 'text-red-600 font-medium' : 'text-muted' }}">
                    {{ $quote->valid_until?->format('d/m/Y') ?? '—' }}
                </td>
                <td class="dash-td text-left font-semibold text-ink nums">
                    {{ number_format($quote->total, 2) }}
                    <span class="text-xs font-normal text-muted">{{ $quote->currency }}</span>
                </td>
                <td class="dash-td">
                    <a href="{{ route('quotes.show', $quote->ulid) }}"
                       class="text-xs text-brand hover:underline font-medium">عرض</a>
                </td>
            </tr>
            @endforeach
            @if($quotes->hasPages())
                <x-slot name="pagination">{{ $quotes->links() }}</x-slot>
            @endif
        </x-data-table>
    @endif

</div>
@endsection

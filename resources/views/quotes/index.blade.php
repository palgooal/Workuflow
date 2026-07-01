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
    {{-- 5 بطاقات: لا يدعم x-stat-grid أكثر من 4 أعمدة، نستخدم grid مباشرةً --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">

        <x-stats-card title="إجمالي العروض" color="brand" :value="$stats->total ?? 0">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586
                             a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </x-slot>
        </x-stats-card>

        <x-stats-card title="في الانتظار" color="yellow" :value="$stats->pending ?? 0">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-slot>
        </x-stats-card>

        <x-stats-card title="مقبولة" color="green" :value="$stats->accepted ?? 0">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-slot>
        </x-stats-card>

        <x-stats-card title="مرفوضة" color="red" :value="$stats->rejected ?? 0">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-slot>
        </x-stats-card>

        <x-stats-card title="محوّلة لفاتورة" color="accent" :value="$stats->converted ?? 0">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9
                             m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </x-slot>
        </x-stats-card>

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
                    {{ number_format($quote->total, \App\Support\Helpers\Currency::decimals($quote->currency)) }}
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

@extends('layouts.app')

@section('title', 'الفواتير')

@section('breadcrumb')
    <span class="text-muted/60">/</span>
    <span class="text-ink">الفواتير</span>
@endsection

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <x-page-header title="الفواتير" subtitle="جميع فواتيرك في مكان واحد">
        <x-slot name="actions">
            <a href="{{ route('invoices.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand hover:bg-brand-600
                      text-white text-sm font-semibold rounded-btn transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                فاتورة جديدة
            </a>
        </x-slot>
    </x-page-header>

    {{-- إحصائيات سريعة --}}
    @if($invoices->total() > 0)
    @php
        $allInvoices = $invoices->getCollection();
    @endphp
    <x-stat-grid cols="4">
        <x-stats-card title="إجمالي الفواتير" color="brand" :value="$invoices->total()">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </x-slot>
        </x-stats-card>

        <x-stats-card title="مسودة / مُرسَلة" color="yellow"
                      :value="$allInvoices->whereIn('status.value', ['draft','sent'])->count()">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-slot>
        </x-stats-card>

        <x-stats-card title="مدفوعة" color="green"
                      :value="$allInvoices->where('status.value', 'paid')->count()">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-slot>
        </x-stats-card>

        <x-stats-card title="متأخرة" color="red"
                      :value="$allInvoices->filter(fn($i) => $i->isOverdue())->count()">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.74-2.99l-6.93-12a2 2 0 00-3.48 0l-6.93 12A2 2 0 005.07 19z"/>
                </svg>
            </x-slot>
        </x-stats-card>
    </x-stat-grid>
    @endif

    {{-- Flash --}}
    @if(session('success'))
        <div class="bg-success-soft border border-success/30 text-success-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- قائمة الفواتير --}}
    @if($invoices->isEmpty())
        <div class="dash-card py-16">
            <x-empty-state
                title="لا توجد فواتير بعد"
                description="ابدأ بإنشاء أول فاتورة لعميلك"
                :action="route('invoices.create')"
                actionLabel="إنشاء فاتورة" />
        </div>
    @else
        <x-data-table>
            <x-slot name="head">
                <x-table-th>رقم الفاتورة</x-table-th>
                <x-table-th>العميل</x-table-th>
                <x-table-th class="hidden md:table-cell">المشروع</x-table-th>
                <x-table-th>الحالة</x-table-th>
                <x-table-th class="hidden lg:table-cell">تاريخ الإصدار</x-table-th>
                <x-table-th class="hidden lg:table-cell">الاستحقاق</x-table-th>
                <x-table-th align="left">الإجمالي</x-table-th>
                <x-table-th><span class="sr-only">إجراءات</span></x-table-th>
            </x-slot>

            @foreach($invoices as $invoice)
            <tr class="dash-row group">
                <td class="dash-td">
                    <a href="{{ route('invoices.show', $invoice->ulid) }}"
                       class="font-semibold text-brand hover:text-brand-700 hover:underline nums">
                        {{ $invoice->number }}
                    </a>
                    @if($invoice->title)
                    <p class="text-xs text-muted mt-0.5 truncate max-w-[160px]">{{ $invoice->title }}</p>
                    @endif
                </td>
                <td class="dash-td">
                    <a href="{{ route('clients.show', $invoice->client->public_id) }}"
                       class="text-slate-800 hover:text-brand transition-colors font-medium">
                        {{ $invoice->client->name }}
                    </a>
                    @if($invoice->client->company)
                    <p class="text-xs text-muted">{{ $invoice->client->company }}</p>
                    @endif
                </td>
                <td class="dash-td hidden md:table-cell">
                    @if($invoice->project)
                    <span class="text-slate-600">{{ $invoice->project->name }}</span>
                    @else
                    <span class="text-muted/60">—</span>
                    @endif
                </td>
                <td class="dash-td">
                    <div class="flex flex-col gap-1">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium w-fit {{ $invoice->status->badgeClass() }}">
                            {{ $invoice->status->icon() }} {{ $invoice->status->label() }}
                        </span>
                        @if($invoice->isOverdue())
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 w-fit">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.74-2.99l-6.93-12a2 2 0 00-3.48 0l-6.93 12A2 2 0 005.07 19z"/>
                            </svg>
                            متأخرة
                        </span>
                        @endif
                    </div>
                </td>
                <td class="dash-td hidden lg:table-cell text-slate-600 nums">
                    {{ $invoice->issue_date->format('Y/m/d') }}
                </td>
                <td class="dash-td hidden lg:table-cell">
                    @if($invoice->due_date)
                    <span class="nums {{ $invoice->isOverdue() ? 'text-red-600 font-medium' : 'text-slate-600' }}">
                        {{ $invoice->due_date->format('Y/m/d') }}
                    </span>
                    @else
                    <span class="text-muted/60">—</span>
                    @endif
                </td>
                <td class="dash-td text-left">
                    <span class="font-semibold text-ink nums">{{ number_format($invoice->total, \App\Support\Helpers\Currency::decimals($invoice->currency)) }}</span>
                    <span class="text-xs text-muted mr-0.5">{{ $invoice->currency }}</span>
                </td>
                <td class="dash-td">
                    <a href="{{ route('invoices.show', $invoice->ulid) }}"
                       class="row-action hover:text-brand hover:bg-brand-50 sm:opacity-0 sm:group-hover:opacity-100 transition"
                       aria-label="عرض الفاتورة">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                </td>
            </tr>
            @endforeach

            @if($invoices->hasPages())
                <x-slot name="pagination">{{ $invoices->links() }}</x-slot>
            @endif
        </x-data-table>
    @endif

</div>
@endsection

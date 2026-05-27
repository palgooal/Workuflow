@extends('layouts.app')

@section('title', 'فاتورة ' . $invoice->number)

@section('content')
<div class="max-w-4xl mx-auto space-y-4">

    {{-- شريط الإجراءات --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('clients.show', $invoice->client->public_id) }}"
               class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-gray-900">{{ $invoice->number }}</h1>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $invoice->status->badgeClass() }}">
                        {{ $invoice->status->icon() }} {{ $invoice->status->label() }}
                    </span>
                    @if($invoice->isOverdue())
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                        ⚠️ متأخرة
                    </span>
                    @endif
                </div>
                <p class="text-sm text-gray-500">{{ $invoice->client->name }}
                    @if($invoice->project) — {{ $invoice->project->name }} @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap print:hidden">
            {{-- تغيير الحالة --}}
            @if($invoice->status !== \App\Support\Enums\InvoiceStatus::Paid && $invoice->status !== \App\Support\Enums\InvoiceStatus::Cancelled)
            @if($invoice->status === \App\Support\Enums\InvoiceStatus::Draft)
            <form method="POST" action="{{ route('invoices.mark-sent', $invoice->ulid) }}">
                @csrf
                <button class="px-3 py-2 text-sm text-blue-600 bg-blue-50 border border-blue-200 rounded-xl hover:bg-blue-100 transition">
                    📤 تحديد كمُرسَلة
                </button>
            </form>
            @endif
            <form method="POST" action="{{ route('invoices.mark-paid', $invoice->ulid) }}">
                @csrf
                <button class="px-3 py-2 text-sm text-teal-600 bg-teal-50 border border-teal-200 rounded-xl hover:bg-teal-100 transition">
                    ✅ تسجيل الدفع
                </button>
            </form>
            @endif

            {{-- طباعة --}}
            <button onclick="window.print()"
                    class="px-3 py-2 text-sm text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                🖨️ طباعة / PDF
            </button>

            {{-- تعديل --}}
            @if($invoice->status === \App\Support\Enums\InvoiceStatus::Draft)
            <a href="{{ route('invoices.edit', $invoice->ulid) }}"
               class="px-3 py-2 text-sm text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                ✏️ تعديل
            </a>
            @endif

            {{-- إلغاء --}}
            @if(!in_array($invoice->status, [\App\Support\Enums\InvoiceStatus::Paid, \App\Support\Enums\InvoiceStatus::Cancelled]))
            <form method="POST" action="{{ route('invoices.cancel', $invoice->ulid) }}"
                  onsubmit="return confirm('هل تريد إلغاء هذه الفاتورة؟')">
                @csrf
                <button class="px-3 py-2 text-sm text-red-500 bg-white border border-red-200 rounded-xl hover:bg-red-50 transition">
                    إلغاء
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- ورقة الفاتورة --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-8 shadow-sm print:shadow-none print:border-0" id="invoice-paper">

        {{-- رأس الفاتورة --}}
        <div class="flex justify-between items-start mb-8">
            <div>
                <h2 class="text-3xl font-bold text-indigo-600">فاتورة</h2>
                <p class="text-gray-500 text-sm mt-1">{{ $invoice->number }}</p>
                @if($invoice->title)
                <p class="text-gray-700 font-medium mt-1">{{ $invoice->title }}</p>
                @endif
            </div>
            <div class="text-left text-sm text-gray-500 space-y-1">
                <div class="flex gap-6">
                    <div>
                        <p class="text-xs text-gray-400">تاريخ الإصدار</p>
                        <p class="font-medium text-gray-700">{{ $invoice->issue_date->format('Y/m/d') }}</p>
                    </div>
                    @if($invoice->due_date)
                    <div>
                        <p class="text-xs text-gray-400">تاريخ الاستحقاق</p>
                        <p class="font-medium {{ $invoice->isOverdue() ? 'text-red-600' : 'text-gray-700' }}">
                            {{ $invoice->due_date->format('Y/m/d') }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- بيانات العميل --}}
        <div class="grid grid-cols-2 gap-8 mb-8 pb-6 border-b border-gray-100">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">إلى</p>
                <p class="font-semibold text-gray-900">{{ $invoice->client->name }}</p>
                @if($invoice->client->company)
                <p class="text-sm text-gray-600">{{ $invoice->client->company }}</p>
                @endif
                @if($invoice->client->email)
                <p class="text-sm text-gray-500">{{ $invoice->client->email }}</p>
                @endif
                @if($invoice->client->phone)
                <p class="text-sm text-gray-500">{{ $invoice->client->phone }}</p>
                @endif
            </div>
            @if($invoice->project)
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">المشروع</p>
                <p class="font-semibold text-gray-900">{{ $invoice->project->name }}</p>
            </div>
            @endif
        </div>

        {{-- جدول البنود --}}
        <table class="w-full text-sm mb-6">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="text-right py-2 text-gray-500 font-medium w-1/2">الوصف</th>
                    <th class="text-center py-2 text-gray-500 font-medium w-1/6">الكمية</th>
                    <th class="text-left py-2 text-gray-500 font-medium w-1/6">سعر الوحدة</th>
                    <th class="text-left py-2 text-gray-500 font-medium w-1/6">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr class="border-b border-gray-50">
                    <td class="py-3 text-gray-800">{{ $item->description }}</td>
                    <td class="py-3 text-center text-gray-600">{{ number_format($item->quantity, 2) }}</td>
                    <td class="py-3 text-gray-600">{{ number_format($item->unit_price, 2) }} {{ $invoice->currency }}</td>
                    <td class="py-3 font-medium text-gray-800">{{ number_format($item->total, 2) }} {{ $invoice->currency }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- الإجماليات --}}
        <div class="flex justify-end mb-6">
            <div class="w-64 space-y-2 text-sm">
                <div class="flex justify-between text-gray-600">
                    <span>المجموع الفرعي</span>
                    <span>{{ number_format($invoice->subtotal, 2) }} {{ $invoice->currency }}</span>
                </div>
                @if($invoice->tax_rate > 0)
                <div class="flex justify-between text-gray-600">
                    <span>ضريبة ({{ $invoice->tax_rate }}%)</span>
                    <span>{{ number_format($invoice->tax_amount, 2) }} {{ $invoice->currency }}</span>
                </div>
                @endif
                @if($invoice->discount > 0)
                <div class="flex justify-between text-gray-600">
                    <span>خصم</span>
                    <span class="text-red-600">-{{ number_format($invoice->discount, 2) }} {{ $invoice->currency }}</span>
                </div>
                @endif
                <div class="flex justify-between font-bold text-gray-900 text-base pt-2 border-t border-gray-200">
                    <span>الإجمالي</span>
                    <span class="text-indigo-700">{{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</span>
                </div>
            </div>
        </div>

        {{-- ملاحظات وشروط --}}
        @if($invoice->notes || $invoice->terms)
        <div class="border-t border-gray-100 pt-6 space-y-3 text-sm text-gray-600">
            @if($invoice->notes)
            <div>
                <p class="font-semibold text-gray-700 mb-1">ملاحظات:</p>
                <p class="whitespace-pre-line">{{ $invoice->notes }}</p>
            </div>
            @endif
            @if($invoice->terms)
            <div>
                <p class="font-semibold text-gray-700 mb-1">الشروط والأحكام:</p>
                <p class="whitespace-pre-line text-gray-500">{{ $invoice->terms }}</p>
            </div>
            @endif
        </div>
        @endif

        {{-- حالة الدفع --}}
        @if($invoice->status === \App\Support\Enums\InvoiceStatus::Paid)
        <div class="absolute top-8 left-8 opacity-10 print:opacity-20">
            <div class="border-4 border-teal-500 text-teal-500 font-bold text-4xl px-6 py-3 rounded-xl rotate-[-15deg]">
                مدفوعة
            </div>
        </div>
        @endif
    </div>

</div>

<style>
@media print {
    nav, header, .print\:hidden { display: none !important; }
    body { background: white; }
    #invoice-paper { margin: 0; }
}
</style>
@endsection

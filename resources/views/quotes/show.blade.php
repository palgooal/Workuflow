@extends('layouts.app')

@section('title', 'عرض السعر ' . $quote->number)

@push('styles')
<style>
@media print {
    nav, header, .print\:hidden { display: none !important; }
    body { background: white !important; }
    .quote-paper { box-shadow: none !important; border: none !important; }
}
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto space-y-4">

    {{-- شريط الإجراءات --}}
    <div class="flex items-center justify-between print:hidden">
        <div class="flex items-center gap-3">
            <a href="{{ route('quotes.index') }}"
               class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-lg font-bold text-gray-900">{{ $quote->number }}</h1>
                @if($quote->title)
                    <p class="text-sm text-gray-500">{{ $quote->title }}</p>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2">

            {{-- طباعة --}}
            <button onclick="window.print()"
                    class="inline-flex items-center gap-2 px-3.5 py-2 text-sm text-gray-600
                           border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                طباعة
            </button>

            {{-- رابط العميل --}}
            @if($quote->status->isPending() || $quote->status === \App\Support\Enums\QuoteStatus::Accepted)
            <button onclick="navigator.clipboard.writeText('{{ $quote->portalUrl() }}').then(() => alert('تم نسخ الرابط ✓'))"
                    class="inline-flex items-center gap-2 px-3.5 py-2 text-sm text-blue-600
                           border border-blue-200 bg-blue-50 rounded-xl hover:bg-blue-100 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                نسخ رابط العميل
            </button>
            @endif

            {{-- تعديل --}}
            @if($quote->status->isEditable())
            <a href="{{ route('quotes.edit', $quote->ulid) }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 text-sm text-gray-600
                      border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                ✏️ تعديل
            </a>
            @endif

            {{-- تسجيل كمُرسَل --}}
            @if($quote->status->canBeSent())
            <form method="POST" action="{{ route('quotes.mark-sent', $quote->ulid) }}" class="inline">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 px-3.5 py-2 text-sm text-white
                               bg-blue-600 rounded-xl hover:bg-blue-700 transition">
                    📤 تسجيل كمُرسَل
                </button>
            </form>
            @endif

            {{-- تحويل لفاتورة --}}
            @if($quote->status->canConvert())
            <form method="POST" action="{{ route('quotes.convert', $quote->ulid) }}" class="inline">
                @csrf
                <button type="submit"
                        onclick="return confirm('تحويل هذا العرض إلى فاتورة؟')"
                        class="inline-flex items-center gap-2 px-3.5 py-2 text-sm text-white
                               bg-teal-600 rounded-xl hover:bg-teal-700 transition">
                    🧾 تحويل لفاتورة
                </button>
            </form>
            @endif

        </div>
    </div>

    @if(session('success'))
        <div class="bg-teal-50 border border-teal-200 text-teal-700 rounded-xl px-4 py-3 text-sm print:hidden">
            {{ session('success') }}
        </div>
    @endif

    {{-- ورقة العرض --}}
    <div class="quote-paper bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- رأس الورقة --}}
        <div class="bg-gradient-to-l from-indigo-600 to-indigo-800 text-white p-8">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-2xl font-bold tracking-wide">عرض سعر</h2>
                    <p class="text-indigo-200 text-sm mt-1">{{ $quote->number }}</p>
                    @if($quote->title)
                        <p class="text-indigo-100 mt-1">{{ $quote->title }}</p>
                    @endif
                </div>
                <div class="text-left">
                    {{-- الحالة --}}
                    @php
                        $isExp = $quote->isExpired();
                        $slabel = $isExp ? 'منتهي الصلاحية' : $quote->status->label();
                    @endphp
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold
                                 {{ $isExp ? 'bg-orange-200 text-orange-800' : 'bg-white/20 text-white' }}">
                        {{ $quote->status->icon() }} {{ $slabel }}
                    </span>
                    @if($quote->status === \App\Support\Enums\QuoteStatus::Accepted && $quote->accepted_at)
                        <div class="text-xs text-indigo-200 mt-1">
                            قُبِل في {{ $quote->accepted_at->format('d/m/Y H:i') }}
                        </div>
                    @endif
                    @if($quote->status === \App\Support\Enums\QuoteStatus::Viewed && $quote->viewed_at)
                        <div class="text-xs text-indigo-200 mt-1">
                            شُوهِد في {{ $quote->viewed_at->format('d/m/Y H:i') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="p-8 space-y-6">

            {{-- بيانات العميل والمواعيد --}}
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">مُقدَّم لـ</p>
                    <p class="font-semibold text-gray-900 text-lg">{{ $quote->client->name }}</p>
                    @if($quote->client->company)
                        <p class="text-gray-500 text-sm">{{ $quote->client->company }}</p>
                    @endif
                    @if($quote->client->email)
                        <p class="text-gray-500 text-sm">{{ $quote->client->email }}</p>
                    @endif
                    @if($quote->project)
                        <p class="text-xs text-indigo-600 mt-1">📁 {{ $quote->project->name }}</p>
                    @endif
                </div>
                <div class="text-left space-y-1">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400">تاريخ الإصدار</span>
                        <span class="text-gray-700">{{ $quote->issue_date->format('d/m/Y') }}</span>
                    </div>
                    @if($quote->valid_until)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400">صالح حتى</span>
                        <span class="{{ $isExp ? 'text-red-600 font-semibold' : 'text-gray-700' }}">
                            {{ $quote->valid_until->format('d/m/Y') }}
                            @if($isExp) (منتهي) @endif
                        </span>
                    </div>
                    @endif
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400">العملة</span>
                        <span class="text-gray-700 font-medium">{{ $quote->currency }}</span>
                    </div>
                </div>
            </div>

            {{-- جدول البنود --}}
            <div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b-2 border-gray-200">
                            <th class="text-right text-xs font-semibold text-gray-500 pb-2 w-full">البيان</th>
                            <th class="text-center text-xs font-semibold text-gray-500 pb-2 w-20 px-3">الكمية</th>
                            <th class="text-left text-xs font-semibold text-gray-500 pb-2 w-28 px-2">سعر الوحدة</th>
                            <th class="text-left text-xs font-semibold text-gray-500 pb-2 w-28">الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($quote->items as $item)
                        <tr>
                            <td class="py-3 text-gray-800">{{ $item->description }}</td>
                            <td class="py-3 text-center text-gray-600 px-3">
                                {{ number_format($item->quantity, 2) }}
                            </td>
                            <td class="py-3 text-gray-600 px-2">
                                {{ number_format($item->unit_price, 2) }}
                            </td>
                            <td class="py-3 font-medium text-gray-800">
                                {{ number_format($item->total, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- الإجماليات --}}
            <div class="flex justify-end">
                <div class="w-64 space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>المجموع الفرعي</span>
                        <span>{{ number_format($quote->subtotal, 2) }} {{ $quote->currency }}</span>
                    </div>
                    @if($quote->tax_rate > 0)
                    <div class="flex justify-between text-gray-600">
                        <span>ضريبة ({{ number_format($quote->tax_rate, 1) }}%)</span>
                        <span>{{ number_format($quote->tax_amount, 2) }} {{ $quote->currency }}</span>
                    </div>
                    @endif
                    @if($quote->discount > 0)
                    <div class="flex justify-between text-red-600">
                        <span>خصم</span>
                        <span>- {{ number_format($quote->discount, 2) }} {{ $quote->currency }}</span>
                    </div>
                    @endif
                    <div class="border-t-2 border-gray-200 pt-2 flex justify-between font-bold text-gray-900 text-base">
                        <span>الإجمالي النهائي</span>
                        <span>{{ number_format($quote->total, 2) }} {{ $quote->currency }}</span>
                    </div>
                </div>
            </div>

            {{-- ملاحظات --}}
            @if($quote->notes)
            <div class="border-t border-gray-100 pt-4">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">ملاحظات</p>
                <p class="text-sm text-gray-600 whitespace-pre-line">{{ $quote->notes }}</p>
            </div>
            @endif

            {{-- الشروط --}}
            @if($quote->terms)
            <div class="border-t border-gray-100 pt-4">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">الشروط والأحكام</p>
                <p class="text-sm text-gray-500 whitespace-pre-line">{{ $quote->terms }}</p>
            </div>
            @endif

            {{-- سبب الرفض --}}
            @if($quote->status === \App\Support\Enums\QuoteStatus::Rejected && $quote->rejection_reason)
            <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                <p class="text-xs text-red-400 font-semibold mb-1">سبب الرفض</p>
                <p class="text-sm text-red-700">{{ $quote->rejection_reason }}</p>
                @if($quote->rejected_at)
                    <p class="text-xs text-red-400 mt-1">{{ $quote->rejected_at->format('d/m/Y H:i') }}</p>
                @endif
            </div>
            @endif

        </div>
    </div>

    {{-- حذف --}}
    @if($quote->status->isEditable())
    <div class="flex justify-start print:hidden">
        <form method="POST" action="{{ route('quotes.destroy', $quote->ulid) }}">
            @csrf
            @method('DELETE')
            <button type="submit"
                    onclick="return confirm('حذف هذا العرض نهائياً؟')"
                    class="text-sm text-red-500 hover:text-red-700 hover:bg-red-50 px-3 py-2 rounded-lg transition">
                🗑️ حذف العرض
            </button>
        </form>
    </div>
    @endif

</div>
@endsection

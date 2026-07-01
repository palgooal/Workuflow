<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دفع الفاتورة {{ $invoice->number }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">
    @php
        $isPaid      = $invoice->status === \App\Support\Enums\InvoiceStatus::Paid;
        $isCancelled = $invoice->status === \App\Support\Enums\InvoiceStatus::Cancelled;
        $isPayable   = ! $isPaid && ! $isCancelled;
    @endphp

    {{-- شريط علوي بسيط --}}
    <div class="bg-white border-b border-slate-100 px-4 py-3 no-print">
        <div class="max-w-2xl mx-auto flex items-center justify-between">
            <span class="text-sm font-semibold text-slate-700">دراهم</span>
            <span class="text-xs text-slate-400">دفع آمن عبر بوابة الدفع</span>
        </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 py-8 space-y-4">

        {{-- رسائل الحالة (flash لمرة واحدة) --}}
        @if(session('success'))
        <div class="bg-teal-50 border border-teal-200 rounded-2xl p-5 text-center no-print">
            <div class="text-3xl mb-2">✅</div>
            <p class="font-semibold text-teal-800">{{ session('success') }}</p>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-2xl p-5 text-center no-print">
            <div class="text-2xl mb-2">⚠️</div>
            <p class="font-semibold text-red-700">{{ session('error') }}</p>
        </div>
        @endif

        @if(session('info'))
        <div class="bg-slate-100 border border-slate-200 rounded-2xl p-5 text-center no-print">
            <p class="text-slate-600">{{ session('info') }}</p>
        </div>
        @endif

        {{-- عملة الفاتورة غير مدعومة للدفع الإلكتروني عبر Togo — الفاتورة تبقى
             معروضة/قابلة للطباعة كالمعتاد، فقط زر الدفع يختفي. --}}
        @if($isPayable && ! $isCurrencySupported)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 text-center no-print">
            <div class="text-2xl mb-2">⚠️</div>
            <p class="font-semibold text-amber-800">هذه العملة غير مدعومة للدفع الإلكتروني حالياً. العملات المدعومة: ILS, USD.</p>
        </div>
        @endif

        {{-- حالة دائمة: مدفوعة (تظهر حتى بدون flash عند العودة للرابط لاحقاً) --}}
        @if($isPaid && ! session('success'))
        <div class="bg-teal-50 border border-teal-200 rounded-2xl p-5 text-center">
            <div class="text-3xl mb-2">✅</div>
            <p class="font-semibold text-teal-800">تم دفع هذه الفاتورة بنجاح. شكراً لك.</p>
        </div>
        @endif

        {{-- بطاقة الفاتورة --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">

            {{-- رأس ملوّن --}}
            <div class="bg-gradient-to-l from-brand to-brand-700 text-white p-8 text-center">
                <p class="text-brand-100 text-sm mb-1">فاتورة رقم {{ $invoice->number }}</p>
                <p class="text-4xl font-extrabold nums">{{ number_format($invoice->total, \App\Support\Helpers\Currency::decimals($invoice->currency)) }} {{ $invoice->currency }}</p>
                <div class="mt-3">
                    @if($isPaid)
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold bg-white text-teal-700">
                            ✅ مدفوعة
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold bg-white/20">
                            {{ $invoice->status->icon() }} {{ $invoice->status->label() }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="p-8 space-y-6">

                {{-- بيانات العميل والتواريخ --}}
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-wide mb-2">إلى</p>
                        <p class="font-semibold text-slate-900">{{ $invoice->client->name }}</p>
                        @if($invoice->client->company)
                            <p class="text-slate-500 text-sm">{{ $invoice->client->company }}</p>
                        @endif
                    </div>
                    <div class="text-left space-y-1.5">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">تاريخ الإصدار</span>
                            <span class="text-slate-700 nums">{{ $invoice->issue_date?->format('Y/m/d') ?? '—' }}</span>
                        </div>
                        @if($invoice->due_date)
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">تاريخ الاستحقاق</span>
                            <span class="text-slate-700 nums">{{ $invoice->due_date->format('Y/m/d') }}</span>
                        </div>
                        @endif
                        @if($isPaid && $invoice->paid_at)
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">تاريخ الدفع</span>
                            <span class="text-teal-700 font-semibold nums">{{ $invoice->paid_at->format('Y/m/d') }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- جدول البنود — يظهر فقط إذا وُجدت بنود --}}
                @if($invoice->items->isNotEmpty())
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide mb-2">بنود الفاتورة</p>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b-2 border-slate-200">
                                <th class="text-right text-xs font-semibold text-slate-500 pb-2">الوصف</th>
                                <th class="text-center text-xs font-semibold text-slate-500 pb-2 w-20 px-3">الكمية</th>
                                <th class="text-left text-xs font-semibold text-slate-500 pb-2 w-28 px-2">سعر الوحدة</th>
                                <th class="text-left text-xs font-semibold text-slate-500 pb-2 w-28">الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($invoice->items as $item)
                            <tr>
                                <td class="py-3 text-slate-800">{{ $item->description }}</td>
                                <td class="py-3 text-center text-slate-600 px-3 nums">{{ number_format($item->quantity, \App\Support\Helpers\Currency::decimals($invoice->currency)) }}</td>
                                <td class="py-3 text-slate-600 px-2 nums">{{ number_format($item->unit_price, \App\Support\Helpers\Currency::decimals($invoice->currency)) }}</td>
                                <td class="py-3 font-medium text-slate-800 nums">{{ number_format($item->total, \App\Support\Helpers\Currency::decimals($invoice->currency)) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- الإجماليات --}}
                <div class="flex justify-end">
                    <div class="w-64 space-y-2 text-sm">
                        @if($invoice->discount > 0 || $invoice->tax_amount > 0)
                        <div class="flex justify-between text-slate-600">
                            <span>المجموع الفرعي</span>
                            <span class="nums">{{ number_format($invoice->subtotal, \App\Support\Helpers\Currency::decimals($invoice->currency)) }} {{ $invoice->currency }}</span>
                        </div>
                        @endif
                        @if($invoice->tax_amount > 0)
                        <div class="flex justify-between text-slate-600">
                            <span>الضريبة ({{ number_format($invoice->tax_rate, 0) }}%)</span>
                            <span class="nums">{{ number_format($invoice->tax_amount, \App\Support\Helpers\Currency::decimals($invoice->currency)) }} {{ $invoice->currency }}</span>
                        </div>
                        @endif
                        @if($invoice->discount > 0)
                        <div class="flex justify-between text-red-600">
                            <span>الخصم</span>
                            <span class="nums">- {{ number_format($invoice->discount_amount, \App\Support\Helpers\Currency::decimals($invoice->currency)) }} {{ $invoice->currency }}</span>
                        </div>
                        @endif
                        <div class="border-t-2 border-slate-200 pt-2 flex justify-between font-bold text-slate-900 text-base">
                            <span>الإجمالي</span>
                            <span class="nums">{{ number_format($invoice->total, \App\Support\Helpers\Currency::decimals($invoice->currency)) }} {{ $invoice->currency }}</span>
                        </div>
                    </div>
                </div>
                @endif

                @if($invoice->notes)
                <div class="border-t border-slate-100 pt-4">
                    <p class="text-xs text-slate-400 uppercase tracking-wide mb-1">ملاحظات</p>
                    <p class="text-sm text-slate-600 whitespace-pre-line">{{ $invoice->notes }}</p>
                </div>
                @endif

            </div>
        </div>

        {{-- زر الدفع — يظهر فقط إذا كانت الفاتورة قابلة للدفع وعملتها مدعومة إلكترونياً --}}
        @if($isPayable && $isCurrencySupported)
        <form method="POST" action="{{ route('pay.invoice.checkout', $invoice->ulid) }}" class="no-print">
            @csrf
            <button type="submit"
                    class="w-full py-4 bg-brand text-white font-bold rounded-2xl shadow-sm
                           hover:bg-brand-600 transition text-base flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a4 4 0 00-8 0v2m-2 0h12a2 2 0 012 2v7a2 2 0 01-2 2H7a2 2 0 01-2-2v-7a2 2 0 012-2z"/>
                </svg>
                ادفع الآن
            </button>
        </form>
        @elseif($isCancelled)
        <div class="bg-slate-100 border border-slate-200 rounded-2xl p-5 text-center">
            <p class="text-slate-500 font-medium">❌ هذه الفاتورة ملغاة ولا يمكن دفعها.</p>
        </div>
        @endif

        <p class="text-xs text-slate-400 text-center pb-4">
            مدعوم بـ <strong>دراهم</strong> — دفع آمن عبر بوابة الدفع
        </p>

    </div>
</body>
</html>

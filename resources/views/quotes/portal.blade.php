<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض سعر {{ $quote->number }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen" x-data="{ showRejectForm: false }">

    {{-- شريط علوي بسيط --}}
    <div class="bg-white border-b border-gray-100 px-4 py-3 no-print">
        <div class="max-w-3xl mx-auto flex items-center justify-between">
            <span class="text-sm font-semibold text-gray-700">دراهم</span>
            <button onclick="window.print()"
                    class="text-xs text-gray-500 hover:text-gray-700 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                طباعة / PDF
            </button>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-4 py-8 space-y-4">

        {{-- رسائل الحالة --}}
        @if(session('portal_success'))
        <div class="bg-teal-50 border border-teal-200 rounded-2xl p-5 text-center no-print">
            <div class="text-3xl mb-2">✅</div>
            <p class="font-semibold text-teal-800 text-lg">{{ session('portal_success') }}</p>
        </div>
        @endif

        @if(session('portal_info'))
        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-5 text-center no-print">
            <div class="text-2xl mb-2">📩</div>
            <p class="text-gray-600">{{ session('portal_info') }}</p>
        </div>
        @endif

        @if($isExpired)
        <div class="bg-orange-50 border border-orange-200 rounded-2xl p-4 text-center no-print">
            <p class="text-orange-700 font-medium">⏰ انتهت صلاحية هذا العرض في {{ $quote->valid_until->format('d/m/Y') }}</p>
            <p class="text-sm text-orange-500 mt-1">للاستفسار يرجى التواصل مع مقدّم العرض</p>
        </div>
        @endif

        {{-- ورقة العرض --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">

            {{-- رأس ملوّن --}}
            <div class="bg-gradient-to-l from-indigo-600 to-indigo-800 text-white p-8">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-2xl font-bold">عرض سعر</h1>
                        <p class="text-indigo-200 text-sm mt-1">{{ $quote->number }}</p>
                        @if($quote->title)
                            <p class="text-indigo-100 mt-1">{{ $quote->title }}</p>
                        @endif
                    </div>
                    <div class="text-left">
                        <span class="inline-block px-3 py-1.5 rounded-full text-xs font-bold bg-white/20">
                            {{ $quote->status->icon() }} {{ $quote->status->label() }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-8 space-y-6">

                {{-- بيانات العميل والتواريخ --}}
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">مُقدَّم لـ</p>
                        <p class="font-semibold text-gray-900 text-lg">{{ $quote->client->name }}</p>
                        @if($quote->client->company)
                            <p class="text-gray-500 text-sm">{{ $quote->client->company }}</p>
                        @endif
                        @if($quote->project)
                            <p class="text-xs text-indigo-600 mt-1">📁 {{ $quote->project->name }}</p>
                        @endif
                    </div>
                    <div class="text-left space-y-1.5">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">تاريخ الإصدار</span>
                            <span class="text-gray-700">{{ $quote->issue_date->format('d/m/Y') }}</span>
                        </div>
                        @if($quote->valid_until)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">صالح حتى</span>
                            <span class="{{ $isExpired ? 'text-red-600 font-semibold' : 'text-gray-700' }}">
                                {{ $quote->valid_until->format('d/m/Y') }}
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
                                <th class="text-right text-xs font-semibold text-gray-500 pb-2">البيان</th>
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
                        <div class="border-t-2 border-gray-200 pt-2 flex justify-between
                                    font-bold text-gray-900 text-base">
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

                @if($quote->terms)
                <div class="border-t border-gray-100 pt-4">
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">الشروط والأحكام</p>
                    <p class="text-sm text-gray-500 whitespace-pre-line">{{ $quote->terms }}</p>
                </div>
                @endif

            </div>
        </div>

        {{-- أزرار القبول والرفض — تظهر فقط إذا كان العرض في انتظار الرد --}}
        @if(in_array($quote->status, [\App\Support\Enums\QuoteStatus::Sent, \App\Support\Enums\QuoteStatus::Viewed]) && !$isExpired)
        <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4 no-print">
            <h2 class="text-base font-semibold text-gray-800 text-center">ردّك على هذا العرض</h2>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">

                {{-- قبول --}}
                <form method="POST" action="{{ route('quotes.accept', $quote->token) }}" class="flex-1 sm:max-w-xs">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('تأكيد قبول عرض السعر {{ $quote->number }}؟')"
                            class="w-full py-3.5 bg-teal-600 text-white font-semibold rounded-xl
                                   hover:bg-teal-700 transition text-sm flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                  d="M5 13l4 4L19 7"/>
                        </svg>
                        قبول العرض
                    </button>
                </form>

                {{-- رفض --}}
                <button type="button" @click="showRejectForm = !showRejectForm"
                        class="flex-1 sm:max-w-xs py-3.5 border-2 border-red-200 text-red-600 font-semibold
                               rounded-xl hover:bg-red-50 transition text-sm flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    رفض العرض
                </button>
            </div>

            {{-- فورم الرفض --}}
            <div x-show="showRejectForm" x-cloak x-transition
                 class="bg-red-50 border border-red-200 rounded-xl p-4">
                <form method="POST" action="{{ route('quotes.reject', $quote->token) }}" class="space-y-3">
                    @csrf
                    <label class="block text-sm font-medium text-red-700">
                        سبب الرفض
                        <span class="font-normal text-red-500">(اختياري — يساعدنا على التحسين)</span>
                    </label>
                    <textarea name="rejection_reason" rows="3"
                              placeholder="مثال: السعر مرتفع / التوقيت لا يناسبنا / نحتاج تعديلات..."
                              class="w-full px-3 py-2.5 text-sm rounded-xl border border-red-200
                                     focus:outline-none focus:ring-2 focus:ring-red-400 bg-white"></textarea>
                    <button type="submit"
                            class="w-full py-2.5 bg-red-600 text-white text-sm font-semibold
                                   rounded-xl hover:bg-red-700 transition">
                        تأكيد الرفض
                    </button>
                </form>
            </div>

            <p class="text-xs text-gray-400 text-center">
                سيتلقى مقدّم العرض إشعاراً فور ردّك.
                @if($quote->valid_until)
                    هذا العرض صالح حتى {{ $quote->valid_until->format('d/m/Y') }}.
                @endif
            </p>
        </div>
        @endif

        {{-- حالة: مقبول --}}
        @if($quote->status === \App\Support\Enums\QuoteStatus::Accepted)
        <div class="bg-teal-50 border border-teal-200 rounded-2xl p-6 text-center no-print">
            <div class="text-4xl mb-3">✅</div>
            <p class="font-semibold text-teal-800 text-lg">تم قبول هذا العرض</p>
            @if($quote->accepted_at)
                <p class="text-sm text-teal-600 mt-1">في {{ $quote->accepted_at->format('d/m/Y الساعة H:i') }}</p>
            @endif
        </div>
        @endif

        {{-- حالة: مرفوض --}}
        @if($quote->status === \App\Support\Enums\QuoteStatus::Rejected)
        <div class="bg-red-50 border border-red-200 rounded-2xl p-6 text-center no-print">
            <div class="text-4xl mb-3">❌</div>
            <p class="font-semibold text-red-800">تم رفض هذا العرض</p>
            @if($quote->rejection_reason)
                <p class="text-sm text-red-600 mt-2">{{ $quote->rejection_reason }}</p>
            @endif
        </div>
        @endif

        {{-- حالة: محوّل لفاتورة --}}
        @if($quote->status === \App\Support\Enums\QuoteStatus::Converted)
        <div class="bg-purple-50 border border-purple-200 rounded-2xl p-6 text-center no-print">
            <div class="text-4xl mb-3">🧾</div>
            <p class="font-semibold text-purple-800">تم تحويل هذا العرض إلى فاتورة</p>
        </div>
        @endif

        {{-- Footer --}}
        <p class="text-xs text-gray-400 text-center pb-4">
            مدعوم بـ <strong>دراهم</strong> — نظام إدارة مالي للمستقلين
        </p>

    </div>

</body>
</html>

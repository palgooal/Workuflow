@extends('layouts.app')

@section('title', 'فاتورة ' . $invoice->number)

@section('content')
<div class="max-w-4xl mx-auto space-y-4">

    {{-- شريط الإجراءات --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('clients.show', $invoice->client->public_id) }}"
               class="p-2 text-muted hover:text-ink hover:bg-slate-100 rounded-lg transition"
               aria-label="العودة إلى العميل">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-ink">{{ $invoice->number }}</h1>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $invoice->status->badgeClass() }}">
                        {{ $invoice->status->icon() }} {{ $invoice->status->label() }}
                    </span>
                    @if($invoice->isOverdue())
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        متأخرة
                    </span>
                    @endif
                </div>
                <p class="text-sm text-muted">{{ $invoice->client->name }}
                    @if($invoice->project) — {{ $invoice->project->name }} @endif
                </p>
            </div>
        </div>

        @php $canSendEmail = auth()->user()->currentPlan()->can('send_invoice_email'); @endphp
        <div class="flex items-center gap-2 flex-wrap print:hidden" x-data="{ showSendModal: false }">

            {{-- زر إرسال للعميل --}}
            @if($invoice->status !== \App\Support\Enums\InvoiceStatus::Cancelled)
            @if($canSendEmail)
            <button @click="showSendModal = true"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm text-brand bg-brand-50 border border-brand/30 rounded-xl hover:bg-brand-100 transition"
                    aria-label="إرسال الفاتورة بالبريد الإلكتروني">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                إرسال للعميل
            </button>
            @else
            {{-- Free plan: زر مقفل مع tooltip --}}
            <div class="relative group">
                <a href="{{ route('billing.upgrade') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 text-sm text-muted bg-slate-50 border border-subtle rounded-xl transition"
                   aria-label="إرسال الفاتورة — متاح في Pro">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    إرسال للعميل
                </a>
                <div class="absolute bottom-full mb-1.5 start-0 hidden group-hover:block z-20 pointer-events-none">
                    <div class="bg-slate-800 text-white text-xs rounded-lg px-2.5 py-1.5 whitespace-nowrap">
                        إرسال الفاتورة بالبريد — متاح في Pro ⚡
                        <div class="absolute top-full end-3 border-4 border-transparent border-t-slate-800"></div>
                    </div>
                </div>
            </div>
            @endif

            {{-- زر واتساب + PDF معاً --}}
            @php
                $whatsappMessage = "مرحباً {$invoice->client->name}،\n\nيسعدني إشعارك بأن الفاتورة رقم *{$invoice->number}* بمبلغ *" . number_format($invoice->total, 2) . " {$invoice->currency}* جاهزة.\n\nتاريخ الاستحقاق: " . ($invoice->due_date?->format('Y/m/d') ?? '—') . "\n\nمرفق ملف الفاتورة PDF 📎\n\nشكراً لتعاملك معنا 🙏";
                $whatsappPhone = preg_replace('/[^0-9]/', '', $invoice->client->phone ?? '');
                $whatsappUrl = 'https://wa.me/' . $whatsappPhone . '?text=' . rawurlencode($whatsappMessage);
                $pdfUrl = route('invoices.pdf', $invoice->ulid);
            @endphp
            @if($whatsappPhone)
            <button onclick="sendWhatsappWithPdf('{{ $whatsappUrl }}', '{{ $pdfUrl }}')"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl hover:bg-green-100 transition"
                    aria-label="إرسال عبر واتساب مع PDF">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                واتساب + PDF
            </button>
            @endif

            {{-- زر تنزيل PDF --}}
            <a href="{{ route('invoices.pdf', $invoice->ulid) }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-2 text-sm text-red-600 bg-red-50 border border-red-200 rounded-xl hover:bg-red-100 transition"
               aria-label="تنزيل PDF">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                PDF
            </a>
            @endif

            {{-- Modal إرسال الفاتورة (Pro/Business فقط) --}}
            @if($canSendEmail)
            <div x-show="showSendModal" x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center p-4"
                 style="display:none">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showSendModal = false"></div>
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4" @click.stop>
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-bold text-ink">إرسال الفاتورة بالبريد</h3>
                        <button @click="showSendModal = false"
                                class="text-muted hover:text-ink transition"
                                aria-label="إغلاق">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-3 text-sm space-y-1.5">
                        <div class="flex justify-between text-muted">
                            <span>الفاتورة</span>
                            <span class="font-semibold text-ink">{{ $invoice->number }}</span>
                        </div>
                        <div class="flex justify-between text-muted">
                            <span>العميل</span>
                            <span class="font-semibold text-ink">{{ $invoice->client->name }}</span>
                        </div>
                        <div class="flex justify-between text-muted">
                            <span>المبلغ</span>
                            <span class="font-bold text-brand-600 nums">{{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('invoices.send-client', $invoice->ulid) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-ink mb-1.5">البريد الإلكتروني للمستلم</label>
                            <input type="email" name="recipient_email"
                                   value="{{ old('recipient_email', $invoice->client->email ?? '') }}"
                                   required placeholder="client@example.com"
                                   class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-subtle focus:outline-none focus:ring-2 focus:ring-accent/40">
                            <p class="text-xs text-muted mt-1">سيُرسَل باسمك ({{ auth()->user()->name }}) مع تفاصيل الفاتورة.</p>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" @click="showSendModal = false"
                                    class="flex-1 py-2.5 text-sm text-muted border border-subtle rounded-xl hover:bg-slate-50 transition">
                                إلغاء
                            </button>
                            <button type="submit"
                                    class="flex-1 inline-flex items-center justify-center gap-2 py-2.5 text-sm text-white bg-brand rounded-xl hover:bg-brand-600 transition font-medium">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                إرسال الآن
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif {{-- canSendEmail --}}

            {{-- تغيير الحالة --}}
            @if($invoice->status !== \App\Support\Enums\InvoiceStatus::Paid && $invoice->status !== \App\Support\Enums\InvoiceStatus::Cancelled)
            @if($invoice->status === \App\Support\Enums\InvoiceStatus::Draft)
            <form method="POST" action="{{ route('invoices.mark-sent', $invoice->ulid) }}">
                @csrf
                <button class="inline-flex items-center gap-1.5 px-3 py-2 text-sm text-blue-600 bg-blue-50 border border-blue-200 rounded-xl hover:bg-blue-100 transition"
                        aria-label="تحديد الفاتورة كمُرسَلة">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    تحديد كمُرسَلة
                </button>
            </form>
            @endif
            {{-- زر يفتح modal اختيار الصندوق --}}
            <button type="button"
                    x-data
                    @click="$dispatch('open-pay-modal')"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm text-success-700 bg-success-soft border border-success/30 rounded-xl hover:bg-emerald-100 transition"
                    aria-label="تسجيل دفع هذه الفاتورة">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                تسجيل الدفع
            </button>

            {{-- زر ادفع الآن — رابط الدفع العام عبر بوابة الدفع (التحصيل عبر دراهم) --}}
            <a href="{{ route('pay.invoice.show', $invoice->ulid) }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-2 text-sm text-brand bg-brand-50 border border-brand/30 rounded-xl hover:bg-brand-100 transition"
               aria-label="فتح رابط الدفع العام للعميل">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a4 4 0 00-8 0v2m-2 0h12a2 2 0 012 2v7a2 2 0 01-2 2H7a2 2 0 01-2-2v-7a2 2 0 012-2z"/>
                </svg>
                ادفع الآن
            </a>
            @endif

            {{-- طباعة --}}
            <button onclick="window.print()"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm text-muted bg-white border border-subtle rounded-xl hover:bg-slate-50 transition"
                    aria-label="طباعة الفاتورة">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                طباعة / PDF
            </button>

            {{-- تعديل --}}
            @if($invoice->status === \App\Support\Enums\InvoiceStatus::Draft)
            <a href="{{ route('invoices.edit', $invoice->ulid) }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 text-sm text-muted bg-white border border-subtle rounded-xl hover:bg-slate-50 transition"
               aria-label="تعديل الفاتورة">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                تعديل
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

    {{-- Flash handled globally by layouts/app.blade.php --}}

    {{-- ورقة الفاتورة --}}
    <div class="dash-card p-8 shadow-sm print:shadow-none print:border-0" id="invoice-paper">

        {{-- رأس الفاتورة --}}
        <div class="flex justify-between items-start mb-8">
            <div>
                <h2 class="text-3xl font-bold text-brand">فاتورة</h2>
                <p class="text-muted text-sm mt-1">{{ $invoice->number }}</p>
                @if($invoice->title)
                <p class="text-ink font-medium mt-1">{{ $invoice->title }}</p>
                @endif
            </div>
            {{-- IS2: text-left → text-end (يعطي text-align:left في RTL — صحيح للأرقام والتواريخ) --}}
            <div class="text-end text-sm text-muted space-y-1">
                <div class="flex gap-6">
                    <div>
                        <p class="text-xs text-muted">تاريخ الإصدار</p>
                        <p class="font-medium text-ink nums">{{ $invoice->issue_date->format('Y/m/d') }}</p>
                    </div>
                    @if($invoice->due_date)
                    <div>
                        <p class="text-xs text-muted">تاريخ الاستحقاق</p>
                        <p class="font-medium nums {{ $invoice->isOverdue() ? 'text-red-600' : 'text-ink' }}">
                            {{ $invoice->due_date->format('Y/m/d') }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- IS5: بيانات العميل — responsive grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 pb-6 border-b border-subtle">
            <div>
                <p class="dash-th mb-2">إلى</p>
                <p class="font-semibold text-ink">{{ $invoice->client->name }}</p>
                @if($invoice->client->company)
                <p class="text-sm text-muted">{{ $invoice->client->company }}</p>
                @endif
                @if($invoice->client->email)
                <p class="text-sm text-muted">{{ $invoice->client->email }}</p>
                @endif
                @if($invoice->client->phone)
                <p class="text-sm text-muted">{{ $invoice->client->phone }}</p>
                @endif
            </div>
            @if($invoice->project)
            <div>
                <p class="dash-th mb-2">المشروع</p>
                <p class="font-semibold text-ink">{{ $invoice->project->name }}</p>
            </div>
            @endif
        </div>

        {{-- جدول البنود --}}
        <table class="w-full text-sm mb-6">
            <thead>
                <tr class="border-b border-subtle">
                    <th class="text-start py-2 text-muted font-medium w-1/2">الوصف</th>
                    <th class="text-center py-2 text-muted font-medium w-1/6">الكمية</th>
                    {{-- IS2: text-left → text-end (صحيح للأرقام في RTL) --}}
                    <th class="text-end py-2 text-muted font-medium w-1/6">سعر الوحدة</th>
                    <th class="text-end py-2 text-muted font-medium w-1/6">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr class="border-b border-slate-50">
                    <td class="py-3 text-ink">{{ $item->description }}</td>
                    <td class="py-3 text-center text-muted nums">{{ number_format($item->quantity, 2) }}</td>
                    <td class="py-3 text-end text-muted nums">{{ number_format($item->unit_price, 2) }} {{ $invoice->currency }}</td>
                    <td class="py-3 text-end font-medium text-ink nums">{{ number_format($item->total, 2) }} {{ $invoice->currency }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- الإجماليات --}}
        <div class="flex justify-end mb-6">
            <div class="w-64 space-y-2 text-sm">
                @if($invoice->discount > 0 || $invoice->tax_amount > 0)
                <div class="flex justify-between text-muted">
                    <span>المجموع الفرعي</span>
                    <span class="nums">{{ number_format($invoice->subtotal, 2) }} {{ $invoice->currency }}</span>
                </div>
                @endif
                @if($invoice->tax_amount > 0)
                <div class="flex justify-between text-muted">
                    <span>الضريبة ({{ number_format($invoice->tax_rate, 0) }}%)</span>
                    <span class="nums">{{ number_format($invoice->tax_amount, 2) }} {{ $invoice->currency }}</span>
                </div>
                @endif
                @if($invoice->discount > 0)
                <div class="flex justify-between text-muted">
                    @if($invoice->discount_type === 'percentage')
                        <span>الخصم ({{ number_format($invoice->discount, 0) }}%)</span>
                        <span class="nums">- {{ number_format($invoice->discount_amount, 2) }} {{ $invoice->currency }}</span>
                    @else
                        <span>الخصم</span>
                        <span class="nums">- {{ number_format($invoice->discount, 2) }} {{ $invoice->currency }}</span>
                    @endif
                </div>
                @endif
                <div class="flex justify-between font-bold text-ink border-t border-subtle pt-2">
                    <span>الإجمالي</span>
                    <span class="nums">{{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</span>
                </div>
            </div>
        </div>

        @if($invoice->notes)
        <div class="border-t border-subtle pt-4">
            <p class="text-xs font-medium text-muted mb-1">ملاحظات</p>
            <p class="text-sm text-ink">{{ $invoice->notes }}</p>
        </div>
        @endif

    </div>
</div>

{{-- ══ Modal: اختيار الصندوق عند تسجيل الدفع ══ --}}
<div x-data="{ open: false }"
     @open-pay-modal.window="open = true"
     x-show="open"
     x-transition.opacity
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display: none;">

    {{-- Overlay --}}
    <div class="absolute inset-0 bg-black/40" @click="open = false"></div>

    {{-- Modal box --}}
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 z-10"
         @click.outside="open = false">

        <h3 class="text-lg font-bold text-ink mb-1">تسجيل الدفع</h3>
        <p class="text-sm text-muted mb-5">
            الفاتورة: <span class="font-semibold text-ink">{{ $invoice->number }}</span>
            {{-- IS3: teal-700 → brand --}}
            — المبلغ: <span class="font-bold text-brand nums">{{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</span>
        </p>

        <form method="POST" action="{{ route('invoices.mark-paid', $invoice->ulid) }}">
            @csrf

            <div class="mb-5">
                <label class="flex items-center gap-2 text-sm font-semibold text-ink mb-2">
                    <svg class="w-4 h-4 text-muted shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    إلى أي صندوق ستُودَع هذه المبالغ؟ <span class="text-red-500">*</span>
                </label>

                @if($wallets->isEmpty())
                    <div class="flex items-center gap-2 bg-amber-50 border border-amber-200 rounded-xl p-3 text-sm text-amber-800">
                        <svg class="w-4 h-4 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        لا يوجد صناديق — <a href="{{ route('wallets.create') }}" class="underline font-medium">أنشئ صندوقاً أولاً</a>
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach($wallets as $wallet)
                        <label class="flex items-center gap-3 p-3 rounded-xl border-2 border-subtle
                                      hover:border-brand/40 hover:bg-brand-50 cursor-pointer transition
                                      has-[:checked]:border-brand has-[:checked]:bg-brand-50">
                            <input type="radio" name="wallet_id" value="{{ $wallet->id }}"
                                   class="text-brand" required
                                   {{ $loop->first ? 'checked' : '' }}>
                            <span class="text-xl">{{ $wallet->icon ?: $wallet->type->icon() }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-ink">{{ $wallet->name }}</p>
                                <p class="text-xs text-muted nums">
                                    {{ $wallet->type->label() }} · {{ $wallet->currency }}
                                    · الرصيد: {{ number_format($wallet->balance(), 2) }}
                                </p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @error('wallet_id')
                        <p class="flex items-center gap-1.5 mt-2 text-xs text-red-600">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                @endif
            </div>

            <div class="flex gap-3">
                @if($wallets->isNotEmpty())
                {{-- IS3: bg-teal-600 → bg-brand --}}
                <button type="submit"
                        class="flex-1 inline-flex items-center justify-center gap-2 py-2.5 bg-brand text-white rounded-xl text-sm font-medium hover:bg-brand-600 transition">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    تأكيد الدفع
                </button>
                @endif
                <button type="button" @click="open = false"
                        class="flex-1 py-2.5 bg-slate-100 text-muted rounded-xl text-sm font-medium hover:bg-slate-200 transition">
                    إلغاء
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function sendWhatsappWithPdf(whatsappUrl, pdfUrl) {
    const a = document.createElement('a');
    a.href = pdfUrl;
    a.download = '';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    setTimeout(function() {
        window.open(whatsappUrl, '_blank');
    }, 1000);
}
</script>

@endsection

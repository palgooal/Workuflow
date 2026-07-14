@extends('layouts.app')

@section('title', 'تعديل الفاتورة ' . $invoice->number)

@section('breadcrumb')
    <span class="text-muted/60">/</span>
    <a href="{{ route('invoices.index') }}" class="text-muted hover:text-ink transition-colors">الفواتير</a>
    <span class="text-muted/60">/</span>
    <a href="{{ route('invoices.show', $invoice->ulid) }}" class="text-muted hover:text-ink transition-colors">{{ $invoice->number }}</a>
    <span class="text-muted/60">/</span>
    <span class="text-ink">تعديل</span>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.snow.min.css">
    <style>
        /* محاذاة شكل محرر Quill مع باقي حقول الفورم (.dash-field). القيم اللونية
           هنا منسوخة حرفياً من tailwind.config.js (subtle/accent) لأن هذا
           <style> عادي غير مُمرَّر عبر Tailwind، فلا يمكن استخدام theme() هنا.

           ملاحظة تصحيح مهمة: Quill لا يضع الـtoolbar داخل عنصر المحرر —
           يضعه كـsibling سابق له (كلاهما أبناء لنفس الحاوية الأب). لذلك
           لا يصح استهداف الشريط عبر ".rich-editor .ql-toolbar" (Descendant
           Selector) لأنه لن يطابق أبداً؛ وأيضاً محاولة تنسيق حاوية المحرر
           نفسها مباشرة (".rich-editor { border: ... }") تخسر أمام قواعد
           Quill الافتراضية الأعلى تخصصاً (".ql-container.ql-snow" بفئتين).
           الحل: نضع الاثنين (toolbar + محرر) داخل غلاف ".rich-editor-wrap"
           واحد فعلي، ونُلغي حدود/زوايا كل عنصر داخلي، ونعتمد على الغلاف
           وحده للحدود/تأثير التركيز — نفس أسلوب .dash-field.

           ملاحظة تصحيح ثانية: كنا نستخدم overflow:hidden على الغلاف
           لقصّ الزوايا الحادة للـtoolbar/container فتبدو مستديرة. لكن هذا
           يقصّ أيضاً أي عنصر يتجاوز حدود الغلاف — واكتشفنا أن نافذة
           معاينة الرابط المنبثقة في Quill (.ql-tooltip، تظهر عند النقر على
           رابط موجود) تُموضَع أحياناً (خصوصاً مع روابط قصيرة قرب بداية سطر
           RTL) جزئياً خارج حدود الغلاف يساراً، فيُخفي overflow:hidden زرَّي
           "تعديل"/"إزالة" تماماً ويمنع الوصول إليهما نهائياً. الحل: زوايا
           مستديرة مباشرة على toolbar (أعلى) وcontainer (أسفل) بدل قصّ
           الغلاف بالكامل — نفس المظهر البصري دون قصّ أي عنصر منبثق. */
        .rich-editor-wrap { border-radius: 0.75rem; border: 1px solid #E5E7EB; background: #fff; transition: border-color .15s, box-shadow .15s; }
        .rich-editor-wrap:focus-within { border-color: #13C597; box-shadow: 0 0 0 2px rgba(19,197,151,.3); }
        .rich-editor-wrap .ql-toolbar.ql-snow { border: none !important; background: #f8fafc; border-radius: 0.75rem 0.75rem 0 0; }
        .rich-editor-wrap .ql-container.ql-snow { border: none !important; font-family: inherit; font-size: 0.875rem; border-radius: 0 0 0.75rem 0.75rem; }
        .rich-editor-wrap .ql-editor { min-height: 90px; direction: rtl; text-align: right; }
        .rich-editor-wrap .ql-editor.ql-blank::before { color: rgba(107,114,128,.7); font-style: normal; }
        /* تعريب نص القائمة المنسدلة "العناوين" — Quill يعرضها بواسطة
           content: على ::before حسب data-value، بنفس أسلوب تعريب نافذة
           الرابط في quill-link-fix.blade.php. باقي القوائم (محاذاة/لون/
           تظليل) أيقونات أو تدرجات لونية بلا نص، فلا تحتاج تعريباً. */
        .rich-editor-wrap .ql-picker.ql-header .ql-picker-label::before,
        .rich-editor-wrap .ql-picker.ql-header .ql-picker-item::before { content: 'عادي'; }
        .rich-editor-wrap .ql-picker.ql-header .ql-picker-label[data-value="2"]::before,
        .rich-editor-wrap .ql-picker.ql-header .ql-picker-item[data-value="2"]::before { content: 'عنوان كبير'; }
        .rich-editor-wrap .ql-picker.ql-header .ql-picker-label[data-value="3"]::before,
        .rich-editor-wrap .ql-picker.ql-header .ql-picker-item[data-value="3"]::before { content: 'عنوان صغير'; }
        /* إصلاح: علامات القوائم (مرقّمة/نقطية) في Quill 1.3.7 تعتمد على CSS
           counters عبر ::before، ولا تظهر بصرياً أثناء الكتابة في هذه الصفحة
           (تأكيد بالفحص: البنية <ol><li> تُحفَظ بشكل صحيح، لكن العلامة نفسها
           لا تُرسَم — على الأرجح تعارض بين ترقيم Quill الداخلي واتجاه RTL).
           الحل: تعطيل ::before الخاصة بـQuill واستخدام list-style-type
           الأصلية في المتصفح بدلاً منها — أبسط وأكثر توافقاً مع RTL. */
        .rich-editor-wrap .ql-editor ol, .rich-editor-wrap .ql-editor ul { padding-right: 1.5em; padding-left: 0; }
        .rich-editor-wrap .ql-editor ol > li, .rich-editor-wrap .ql-editor ul > li { list-style-position: outside; }
        .rich-editor-wrap .ql-editor ol > li { list-style-type: decimal; }
        .rich-editor-wrap .ql-editor ul > li { list-style-type: disc; }
        .rich-editor-wrap .ql-editor li::before { content: none !important; }
    </style>
@endpush

@include('invoices.partials.quill-link-fix')

@section('content')
<div class="max-w-4xl mx-auto space-y-5" x-data="invoiceForm()">

    <x-page-header title="تعديل الفاتورة" :subtitle="$invoice->number" />

    <form method="POST" action="{{ route('invoices.update', $invoice->ulid) }}" class="space-y-5" @submit="syncEditors()">
        @csrf
        @method('PUT')

        {{-- البيانات الأساسية --}}
        <x-card-section title="بيانات الفاتورة">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- العميل --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-ink mb-1.5">
                        العميل <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-muted">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </span>
                        <select name="client_id" required
                                class="dash-field pr-9 py-2.5 @error('client_id') dash-field-error @enderror">
                            <option value="">اختر العميل…</option>
                            @foreach($clients as $client)
                            <option value="{{ $client->id }}"
                                {{ (old('client_id', $invoice->client_id) == $client->id) ? 'selected' : '' }}>
                                {{ $client->name }} @if($client->company)({{ $client->company }})@endif
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @error('client_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- المشروع --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-ink mb-1.5">
                        المشروع <span class="text-muted font-normal text-xs">(اختياري)</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-muted">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                        </span>
                        <select name="project_id" class="dash-field pr-9 py-2.5">
                            <option value="">بدون مشروع</option>
                            @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ old('project_id', $invoice->project_id) == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- العنوان --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-ink mb-1.5">
                        عنوان الفاتورة <span class="text-muted font-normal text-xs">(اختياري)</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-muted">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </span>
                        <input type="text" name="title" value="{{ old('title', $invoice->title) }}"
                               placeholder="مثال: خدمات تصميم — مايو 2026"
                               class="dash-field pr-9 py-2.5">
                    </div>
                </div>

                {{-- تاريخ الإصدار --}}
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1.5">
                        تاريخ الإصدار <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-muted">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </span>
                        <input type="date" name="issue_date"
                               value="{{ old('issue_date', $invoice->issue_date->format('Y-m-d')) }}" required
                               class="dash-field pr-9 py-2.5 nums @error('issue_date') dash-field-error @enderror">
                    </div>
                    @error('issue_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- تاريخ الاستحقاق --}}
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1.5">تاريخ الاستحقاق</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-muted">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <input type="date" name="due_date"
                               value="{{ old('due_date', $invoice->due_date?->format('Y-m-d')) }}"
                               class="dash-field pr-9 py-2.5 nums">
                    </div>
                </div>

                {{-- العملة --}}
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1.5">العملة</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-muted">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <select name="currency" x-model="currency" @change="recalc()" class="dash-field pr-9 py-2.5">
                            @foreach($currencies as $code => $label)
                                <option value="{{ $code }}"
                                    {{ old('currency', $invoice->currency) === $code ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- الضريبة --}}
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1.5">نسبة الضريبة %</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-muted">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <input type="number" name="tax_rate"
                               value="{{ old('tax_rate', $invoice->tax_rate) }}"
                               min="0" max="100" step="0.01" x-model.number="taxRate"
                               class="dash-field pr-9 py-2.5 nums">
                    </div>
                </div>
            </div>
        </x-card-section>

        {{-- البنود --}}
        <x-card-section title="البنود">
            <x-slot name="action">
                <button type="button" @click="addItem()"
                        class="inline-flex items-center gap-1.5 text-xs text-brand hover:text-brand-700 font-semibold transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    إضافة بند
                </button>
            </x-slot>

            <div class="space-y-3">
                {{-- رأس الجدول --}}
                <div class="hidden md:grid grid-cols-12 gap-2 dash-th px-1">
                    <div class="col-span-6">الوصف</div>
                    <div class="col-span-2 text-center">الكمية</div>
                    <div class="col-span-3 text-center">السعر</div>
                    <div class="col-span-1"></div>
                </div>

                <template x-for="(item, index) in items" :key="index">
                    <div class="grid grid-cols-12 gap-2 items-center">
                        <div class="col-span-12 md:col-span-6">
                            <input type="text" :name="`items[${index}][description]`"
                                   x-model="item.description" placeholder="وصف الخدمة أو المنتج" required
                                   class="dash-field px-3 py-2">
                        </div>
                        <div class="col-span-4 md:col-span-2">
                            <input type="number" :name="`items[${index}][quantity]`"
                                   x-model.number="item.quantity" @input="recalc()"
                                   placeholder="1" min="0.01" :step="priceStep" required
                                   class="dash-field px-3 py-2 text-center nums">
                        </div>
                        <div class="col-span-7 md:col-span-3">
                            <input type="number" :name="`items[${index}][unit_price]`"
                                   x-model.number="item.unit_price" @input="recalc()"
                                   :placeholder="unitPricePlaceholder" min="0" :step="priceStep" required
                                   class="dash-field px-3 py-2 text-left nums">
                        </div>
                        <div class="col-span-1 flex justify-center">
                            <button type="button" @click="removeItem(index)"
                                    x-show="items.length > 1"
                                    class="p-1 text-red-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>

                @error('items') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </x-card-section>

        {{-- الإجماليات + الخصم --}}
        <x-card-section>
            <div class="flex flex-col md:flex-row gap-6 items-start">
                {{-- الخصم --}}
                <div class="w-full md:w-56">
                    <label class="block text-sm font-semibold text-ink mb-1.5">الخصم</label>
                    {{-- Toggle نوع الخصم --}}
                    <div class="flex rounded-lg border border-slate-200 overflow-hidden mb-2 text-xs font-semibold">
                        <button type="button"
                                @click="discountType='fixed'; recalc()"
                                :class="discountType==='fixed'
                                    ? 'bg-brand text-white'
                                    : 'bg-white text-slate-500 hover:bg-slate-50'"
                                class="flex-1 py-1.5 transition-colors">
                            قيمة ثابتة
                        </button>
                        <button type="button"
                                @click="discountType='percentage'; recalc()"
                                :class="discountType==='percentage'
                                    ? 'bg-brand text-white'
                                    : 'bg-white text-slate-500 hover:bg-slate-50'"
                                class="flex-1 py-1.5 transition-colors">
                            نسبة %
                        </button>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-muted text-xs font-bold"
                              x-text="discountType==='percentage' ? '%' : currencySymbol"></span>
                        <input type="number" name="discount"
                               value="{{ old('discount', $invoice->discount) }}"
                               min="0" :step="discountType==='percentage' ? '0.01' : priceStep"
                               :max="discountType==='percentage' ? 100 : undefined"
                               x-model.number="discountValue" @input="recalc()"
                               class="dash-field pr-8 py-2.5 nums">
                        <input type="hidden" name="discount_type" :value="discountType">
                    </div>
                </div>

                {{-- الأرقام --}}
                <div class="flex-1 space-y-2 text-sm bg-slate-50 rounded-xl p-4">
                    <div class="flex justify-between text-muted">
                        <span>المجموع الفرعي</span>
                        <span class="nums font-medium text-ink" x-text="formatMoney(subtotal)"></span>
                    </div>
                    <div class="flex justify-between text-muted" x-show="taxRate > 0">
                        <span>الضريبة (<span x-text="taxRate"></span>%)</span>
                        <span class="nums font-medium text-ink" x-text="formatMoney(taxAmount)"></span>
                    </div>
                    <div class="flex justify-between text-muted" x-show="discountValue > 0">
                        <span x-text="discountType==='percentage' ? 'الخصم (' + discountValue + '%)' : 'الخصم'"></span>
                        <span class="nums font-medium text-red-600" x-text="'-' + formatMoney(discountAmount)"></span>
                    </div>
                    <div class="flex justify-between font-bold text-ink text-base pt-2 border-t border-subtle">
                        <span>الإجمالي</span>
                        <span class="nums" x-text="formatMoney(total)"></span>
                    </div>
                </div>
            </div>
        </x-card-section>

        {{-- الملاحظات والشروط --}}
        <x-card-section title="ملاحظات وشروط">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1.5">ملاحظات للعميل</label>
                    <div class="rich-editor-wrap">
                        <div x-ref="notesEditor"></div>
                    </div>
                    <input type="hidden" name="notes" value="{{ old('notes', $invoice->notes) }}">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1.5">الشروط والأحكام</label>
                    <div class="rich-editor-wrap">
                        <div x-ref="termsEditor"></div>
                    </div>
                    <input type="hidden" name="terms" value="{{ old('terms', $invoice->terms) }}">
                </div>
            </div>
        </x-card-section>

        {{-- الأزرار --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                    class="flex-1 inline-flex items-center justify-center gap-2 py-2.5 bg-brand hover:bg-brand-600 text-white text-sm font-semibold rounded-btn transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                حفظ التعديلات
            </button>
            <a href="{{ route('invoices.show', $invoice->ulid) }}"
               class="flex-1 inline-flex items-center justify-center gap-2 py-2.5 bg-slate-100 text-slate-700 rounded-btn text-sm font-medium hover:bg-slate-200 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                إلغاء
            </a>
        </div>

    </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.min.js"></script>
<script>
@php
    $invoiceItems = $invoice->items->map(fn($i) => [
        'description' => $i->description,
        'quantity'    => (float) $i->quantity,
        'unit_price'  => (float) $i->unit_price,
    ])->values()->all();
@endphp
function invoiceForm() {
    return {
        // خريطة code => عدد الخانات العشرية (3 لعملات الفلس JOD/KWD/BHD/OMR، وإلا 2)
        currencyDecimals: @json(\App\Support\Helpers\Currency::decimalsMap()),
        // خريطة code => رمز العملة — لعرض رمز الخصم الصحيح بدل "₪" الثابتة سابقاً
        currencySymbols: @json(\App\Support\Helpers\Currency::symbolsMap()),
        currency: '{{ old('currency', $invoice->currency) }}',
        items: @json($invoiceItems),
        taxRate:       {{ old('tax_rate', $invoice->tax_rate) }},
        discountValue: {{ old('discount', $invoice->discount) }},
        discountType:  '{{ old('discount_type', $invoice->discount_type ?? 'fixed') }}',
        subtotal:      {{ $invoice->subtotal }},
        taxAmount:     {{ $invoice->tax_amount }},
        discountAmount: 0,
        total:         {{ $invoice->total }},

        get decimals() {
            return this.currencyDecimals[this.currency] ?? 2;
        },
        get priceStep() {
            return (1 / Math.pow(10, this.decimals)).toFixed(this.decimals);
        },
        get unitPricePlaceholder() {
            return (0).toFixed(this.decimals);
        },
        get currencySymbol() {
            return this.currencySymbols[this.currency] ?? this.currency;
        },

        // ── محرر النصوص المنسّق (ملاحظات/شروط) ──────────────────────────
        // مكتبة Quill عبر CDN — راجع docs/INVOICES.md. الناتج HTML يُعقَّم
        // على الخادم عبر PageContentSanitizer::clean(..., 'invoice_notes')
        // في InvoiceController قبل الحفظ.
        notesQuill: null,
        termsQuill: null,

        initEditor(refName, hiddenInputSelector, placeholder) {
            const container = this.$refs[refName];
            const hiddenInput = this.$el.querySelector(hiddenInputSelector);
            const quill = new Quill(container, {
                theme: 'snow',
                placeholder: placeholder,
                // bounds: يمنع Quill من موضعة نافذة معاينة/تحرير الرابط
                // خارج حدود غلاف المحرر. افتراضياً Quill يقيّدها بحدود
                // document.body فقط، فمع رابط قصير قرب بداية سطر RTL كانت
                // النافذة تُموضَع جزئياً خارج البطاقة (Card) المحيطة — التي
                // تملك overflow-hidden الخاص بها (تنسيق مشترك في x-card-section
                // غير مرتبط بهذه الميزة، فلا نعدّله) — فتُقصّ نافذة الرابط
                // ويصبح زرّا "تعديل"/"إزالة" غير قابلين للنقر. تقييد الحدود
                // بغلاف المحرر نفسه يبقيها ظاهرة وقابلة للنقر دائماً.
                bounds: container.parentElement,
                modules: {
                    toolbar: [
                        [{ header: [2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ align: [] }],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['blockquote'],
                        [{ color: [] }, { background: [] }],
                        ['link'],
                        ['clean'],
                    ],
                },
            });

            // اتجاه الكتابة RTL افتراضياً — Quill لا يرث dir من الصفحة
            // تلقائياً حتى لو كان <html> بأكمله dir="rtl"، فيبقى المؤشر
            // والفقرات الجديدة LTR افتراضياً ما لم يُضبَط صراحة هنا.
            quill.root.setAttribute('dir', 'rtl');

            // إصلاح أداة الرابط (تعقيم/تطبيع href + تعريب الواجهة) —
            // راجع resources/views/invoices/partials/quill-link-fix.blade.php
            window.setupInvoiceLinkTooltip(quill);

            // إصلاح وصولية: أزرار شريط Quill افتراضياً بلا aria-label ولا
            // title — أيقونات SVG فقط، فلا تُقرأ أسماؤها لمستخدمي قارئ
            // الشاشة، ولا تظهر تلميحات عند التحويم بالماوس. نضيفها يدوياً هنا.
            const toolbarLabels = { 'ql-bold': 'غامق', 'ql-italic': 'مائل', 'ql-underline': 'تسطير', 'ql-strike': 'يتوسطه خط', 'ql-blockquote': 'اقتباس', 'ql-link': 'إدراج رابط', 'ql-clean': 'إزالة التنسيق' };
            container.parentElement.querySelectorAll('.ql-toolbar button').forEach((btn) => {
                let label = null;
                if (btn.classList.contains('ql-list')) {
                    label = btn.getAttribute('value') === 'ordered' ? 'قائمة مرقّمة' : 'قائمة نقطية';
                } else {
                    for (const cls in toolbarLabels) {
                        if (btn.classList.contains(cls)) { label = toolbarLabels[cls]; break; }
                    }
                }
                if (label) {
                    btn.setAttribute('aria-label', label);
                    btn.setAttribute('title', label);
                }
            });
            // تسميات القوائم المنسدلة الجديدة (عناوين/محاذاة/لون/تظليل) —
            // هذه ليست <button> عادية بل .ql-picker يحوّلها Quill من <select>،
            // فتحتاج نفس معالجة aria-label/title على عنصر التسمية الظاهر.
            const pickerLabels = { 'ql-header': 'نوع العنوان', 'ql-align': 'محاذاة النص', 'ql-color': 'لون النص', 'ql-background': 'لون التظليل' };
            container.parentElement.querySelectorAll('.ql-toolbar .ql-picker').forEach((picker) => {
                for (const cls in pickerLabels) {
                    if (picker.classList.contains(cls)) {
                        const pickerLabel = picker.querySelector('.ql-picker-label');
                        if (pickerLabel) {
                            pickerLabel.setAttribute('aria-label', pickerLabels[cls]);
                            pickerLabel.setAttribute('title', pickerLabels[cls]);
                        }
                        break;
                    }
                }
            });

            // تعبئة القيمة القديمة — إما HTML مُعقَّم مسبقاً من فاتورة عُدِّلت
            // من قبل عبر هذا المحرر، أو نص قديم عادي (بدون أي وسم HTML) من
            // فاتورة أُنشئت قبل هذه الميزة، نحوّل فيه فواصل الأسطر لـ<br>
            // يدوياً حتى لا تختفي بصرياً عند تحويلها لـHTML خام.
            let html = hiddenInput.value;
            if (html && !html.includes('<')) {
                html = html.split('\n').join('<br>');
            }
            if (html) {
                quill.clipboard.dangerouslyPasteHTML(html);
            }
            return quill;
        },
        syncEditors() {
            if (this.notesQuill) this.$el.querySelector('input[name="notes"]').value = this.notesQuill.root.innerHTML;
            if (this.termsQuill) this.$el.querySelector('input[name="terms"]').value = this.termsQuill.root.innerHTML;
        },

        init() {
            this.recalc();
            this.notesQuill = this.initEditor('notesEditor', 'input[name="notes"]', 'شكراً لتعاملكم معنا…');
            this.termsQuill = this.initEditor('termsEditor', 'input[name="terms"]', 'الدفع خلال 14 يوم من تاريخ الفاتورة…');
        },

        addItem() {
            this.items.push({ description: '', quantity: 1, unit_price: 0 });
        },
        removeItem(index) {
            this.items.splice(index, 1);
            this.recalc();
        },
        recalc() {
            this.subtotal = this.items.reduce((s, i) => s + (i.quantity * i.unit_price), 0);
            this.taxAmount = this.subtotal * (this.taxRate / 100);
            this.discountAmount = this.discountType === 'percentage'
                ? this.subtotal * (this.discountValue / 100)
                : this.discountValue;
            this.discountAmount = Math.max(0, this.discountAmount);
            this.total = Math.max(0, this.subtotal + this.taxAmount - this.discountAmount);
        },
        formatMoney(val) {
            return new Intl.NumberFormat('en-US', { minimumFractionDigits: this.decimals, maximumFractionDigits: this.decimals }).format(val || 0);
        }
    }
}
</script>
@endsection

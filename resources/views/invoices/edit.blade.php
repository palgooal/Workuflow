@extends('layouts.app')

@section('title', 'تعديل الفاتورة ' . $invoice->number)

@section('content')
<div class="max-w-4xl mx-auto space-y-5" x-data="invoiceForm()">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('invoices.show', $invoice->ulid) }}"
           class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">تعديل الفاتورة</h1>
            <p class="text-sm text-gray-500">{{ $invoice->number }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('invoices.update', $invoice->ulid) }}" class="space-y-5">
        @csrf
        @method('PUT')

        {{-- البيانات الأساسية --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5 space-y-4">
            <h2 class="text-sm font-semibold text-gray-700">بيانات الفاتورة</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- العميل --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">العميل <span class="text-red-500">*</span></label>
                    <select name="client_id" required
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none bg-white {{ $errors->has('client_id') ? 'border-red-400' : '' }}">
                        <option value="">اختر العميل…</option>
                        @foreach($clients as $client)
                        <option value="{{ $client->id }}"
                            {{ (old('client_id', $invoice->client_id) == $client->id) ? 'selected' : '' }}>
                            {{ $client->name }} @if($client->company)({{ $client->company }})@endif
                        </option>
                        @endforeach
                    </select>
                    @error('client_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- المشروع --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">المشروع <span class="text-gray-400 text-xs">(اختياري)</span></label>
                    <select name="project_id"
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                        <option value="">بدون مشروع</option>
                        @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ old('project_id', $invoice->project_id) == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- العنوان --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">عنوان الفاتورة <span class="text-gray-400 text-xs">(اختياري)</span></label>
                    <input type="text" name="title" value="{{ old('title', $invoice->title) }}"
                           placeholder="مثال: خدمات تصميم — مايو 2026"
                           class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>

                {{-- تاريخ الإصدار --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الإصدار <span class="text-red-500">*</span></label>
                    <input type="date" name="issue_date"
                           value="{{ old('issue_date', $invoice->issue_date->format('Y-m-d')) }}" required
                           class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none {{ $errors->has('issue_date') ? 'border-red-400' : '' }}">
                    @error('issue_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- تاريخ الاستحقاق --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الاستحقاق</label>
                    <input type="date" name="due_date"
                           value="{{ old('due_date', $invoice->due_date?->format('Y-m-d')) }}"
                           class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>

                {{-- العملة --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">العملة</label>
                    <select name="currency"
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                        <option value="ILS" {{ old('currency', $invoice->currency) === 'ILS' ? 'selected' : '' }}>₪ شيكل (ILS)</option>
                        <option value="USD" {{ old('currency', $invoice->currency) === 'USD' ? 'selected' : '' }}>$ دولار (USD)</option>
                        <option value="EUR" {{ old('currency', $invoice->currency) === 'EUR' ? 'selected' : '' }}>€ يورو (EUR)</option>
                        <option value="JOD" {{ old('currency', $invoice->currency) === 'JOD' ? 'selected' : '' }}>د.أ دينار (JOD)</option>
                    </select>
                </div>

                {{-- الضريبة --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">نسبة الضريبة %</label>
                    <input type="number" name="tax_rate"
                           value="{{ old('tax_rate', $invoice->tax_rate) }}"
                           min="0" max="100" step="0.01" x-model.number="taxRate"
                           class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
            </div>
        </div>

        {{-- البنود --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5 space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700">البنود</h2>
                <button type="button" @click="addItem()"
                        class="inline-flex items-center gap-1.5 text-xs text-indigo-600 hover:text-indigo-800 font-medium transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    إضافة بند
                </button>
            </div>

            {{-- رأس الجدول --}}
            <div class="hidden md:grid grid-cols-12 gap-2 text-xs font-medium text-gray-400 px-1">
                <div class="col-span-6">الوصف</div>
                <div class="col-span-2 text-center">الكمية</div>
                <div class="col-span-3 text-center">السعر</div>
                <div class="col-span-1"></div>
            </div>

            {{-- البنود --}}
            <template x-for="(item, index) in items" :key="index">
                <div class="grid grid-cols-12 gap-2 items-center">
                    <div class="col-span-12 md:col-span-6">
                        <input type="text" :name="`items[${index}][description]`"
                               x-model="item.description" placeholder="وصف الخدمة أو المنتج" required
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div class="col-span-4 md:col-span-2">
                        <input type="number" :name="`items[${index}][quantity]`"
                               x-model.number="item.quantity" @input="recalc()"
                               placeholder="1" min="0.01" step="0.01" required
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-center">
                    </div>
                    <div class="col-span-7 md:col-span-3">
                        <input type="number" :name="`items[${index}][unit_price]`"
                               x-model.number="item.unit_price" @input="recalc()"
                               placeholder="0.00" min="0" step="0.01" required
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-left">
                    </div>
                    <div class="col-span-1 flex justify-center">
                        <button type="button" @click="removeItem(index)"
                                x-show="items.length > 1"
                                class="text-red-400 hover:text-red-600 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </template>

            @error('items') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- الإجماليات --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <div class="max-w-xs mr-auto space-y-2 text-sm">
                <div class="flex justify-between text-gray-600">
                    <span>المجموع الفرعي</span>
                    <span x-text="formatMoney(subtotal)"></span>
                </div>
                <div class="flex justify-between text-gray-600" x-show="taxRate > 0">
                    <span>الضريبة (<span x-text="taxRate"></span>%)</span>
                    <span x-text="formatMoney(taxAmount)"></span>
                </div>
                <div class="flex justify-between text-gray-600" x-show="discount > 0">
                    <span>الخصم</span>
                    <span x-text="'-' + formatMoney(discount)"></span>
                </div>
                <div class="flex justify-between font-bold text-gray-900 text-base pt-2 border-t border-gray-100">
                    <span>الإجمالي</span>
                    <span x-text="formatMoney(total)"></span>
                </div>
            </div>

            {{-- خصم --}}
            <div class="mt-4 max-w-xs mr-auto">
                <label class="block text-sm font-medium text-gray-700 mb-1">خصم (بالقيمة)</label>
                <input type="number" name="discount"
                       value="{{ old('discount', $invoice->discount) }}"
                       min="0" step="0.01" x-model.number="discount" @input="recalc()"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
        </div>

        {{-- الملاحظات والشروط --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5 space-y-4">
            <h2 class="text-sm font-semibold text-gray-700">ملاحظات وشروط</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات للعميل</label>
                <textarea name="notes" rows="2" placeholder="شكراً لتعاملكم معنا…"
                          class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none resize-none">{{ old('notes', $invoice->notes) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الشروط والأحكام</label>
                <textarea name="terms" rows="2" placeholder="الدفع خلال 14 يوم من تاريخ الفاتورة…"
                          class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none resize-none">{{ old('terms', $invoice->terms) }}</textarea>
            </div>
        </div>

        {{-- الأزرار --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('invoices.show', $invoice->ulid) }}"
               class="px-4 py-2.5 text-sm text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                إلغاء
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition">
                حفظ التعديلات
            </button>
        </div>

    </form>
</div>

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
        items: @json($invoiceItems),
        taxRate:   {{ old('tax_rate',  $invoice->tax_rate)  }},
        discount:  {{ old('discount',  $invoice->discount)  }},
        subtotal:  {{ $invoice->subtotal }},
        taxAmount: {{ $invoice->tax_amount }},
        total:     {{ $invoice->total }},

        init() { this.recalc(); },

        addItem() {
            this.items.push({ description: '', quantity: 1, unit_price: 0 });
        },
        removeItem(index) {
            this.items.splice(index, 1);
            this.recalc();
        },
        recalc() {
            this.subtotal  = this.items.reduce((s, i) => s + (i.quantity * i.unit_price), 0);
            this.taxAmount = this.subtotal * (this.taxRate / 100);
            this.total     = Math.max(0, this.subtotal + this.taxAmount - this.discount);
        },
        formatMoney(val) {
            return new Intl.NumberFormat('ar-PS', { minimumFractionDigits: 2 }).format(val || 0);
        }
    }
}
</script>
@endsection

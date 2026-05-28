@extends('layouts.app')

@section('title', 'إنشاء عرض سعر')

@section('content')
<div class="max-w-4xl mx-auto space-y-5" x-data="quoteForm()">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ url()->previous() }}"
           class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">إنشاء عرض سعر</h1>
            <p class="text-sm text-gray-500">أدخل بيانات العرض والخدمات المقدَّمة</p>
        </div>
    </div>

    <form method="POST" action="{{ route('quotes.store') }}" class="space-y-5">
        @csrf

        {{-- البيانات الأساسية --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5 space-y-4">
            <h2 class="text-sm font-semibold text-gray-700">بيانات العرض</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- العميل --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        العميل <span class="text-red-500">*</span>
                    </label>
                    <select name="client_id" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500
                                   @error('client_id') border-red-300 @enderror">
                        <option value="">— اختر عميلاً —</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}"
                                {{ old('client_id', $preClientId) == $client->id ? 'selected' : '' }}>
                                {{ $client->display_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('client_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- المشروع --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">المشروع</label>
                    <select name="project_id"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">— بدون مشروع —</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}"
                                {{ old('project_id', $preProjectId) == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- العنوان --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">عنوان العرض</label>
                    <input type="text" name="title" value="{{ old('title') }}"
                           placeholder="مثال: عرض سعر تصميم وتطوير موقع شركة..."
                           class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                {{-- تاريخ الإصدار --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        تاريخ الإصدار <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="issue_date" required
                           value="{{ old('issue_date', date('Y-m-d')) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500
                                  @error('issue_date') border-red-300 @enderror">
                    @error('issue_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- صالح حتى --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">صالح حتى</label>
                    <input type="date" name="valid_until"
                           value="{{ old('valid_until', date('Y-m-d', strtotime('+30 days'))) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500
                                  @error('valid_until') border-red-300 @enderror">
                    @error('valid_until') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- العملة --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        العملة <span class="text-red-500">*</span>
                    </label>
                    <select name="currency" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($currencies as $cur)
                            <option value="{{ $cur }}" {{ old('currency', 'ILS') === $cur ? 'selected' : '' }}>
                                {{ $cur }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>

        {{-- البنود --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5 space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700">بنود العرض</h2>
                {{-- إضافة من كتالوج الخدمات --}}
                @if($services->isNotEmpty())
                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click="open = !open"
                            class="text-xs text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 4v16m8-8H4"/>
                        </svg>
                        إضافة من الخدمات
                    </button>
                    <div x-show="open" @click.outside="open = false" x-cloak
                         class="absolute left-0 top-7 z-20 w-64 bg-white rounded-xl border border-gray-200
                                shadow-lg divide-y divide-gray-50 max-h-60 overflow-y-auto">
                        @foreach($services as $svc)
                        <button type="button"
                                @click="addServiceItem('{{ addslashes($svc->name_ar) }}'); open = false"
                                class="w-full text-right px-3.5 py-2.5 text-xs hover:bg-indigo-50 transition">
                            {{ $svc->name_ar }}
                        </button>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <template x-for="(item, index) in items" :key="index">
                <div class="flex gap-2 items-start">
                    <div class="flex-1">
                        <input type="text" :name="`items[${index}][description]`"
                               x-model="item.description" placeholder="وصف الخدمة أو البند"
                               class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    </div>
                    <div class="w-24">
                        <input type="number" :name="`items[${index}][quantity]`"
                               x-model.number="item.quantity" @input="recalc()"
                               placeholder="الكمية" min="0.01" step="0.01"
                               class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    </div>
                    <div class="w-28">
                        <input type="number" :name="`items[${index}][unit_price]`"
                               x-model.number="item.unit_price" @input="recalc()"
                               placeholder="سعر الوحدة" min="0" step="0.01"
                               class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    </div>
                    <div class="w-28 py-2 px-3 text-sm text-gray-500 bg-gray-50 rounded-lg text-center">
                        <span x-text="(item.quantity * item.unit_price).toLocaleString('en', {minimumFractionDigits:2, maximumFractionDigits:2})"></span>
                    </div>
                    <button type="button" @click="removeItem(index)"
                            class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </template>

            {{-- رأس الجدول --}}
            <div class="flex gap-2 text-xs text-gray-400 px-0.5">
                <div class="flex-1">الوصف</div>
                <div class="w-24 text-center">الكمية</div>
                <div class="w-28 text-center">سعر الوحدة</div>
                <div class="w-28 text-center">الإجمالي</div>
                <div class="w-8"></div>
            </div>

            <button type="button" @click="addItem()"
                    class="w-full py-2.5 text-sm text-indigo-600 border-2 border-dashed border-indigo-200
                           rounded-xl hover:border-indigo-400 hover:bg-indigo-50 transition">
                + إضافة بند
            </button>
        </div>

        {{-- الإجماليات والملاحظات --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            {{-- ملاحظات وشروط --}}
            <div class="bg-white rounded-xl border border-gray-100 p-5 space-y-4">
                <h2 class="text-sm font-semibold text-gray-700">ملاحظات وشروط</h2>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">ملاحظات للعميل</label>
                    <textarea name="notes" rows="3" placeholder="أي ملاحظات تودّ إيصالها للعميل..."
                              class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200
                                     focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">الشروط والأحكام</label>
                    <textarea name="terms" rows="3" placeholder="شروط الدفع، ضمانات، استثناءات..."
                              class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200
                                     focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('terms') }}</textarea>
                </div>
            </div>

            {{-- الإجماليات --}}
            <div class="bg-white rounded-xl border border-gray-100 p-5 space-y-3">
                <h2 class="text-sm font-semibold text-gray-700">الإجماليات</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>المجموع الفرعي</span>
                        <span x-text="subtotal.toLocaleString('en', {minimumFractionDigits:2})"></span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <label class="text-gray-600 whitespace-nowrap">ضريبة %</label>
                        <input type="number" name="tax_rate" x-model.number="taxRate" @input="recalc()"
                               min="0" max="100" step="0.1" value="{{ old('tax_rate', 0) }}"
                               class="w-20 px-2 py-1.5 text-sm rounded-lg border border-gray-200
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 text-center">
                        <span x-text="taxAmount.toLocaleString('en', {minimumFractionDigits:2})"
                              class="text-gray-500 min-w-16 text-left"></span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <label class="text-gray-600 whitespace-nowrap">خصم</label>
                        <input type="number" name="discount" x-model.number="discount" @input="recalc()"
                               min="0" step="0.01" value="{{ old('discount', 0) }}"
                               class="w-20 px-2 py-1.5 text-sm rounded-lg border border-gray-200
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 text-center">
                        <span x-text="'- ' + discount.toLocaleString('en', {minimumFractionDigits:2})"
                              class="text-red-500 min-w-16 text-left"></span>
                    </div>
                    <div class="border-t border-gray-100 pt-2 flex justify-between font-bold text-gray-900">
                        <span>الإجمالي</span>
                        <span x-text="total.toLocaleString('en', {minimumFractionDigits:2})"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- أزرار الإرسال --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('quotes.index') }}"
               class="px-5 py-2.5 text-sm text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-xl transition">
                إلغاء
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium
                           rounded-xl hover:bg-indigo-700 transition">
                حفظ العرض
            </button>
        </div>
    </form>
</div>

@push('scripts')
@php
    $defaultItems = old('items') ?: [['description' => '', 'quantity' => 1, 'unit_price' => 0]];
@endphp
<script>
function quoteForm() {
    return {
        items:    @json($defaultItems),
        taxRate:  {{ old('tax_rate', 0) }},
        discount: {{ old('discount', 0) }},
        subtotal: 0,
        taxAmount: 0,
        total: 0,

        init() { this.recalc(); },

        addItem() {
            this.items.push({ description: '', quantity: 1, unit_price: 0 });
        },
        addServiceItem(name) {
            this.items.push({ description: name, quantity: 1, unit_price: 0 });
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
                this.recalc();
            }
        },
        recalc() {
            this.subtotal  = this.items.reduce((s, i) => s + (i.quantity * i.unit_price), 0);
            this.taxAmount = Math.round(this.subtotal * (this.taxRate / 100) * 100) / 100;
            this.total     = Math.max(0, this.subtotal + this.taxAmount - this.discount);
        },
    };
}
</script>
@endpush
@endsection

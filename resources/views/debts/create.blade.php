@extends('layouts.app')

@section('title', 'إضافة دين جديد')

@section('breadcrumb')
    <span class="text-muted/60">/</span>
    <a href="{{ route('debts.index') }}" class="text-muted hover:text-ink transition-colors">الديون</a>
    <span class="text-muted/60">/</span>
    <span class="text-ink">إضافة دين</span>
@endsection

@section('content')
<div class="max-w-xl mx-auto space-y-5">

    <x-page-header title="إضافة دين جديد" subtitle="سجّل ديناً عليك أو ديناً لك" />

    <div class="dash-card p-6 sm:p-7"
         x-data="{
             type: '{{ old('type', 'borrowed') }}',
             get typeLabel() {
                 return this.type === 'borrowed' ? 'دين عليّ (اقترضت)' : 'دين لي (أقرضت)';
             }
         }">

        <form method="POST" action="{{ route('debts.store') }}" class="space-y-6">
            @csrf

            {{-- Type Toggle --}}
            <div>
                <label class="block text-sm font-semibold text-ink mb-2">نوع الدين</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="borrowed" x-model="type" class="sr-only">
                        <div :class="type === 'borrowed'
                                ? 'border-error bg-error-soft text-red-700 ring-2 ring-error/30'
                                : 'border-subtle bg-surface text-slate-600 hover:border-slate-300'"
                             class="border-2 rounded-xl p-4 text-center transition-colors">
                            <div class="text-2xl mb-1">💸</div>
                            <p class="text-sm font-semibold">دين عليّ</p>
                            <p class="text-xs mt-0.5 opacity-70">اقترضت من شخص</p>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="lent" x-model="type" class="sr-only">
                        <div :class="type === 'lent'
                                ? 'border-success bg-success-soft text-success-700 ring-2 ring-success/30'
                                : 'border-subtle bg-surface text-slate-600 hover:border-slate-300'"
                             class="border-2 rounded-xl p-4 text-center transition-colors">
                            <div class="text-2xl mb-1">🤝</div>
                            <p class="text-sm font-semibold">دين لي</p>
                            <p class="text-xs mt-0.5 opacity-70">أقرضت شخصاً</p>
                        </div>
                    </label>
                </div>
                @error('type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Party Name --}}
            <div>
                <label for="party_name" class="block text-sm font-semibold text-ink mb-1.5">
                    <span x-text="type === 'borrowed' ? 'اسم المُقرِض (من أخذت منه)' : 'اسم المُقترِض (من أعطيته)'"></span>
                </label>
                <input type="text" id="party_name" name="party_name"
                       value="{{ old('party_name') }}" required
                       :placeholder="type === 'borrowed' ? 'مثال: أحمد محمد' : 'مثال: شركة XYZ'"
                       class="dash-field px-4 py-2.5 @error('party_name') dash-field-error @enderror">
                @error('party_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Amount + Currency --}}
            <div class="grid grid-cols-3 gap-3">
                <div class="col-span-2">
                    <label for="amount" class="block text-sm font-semibold text-ink mb-1.5">المبلغ</label>
                    <input type="number" id="amount" name="amount"
                           value="{{ old('amount') }}" required min="0.01" step="0.01"
                           placeholder="0.00"
                           class="dash-field px-4 py-2.5 nums @error('amount') dash-field-error @enderror">
                    @error('amount') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="currency" class="block text-sm font-semibold text-ink mb-1.5">العملة</label>
                    <select id="currency" name="currency" class="dash-field px-3 py-2.5">
                        @foreach($currencies as $cur)
                            <option value="{{ $cur }}" {{ old('currency', 'SAR') === $cur ? 'selected' : '' }}>
                                {{ $cur }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Due Date --}}
            <div>
                <label for="due_date" class="block text-sm font-semibold text-ink mb-1.5">
                    تاريخ الاستحقاق <span class="text-muted font-normal">(اختياري)</span>
                </label>
                <input type="date" id="due_date" name="due_date"
                       value="{{ old('due_date') }}" min="{{ date('Y-m-d') }}"
                       class="dash-field px-4 py-2.5 nums @error('due_date') dash-field-error @enderror">
                @error('due_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Notes --}}
            <div>
                <label for="notes" class="block text-sm font-semibold text-ink mb-1.5">
                    ملاحظات <span class="text-muted font-normal">(اختياري)</span>
                </label>
                <textarea id="notes" name="notes" rows="3"
                          placeholder="سبب الدين، تفاصيل الاتفاق..."
                          class="dash-field px-4 py-2.5 resize-none @error('notes') dash-field-error @enderror">{{ old('notes') }}</textarea>
                @error('notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="flex-1 py-2.5 bg-brand hover:bg-brand-600
                               text-white text-sm font-semibold rounded-btn transition-colors">
                    حفظ الدين
                </button>
                <a href="{{ route('debts.index') }}"
                   class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200
                          text-slate-700 text-sm font-medium rounded-btn transition-colors text-center">
                    إلغاء
                </a>
            </div>

        </form>
    </div>

</div>
@endsection

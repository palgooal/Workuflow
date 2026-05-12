@extends('layouts.app')

@section('title', 'إضافة دين جديد')

@section('breadcrumb')
    <a href="{{ route('debts.index') }}" class="hover:text-gray-700">الديون</a>
    <svg class="w-3 h-3 mx-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
    </svg>
    <span>إضافة دين</span>
@endsection

@section('content')
<div class="max-w-xl mx-auto">

    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900">إضافة دين جديد</h1>
        <p class="mt-1 text-sm text-gray-500">سجّل ديناً عليك أو ديناً لك</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 p-6"
         x-data="{
             type: '{{ old('type', 'borrowed') }}',
             get typeLabel() {
                 return this.type === 'borrowed' ? 'دين عليّ (اقترضت)' : 'دين لي (أقرضت)';
             }
         }">

        <form method="POST" action="{{ route('debts.store') }}" class="space-y-5">
            @csrf

            {{-- Type Toggle --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">نوع الدين</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="borrowed"
                               x-model="type" class="sr-only">
                        <div :class="type === 'borrowed'
                                ? 'border-red-400 bg-red-50 text-red-700 ring-2 ring-red-300'
                                : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300'"
                             class="border-2 rounded-xl p-3.5 text-center transition">
                            <div class="text-2xl mb-1">💸</div>
                            <p class="text-sm font-semibold">دين عليّ</p>
                            <p class="text-xs mt-0.5 opacity-70">اقترضت من شخص</p>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="lent"
                               x-model="type" class="sr-only">
                        <div :class="type === 'lent'
                                ? 'border-green-400 bg-green-50 text-green-700 ring-2 ring-green-300'
                                : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300'"
                             class="border-2 rounded-xl p-3.5 text-center transition">
                            <div class="text-2xl mb-1">🤝</div>
                            <p class="text-sm font-semibold">دين لي</p>
                            <p class="text-xs mt-0.5 opacity-70">أقرضت شخصاً</p>
                        </div>
                    </label>
                </div>
                @error('type')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Party Name --}}
            <div>
                <label for="party_name" class="block text-sm font-medium text-gray-700 mb-1.5">
                    <span x-text="type === 'borrowed' ? 'اسم المُقرِض (من أخذت منه)' : 'اسم المُقترِض (من أعطيته)'"></span>
                </label>
                <input type="text" id="party_name" name="party_name"
                       value="{{ old('party_name') }}" required
                       :placeholder="type === 'borrowed' ? 'مثال: أحمد محمد' : 'مثال: شركة XYZ'"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200
                              focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                @error('party_name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Amount + Currency --}}
            <div class="grid grid-cols-3 gap-3">
                <div class="col-span-2">
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-1.5">المبلغ</label>
                    <input type="number" id="amount" name="amount"
                           value="{{ old('amount') }}" required min="0.01" step="0.01"
                           placeholder="0.00"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    @error('amount')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="currency" class="block text-sm font-medium text-gray-700 mb-1.5">العملة</label>
                    <select id="currency" name="currency"
                            class="w-full px-3 py-2.5 rounded-xl border border-gray-200 bg-white
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
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
                <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1.5">
                    تاريخ الاستحقاق
                    <span class="text-gray-400 font-normal">(اختياري)</span>
                </label>
                <input type="date" id="due_date" name="due_date"
                       value="{{ old('due_date') }}"
                       min="{{ date('Y-m-d') }}"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200
                              focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                @error('due_date')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Notes --}}
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1.5">
                    ملاحظات
                    <span class="text-gray-400 font-normal">(اختياري)</span>
                </label>
                <textarea id="notes" name="notes" rows="3"
                          placeholder="سبب الدين، تفاصيل الاتفاق..."
                          class="w-full px-4 py-2.5 rounded-xl border border-gray-200
                                 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm resize-none">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700
                               text-white text-sm font-medium rounded-xl transition">
                    حفظ الدين
                </button>
                <a href="{{ route('debts.index') }}"
                   class="flex-1 py-2.5 bg-gray-100 hover:bg-gray-200
                          text-gray-700 text-sm font-medium rounded-xl transition text-center">
                    إلغاء
                </a>
            </div>

        </form>
    </div>

</div>
@endsection

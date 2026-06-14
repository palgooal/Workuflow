{{-- Shared form for create & edit --}}
<div class="bg-white rounded-2xl border border-gray-100 p-6"
     x-data="{
         selectedType: '{{ old('type', $transaction->type->value ?? 'income') }}',
         filteredCategories: [],
         allCategories: {{ $categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'icon' => $c->icon, 'type' => $c->type->value])->toJson() }},
         init() {
             this.filterCategories();
             this.$watch('selectedType', () => this.filterCategories());
         },
         filterCategories() {
             this.filteredCategories = this.allCategories.filter(c => c.type === this.selectedType || c.type === 'transfer');
         }
     }">

    <div class="space-y-5">

        {{-- Type Toggle --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">نوع المعاملة <span class="text-red-500">*</span></label>
            <div class="grid grid-cols-2 gap-3">
                <label class="cursor-pointer" @click="selectedType = 'income'">
                    <input type="radio" name="type" value="income" x-model="selectedType" class="sr-only">
                    <div class="flex items-center gap-3 p-4 rounded-xl border-2 transition"
                         :class="selectedType === 'income' ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-gray-300'">
                        <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">دخل</p>
                            <p class="text-xs text-gray-400">مبلغ مستلم</p>
                        </div>
                    </div>
                </label>
                <label class="cursor-pointer" @click="selectedType = 'expense'">
                    <input type="radio" name="type" value="expense" x-model="selectedType" class="sr-only">
                    <div class="flex items-center gap-3 p-4 rounded-xl border-2 transition"
                         :class="selectedType === 'expense' ? 'border-red-500 bg-red-50' : 'border-gray-200 hover:border-gray-300'">
                        <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">مصروف</p>
                            <p class="text-xs text-gray-400">مبلغ مدفوع</p>
                        </div>
                    </div>
                </label>
            </div>
            @error('type') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Amount + Currency --}}
        <div class="grid grid-cols-3 gap-4">
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">المبلغ <span class="text-red-500">*</span></label>
                <input type="number" name="amount" step="0.01" min="0"
                       value="{{ old('amount', $transaction->amount ?? '') }}"
                       placeholder="0.00"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm
                              focus:outline-none focus:ring-2 focus:ring-indigo-500
                              @error('amount') border-red-300 @enderror">
                @error('amount') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">العملة</label>
                <select name="currency"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm bg-white
                               focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach($currencies as $code => $label)
                        <option value="{{ $code }}"
                                {{ old('currency', $transaction->currency ?? auth()->user()->currency) === $code ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Description --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">الوصف <span class="text-red-500">*</span></label>
            <input type="text" name="description"
                   value="{{ old('description', $transaction->description ?? '') }}"
                   placeholder="وصف مختصر للمعاملة..."
                   class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-500
                          @error('description') border-red-300 @enderror">
            @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Payee — يظهر فقط عند المصروف --}}
        <div x-show="selectedType === 'expense'" x-transition>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                جهة الدفع
                <span class="text-gray-400 font-normal">(المورد / الجهة المستفيدة)</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 5h1"/>
                    </svg>
                </div>
                <input type="text" name="payee"
                       value="{{ old('payee', $transaction->payee ?? '') }}"
                       placeholder="مثال: مطبعة النور، Adobe، مصور فريلانس..."
                       class="w-full pr-9 pl-3.5 py-2.5 rounded-xl border border-gray-200 text-sm
                              focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent
                              @error('payee') border-red-300 bg-red-50 @enderror">
            </div>
            @error('payee') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Date + Project --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">التاريخ <span class="text-red-500">*</span></label>
                <input type="date" name="transaction_date"
                       value="{{ old('transaction_date', isset($transaction) ? $transaction->transaction_date->format('Y-m-d') : now()->format('Y-m-d')) }}"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm
                              focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('transaction_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">المشروع</label>
                <select name="project_id"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm bg-white
                               focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">بدون مشروع</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}"
                                {{ old('project_id', $transaction->project_id ?? $preProject ?? '') === $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- الصندوق (إجباري) --}}
        @if(isset($transaction) && !$transaction->wallet_id)
        <div class="rounded-xl border-2 border-amber-300 bg-amber-50 p-3 flex items-start gap-2">
            <span class="text-amber-500 text-lg leading-none mt-0.5">⚠️</span>
            <div>
                <p class="text-sm font-semibold text-amber-800">هذه المعاملة لا تنتمي لأي صندوق</p>
                <p class="text-xs text-amber-600 mt-0.5">يجب تحديد صندوق لحفظ التعديلات — الأموال لا تُسجَّل بدون صندوق.</p>
            </div>
        </div>
        @endif
        <div class="rounded-xl border-2 {{ $errors->has('wallet_id') ? 'border-red-400 bg-red-50' : 'border-indigo-100 bg-indigo-50' }} p-4">
            <label class="block text-sm font-semibold text-indigo-800 mb-2">
                🏦 الصندوق / الخزينة <span class="text-red-500">*</span>
                <span class="text-xs font-normal text-indigo-500 mr-1">— إلى أين ستذهب الأموال؟</span>
            </label>
            @if(!empty($wallets) && $wallets->isNotEmpty())
                <select name="wallet_id" required
                        class="w-full px-3.5 py-2.5 rounded-xl border text-sm bg-white
                               focus:outline-none focus:ring-2 focus:ring-indigo-500
                               {{ $errors->has('wallet_id') ? 'border-red-400' : 'border-indigo-200' }}">
                    <option value="">— اختر الصندوق —</option>
                    @foreach($wallets as $wallet)
                        <option value="{{ $wallet->id }}"
                                {{ old('wallet_id', $transaction->wallet_id ?? request('wallet_id') ?? '') === $wallet->id ? 'selected' : '' }}>
                            {{ $wallet->icon ?: $wallet->type->icon() }} {{ $wallet->name }} ({{ $wallet->currency }})
                        </option>
                    @endforeach
                </select>
                @error('wallet_id')
                    <p class="mt-1.5 text-xs text-red-600 font-medium">⚠️ {{ $message }}</p>
                @enderror
            @else
                <div class="flex items-center justify-between">
                    <p class="text-sm text-amber-700">⚠️ لا يوجد صناديق — يجب إنشاء صندوق أولاً قبل تسجيل معاملة.</p>
                    <a href="{{ route('wallets.create') }}" target="_blank"
                       class="text-xs px-3 py-1.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        + صندوق جديد
                    </a>
                </div>
            @endif
        </div>

        {{-- Category (dynamic by type) --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">الفئة</label>
            <select name="category_id"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm bg-white
                           focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">بدون فئة</option>
                <template x-for="cat in filteredCategories" :key="cat.id">
                    <option :value="cat.id"
                            :selected="cat.id === '{{ old('category_id', $transaction->category_id ?? '') }}'">
                        <span x-text="cat.icon + ' ' + cat.name"></span>
                    </option>
                </template>
            </select>
        </div>

        {{-- Notes + Reference --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    ملاحظات <span class="text-gray-400 font-normal">(اختياري)</span>
                </label>
                <textarea name="notes" rows="2"
                          placeholder="أي تفاصيل إضافية..."
                          class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm resize-none
                                 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes', $transaction->notes ?? '') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    المرجع <span class="text-gray-400 font-normal">(اختياري)</span>
                </label>
                <input type="text" name="reference"
                       value="{{ old('reference', $transaction->reference ?? '') }}"
                       placeholder="رقم فاتورة، رقم حوالة..."
                       class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm
                              focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between pt-2 border-t border-gray-100">
            <a href="{{ route('transactions.index') }}"
               class="text-sm font-medium text-gray-500 hover:text-gray-700 transition">
                إلغاء
            </a>
            <div class="flex items-center gap-3">
                @unless(isset($transaction))
                {{-- زر حفظ وإضافة آخر --}}
                <button type="submit" name="redirect_to" value="{{ route('transactions.create') }}"
                        class="px-4 py-2.5 border border-gray-200 text-gray-700 text-sm font-medium rounded-xl
                               hover:bg-gray-50 transition">
                    حفظ وإضافة آخرى
                </button>
                @endunless
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium
                               text-white rounded-xl transition
                               {{ isset($transaction) ? 'bg-indigo-600 hover:bg-indigo-700' : '' }}"
                        :class="selectedType === 'income'
                            ? 'bg-green-600 hover:bg-green-700'
                            : 'bg-red-600 hover:bg-red-700'"
                        x-bind:class="!{{ isset($transaction) ? 'true' : 'false' }} ? (selectedType === 'income' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700') : 'bg-indigo-600 hover:bg-indigo-700'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ isset($transaction) ? 'حفظ التعديلات' : 'إضافة المعاملة' }}
                </button>
            </div>
        </div>

    </div>
</div>

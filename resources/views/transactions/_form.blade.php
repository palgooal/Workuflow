{{-- Shared form for create & edit --}}
<div class="dash-card p-6 sm:p-8"
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

    <div class="space-y-6">

        {{-- ── نوع المعاملة ── --}}
        <div>
            <label class="block text-sm font-semibold text-ink mb-2">
                نوع المعاملة <span class="text-red-500">*</span>
            </label>
            <div class="grid grid-cols-2 gap-3">
                <label class="cursor-pointer" @click="selectedType = 'income'">
                    <input type="radio" name="type" value="income" x-model="selectedType" class="sr-only">
                    <div class="flex items-center gap-3 p-4 rounded-xl border-2 transition-all"
                         :class="selectedType === 'income'
                             ? 'border-emerald-500 bg-emerald-50 shadow-sm'
                             : 'border-subtle bg-surface hover:border-slate-300'">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                             :class="selectedType === 'income' ? 'bg-emerald-100' : 'bg-slate-100'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"
                                 :class="selectedType === 'income' ? 'text-emerald-600' : 'text-slate-400'">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold"
                               :class="selectedType === 'income' ? 'text-emerald-700' : 'text-ink'">دخل</p>
                            <p class="text-xs text-muted">مبلغ مستلم</p>
                        </div>
                    </div>
                </label>
                <label class="cursor-pointer" @click="selectedType = 'expense'">
                    <input type="radio" name="type" value="expense" x-model="selectedType" class="sr-only">
                    <div class="flex items-center gap-3 p-4 rounded-xl border-2 transition-all"
                         :class="selectedType === 'expense'
                             ? 'border-red-400 bg-red-50 shadow-sm'
                             : 'border-subtle bg-surface hover:border-slate-300'">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                             :class="selectedType === 'expense' ? 'bg-red-100' : 'bg-slate-100'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"
                                 :class="selectedType === 'expense' ? 'text-red-600' : 'text-slate-400'">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold"
                               :class="selectedType === 'expense' ? 'text-red-700' : 'text-ink'">مصروف</p>
                            <p class="text-xs text-muted">مبلغ مدفوع</p>
                        </div>
                    </div>
                </label>
            </div>
            @error('type') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- ── المبلغ + العملة ── --}}
        <div class="grid grid-cols-3 gap-4">
            <div class="col-span-2">
                <label class="block text-sm font-semibold text-ink mb-1.5">
                    المبلغ <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <input type="number" name="amount" step="0.01" min="0"
                           value="{{ old('amount', $transaction->amount ?? '') }}"
                           placeholder="0.00"
                           class="dash-field pr-9 pl-3.5 py-2.5 nums @error('amount') dash-field-error @enderror">
                </div>
                @error('amount') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-ink mb-1.5">العملة</label>
                <select name="currency" class="dash-field px-3.5 py-2.5">
                    @foreach($currencies as $code => $label)
                        <option value="{{ $code }}"
                                {{ old('currency', $transaction->currency ?? auth()->user()->currency) === $code ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- ── الوصف ── --}}
        <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">
                الوصف <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <input type="text" name="description"
                       value="{{ old('description', $transaction->description ?? '') }}"
                       placeholder="وصف مختصر للمعاملة..."
                       class="dash-field pr-9 pl-3.5 py-2.5 @error('description') dash-field-error @enderror">
            </div>
            @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- ── التاريخ + المشروع ── --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-ink mb-1.5">
                    التاريخ <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <input type="date" name="transaction_date"
                           value="{{ old('transaction_date', isset($transaction) ? $transaction->transaction_date->format('Y-m-d') : now()->format('Y-m-d')) }}"
                           class="dash-field pr-9 pl-3.5 py-2.5 nums">
                </div>
                @error('transaction_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-ink mb-1.5">المشروع</label>
                <div class="relative">
                    <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                    </div>
                    <select name="project_id" class="dash-field pr-9 pl-3.5 py-2.5">
                        <option value="">بدون مشروع</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}"
                                    {{ old('project_id', $transaction->project_id ?? $preProject ?? '') == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- ── جهة الدفع (عند المصروف فقط) ── --}}
        <div x-show="selectedType === 'expense'" x-transition>
            <label class="block text-sm font-semibold text-ink mb-1.5">
                جهة الدفع
                <span class="text-muted font-normal text-xs">(المورد / الجهة المستفيدة)</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 5h1"/>
                    </svg>
                </div>
                <input type="text" name="payee"
                       value="{{ old('payee', $transaction->payee ?? '') }}"
                       placeholder="مثال: مطبعة النور، Adobe، مصور فريلانس..."
                       class="dash-field pr-9 pl-3.5 py-2.5 @error('payee') dash-field-error @enderror">
            </div>
            @error('payee') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- ── الصندوق (إجباري) ── --}}
        @if(isset($transaction) && !$transaction->wallet_id)
        <div class="rounded-xl border border-amber-300 bg-amber-50 p-3 flex items-start gap-2.5">
            <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.74-2.99l-6.93-12a2 2 0 00-3.48 0l-6.93 12A2 2 0 005.07 19z"/>
            </svg>
            <div>
                <p class="text-sm font-semibold text-amber-800">هذه المعاملة لا تنتمي لأي صندوق</p>
                <p class="text-xs text-amber-700 mt-0.5">يجب تحديد صندوق لحفظ التعديلات — الأموال لا تُسجَّل بدون صندوق.</p>
            </div>
        </div>
        @endif

        <div class="rounded-xl border {{ $errors->has('wallet_id') ? 'border-error bg-error-soft' : 'border-brand-100 bg-brand-50/60' }} p-4">
            <label class="flex items-center gap-2 text-sm font-semibold text-brand-700 mb-2.5">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                الصندوق / الخزينة <span class="text-red-500">*</span>
                <span class="text-xs font-normal text-brand/70">— إلى أين ستذهب الأموال؟</span>
            </label>
            @if(!empty($wallets) && $wallets->isNotEmpty())
                <div class="relative">
                    <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <select name="wallet_id" required
                            class="dash-field pr-9 pl-3.5 py-2.5 {{ $errors->has('wallet_id') ? 'dash-field-error' : '' }}">
                        <option value="">— اختر الصندوق —</option>
                        @foreach($wallets as $wallet)
                            <option value="{{ $wallet->id }}"
                                    {{ old('wallet_id', $transaction->wallet_id ?? request('wallet_id') ?? '') == $wallet->id ? 'selected' : '' }}>
                                {{ $wallet->icon ?: $wallet->type->icon() }} {{ $wallet->name }} ({{ $wallet->currency }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('wallet_id')
                    <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            @else
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm text-amber-700">لا يوجد صناديق — يجب إنشاء صندوق أولاً قبل تسجيل معاملة.</p>
                    <a href="{{ route('wallets.create') }}" target="_blank"
                       class="text-xs px-3 py-1.5 bg-brand text-white rounded-lg hover:bg-brand-600 transition-colors shrink-0">
                        + صندوق جديد
                    </a>
                </div>
            @endif
        </div>

        {{-- ── الفئة (ديناميكية حسب النوع) ── --}}
        <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">الفئة</label>
            <div class="relative">
                <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
                <select name="category_id" class="dash-field pr-9 pl-3.5 py-2.5">
                    <option value="">بدون فئة</option>
                    <template x-for="cat in filteredCategories" :key="cat.id">
                        <option :value="cat.id"
                                :selected="cat.id === '{{ old('category_id', $transaction->category_id ?? '') }}'">
                            <span x-text="cat.icon + ' ' + cat.name"></span>
                        </option>
                    </template>
                </select>
            </div>
        </div>

        {{-- ── ملاحظات + مرجع ── --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-ink mb-1.5">
                    ملاحظات <span class="text-muted font-normal text-xs">(اختياري)</span>
                </label>
                <textarea name="notes" rows="2"
                          placeholder="أي تفاصيل إضافية..."
                          class="dash-field px-3.5 py-2.5 resize-none">{{ old('notes', $transaction->notes ?? '') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-ink mb-1.5">
                    المرجع <span class="text-muted font-normal text-xs">(اختياري)</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <input type="text" name="reference"
                           value="{{ old('reference', $transaction->reference ?? '') }}"
                           placeholder="رقم فاتورة، رقم حوالة..."
                           class="dash-field pr-9 pl-3.5 py-2.5">
                </div>
            </div>
        </div>

        {{-- ── أزرار الحفظ ── --}}
        <div class="flex items-center justify-between pt-4 border-t border-subtle">
            <a href="{{ route('transactions.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-muted
                      hover:text-ink hover:bg-slate-100 rounded-btn transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                إلغاء
            </a>
            <div class="flex items-center gap-3">
                @unless(isset($transaction))
                <button type="submit" name="redirect_to" value="{{ route('transactions.create') }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 border border-subtle text-slate-700
                               text-sm font-medium rounded-btn hover:bg-slate-50 transition-colors">
                    <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    حفظ وإضافة أخرى
                </button>
                @endunless
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold
                               text-white rounded-btn transition-colors shadow-sm
                               {{ isset($transaction) ? 'bg-brand hover:bg-brand-600' : '' }}"
                        x-bind:class="!{{ isset($transaction) ? 'true' : 'false' }}
                            ? (selectedType === 'income' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-red-600 hover:bg-red-700')
                            : 'bg-brand hover:bg-brand-600'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ isset($transaction) ? 'حفظ التعديلات' : 'إضافة المعاملة' }}
                </button>
            </div>
        </div>

    </div>
</div>

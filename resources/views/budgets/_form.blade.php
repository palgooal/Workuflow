@php
    $isEdit  = isset($budget);
    $oldAmt  = old('amount',      $isEdit ? $budget->amount      : '');
    $oldPer  = old('period',      $isEdit ? $budget->period      : 'monthly');
    $oldMon  = old('month',       $isEdit ? $budget->month       : $currentMonth);
    $oldYear = old('year',        $isEdit ? $budget->year        : $currentYear);
    $oldCat  = old('category_id', $isEdit ? $budget->category_id : '');
    $oldProj = old('project_id',  $isEdit ? $budget->project_id  : '');
@endphp

<div x-data="{ period: '{{ $oldPer }}' }" class="space-y-6">

    {{-- خطأ تكرار --}}
    @if($errors->has('duplicate'))
        <div class="bg-error-soft border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.74-2.99l-6.93-12a2 2 0 00-3.48 0l-6.93 12A2 2 0 005.07 19z"/>
            </svg>
            {{ $errors->first('duplicate') }}
        </div>
    @endif

    {{-- المبلغ المخصص --}}
    <div>
        <label class="block text-sm font-semibold text-ink mb-1.5">
            المبلغ المخصص <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-muted">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <input type="number" name="amount" step="0.01" min="1"
                   value="{{ $oldAmt }}"
                   placeholder="0.00"
                   class="dash-field pr-9 py-2.5 nums @error('amount') dash-field-error @enderror">
        </div>
        @error('amount')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    {{-- الفترة --}}
    <div>
        <label class="block text-sm font-semibold text-ink mb-2">نوع الفترة</label>
        <div class="grid grid-cols-2 gap-3">
            <label class="cursor-pointer" @click="period = 'monthly'">
                <input type="radio" name="period" value="monthly" x-model="period" class="sr-only">
                <div class="flex items-center gap-3 p-3.5 rounded-xl border-2 transition-all"
                     :class="period === 'monthly'
                         ? 'border-brand bg-brand-50'
                         : 'border-subtle bg-surface hover:border-slate-300'">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                         :class="period === 'monthly' ? 'bg-brand text-white' : 'bg-slate-100 text-muted'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold" :class="period === 'monthly' ? 'text-brand' : 'text-ink'">شهرية</p>
                        <p class="text-xs text-muted">تتجدد كل شهر</p>
                    </div>
                </div>
            </label>
            <label class="cursor-pointer" @click="period = 'yearly'">
                <input type="radio" name="period" value="yearly" x-model="period" class="sr-only">
                <div class="flex items-center gap-3 p-3.5 rounded-xl border-2 transition-all"
                     :class="period === 'yearly'
                         ? 'border-brand bg-brand-50'
                         : 'border-subtle bg-surface hover:border-slate-300'">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                         :class="period === 'yearly' ? 'bg-brand text-white' : 'bg-slate-100 text-muted'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold" :class="period === 'yearly' ? 'text-brand' : 'text-ink'">سنوية</p>
                        <p class="text-xs text-muted">للسنة كاملة</p>
                    </div>
                </div>
            </label>
        </div>
    </div>

    {{-- الشهر + السنة --}}
    <div class="grid grid-cols-2 gap-4">
        <div x-show="period === 'monthly'" x-transition>
            <label class="block text-sm font-semibold text-ink mb-1.5">
                الشهر <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-muted">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </span>
                <select name="month" x-model.number="month"
                        class="dash-field pr-9 py-2.5 @error('month') dash-field-error @enderror">
                    @foreach($months as $num => $name)
                        <option value="{{ $num }}" @selected($oldMon == $num)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div :class="period === 'yearly' ? 'col-span-2' : ''">
            <label class="block text-sm font-semibold text-ink mb-1.5">
                السنة <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-muted">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </span>
                <select name="year" x-model.number="year"
                        class="dash-field pr-9 py-2.5 @error('year') dash-field-error @enderror">
                    @foreach($years as $y)
                        <option value="{{ $y }}" @selected($oldYear == $y)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- الفئة --}}
    <div>
        <label class="block text-sm font-semibold text-ink mb-1.5">
            الفئة
            <span class="text-muted font-normal text-xs">(اختياري — اتركه فارغاً لتشمل كل الفئات)</span>
        </label>
        <div class="relative">
            <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-muted">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
            </span>
            <select name="category_id" class="dash-field pr-9 py-2.5">
                <option value="">— عام (كل الفئات) —</option>
                @foreach($categories->groupBy('type') as $type => $cats)
                    <optgroup label="{{ $type === 'expense' ? 'مصروفات' : 'دخل' }}">
                        @foreach($cats as $cat)
                            <option value="{{ $cat->id }}" @selected($oldCat == $cat->id)>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>
    </div>

    {{-- المشروع --}}
    @if($projects->isNotEmpty())
    <div>
        <label class="block text-sm font-semibold text-ink mb-1.5">
            المشروع
            <span class="text-muted font-normal text-xs">(اختياري)</span>
        </label>
        <div class="relative">
            <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-muted">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
            </span>
            <select name="project_id" class="dash-field pr-9 py-2.5">
                <option value="">— كل المشاريع —</option>
                @foreach($projects as $proj)
                    <option value="{{ $proj->id }}" @selected($oldProj == $proj->id)>{{ $proj->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    @endif

    {{-- أزرار --}}
    <div class="flex gap-3 pt-2">
        <button type="submit"
                class="flex-1 inline-flex items-center justify-center gap-2 py-2.5 bg-brand text-white rounded-btn text-sm font-semibold hover:bg-brand-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            {{ $isEdit ? 'حفظ التعديلات' : 'إنشاء الميزانية' }}
        </button>
        <a href="{{ route('budget.index') }}"
           class="flex-1 inline-flex items-center justify-center gap-2 py-2.5 bg-slate-100 text-slate-700 rounded-btn text-sm font-medium hover:bg-slate-200 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            إلغاء
        </a>
    </div>

</div>

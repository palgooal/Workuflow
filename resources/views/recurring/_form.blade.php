@php
    $isEdit     = isset($recurring);
    $oldType    = old('type',        $isEdit ? $recurring->type->value        : 'expense');
    $oldDesc    = old('description', $isEdit ? $recurring->description        : '');
    $oldAmt     = old('amount',      $isEdit ? $recurring->amount             : '');
    $oldFreq    = old('frequency',   $isEdit ? $recurring->frequency->value   : 'monthly');
    $oldStart   = old('start_date',  $isEdit ? $recurring->start_date->toDateString() : today()->toDateString());
    $oldEnd     = old('end_date',    $isEdit ? $recurring->end_date?->toDateString()  : '');
    $oldCat     = old('category_id', $isEdit ? $recurring->category_id        : '');
    $oldProj    = old('project_id',  $isEdit ? $recurring->project_id         : '');
    $hasEnd     = $isEdit && $recurring->end_date ? 'true' : 'false';
@endphp

<div x-data="{
    type: '{{ $oldType }}',
    hasEndDate: {{ $hasEnd }},
}" class="space-y-6">

    {{-- النوع --}}
    <div>
        <label class="block text-sm font-semibold text-ink mb-2">نوع الالتزام</label>
        <div class="grid grid-cols-2 gap-3">
            <label class="cursor-pointer" @click="type = 'expense'">
                <input type="radio" name="type" value="expense" x-model="type" class="sr-only">
                <div class="flex items-center gap-3 p-3.5 rounded-xl border-2 transition-all"
                     :class="type === 'expense'
                         ? 'border-red-400 bg-red-50'
                         : 'border-subtle bg-surface hover:border-slate-300'">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                         :class="type === 'expense' ? 'bg-red-500 text-white' : 'bg-slate-100 text-muted'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold" :class="type === 'expense' ? 'text-red-600' : 'text-ink'">مصروف</p>
                        <p class="text-xs text-muted">إيجار، اشتراك، ...</p>
                    </div>
                </div>
            </label>
            <label class="cursor-pointer" @click="type = 'income'">
                <input type="radio" name="type" value="income" x-model="type" class="sr-only">
                <div class="flex items-center gap-3 p-3.5 rounded-xl border-2 transition-all"
                     :class="type === 'income'
                         ? 'border-emerald-400 bg-emerald-50'
                         : 'border-subtle bg-surface hover:border-slate-300'">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                         :class="type === 'income' ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-muted'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold" :class="type === 'income' ? 'text-emerald-600' : 'text-ink'">دخل</p>
                        <p class="text-xs text-muted">راتب، عائد، ...</p>
                    </div>
                </div>
            </label>
        </div>
        @error('type')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    {{-- الوصف --}}
    <div>
        <label class="block text-sm font-semibold text-ink mb-1.5">
            الوصف <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-muted">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </span>
            <input type="text" name="description"
                   value="{{ $oldDesc }}"
                   placeholder="مثال: إيجار المكتب، راتب شهري، ..."
                   class="dash-field pr-9 py-2.5 @error('description') dash-field-error @enderror">
        </div>
        @error('description')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    {{-- المبلغ + التكرار --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">
                المبلغ <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-muted">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <input type="number" name="amount" step="0.01" min="0.01"
                       value="{{ $oldAmt }}"
                       placeholder="0.00"
                       class="dash-field pr-9 py-2.5 nums @error('amount') dash-field-error @enderror">
            </div>
            @error('amount')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">التكرار</label>
            <div class="relative">
                <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-muted">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </span>
                <select name="frequency" class="dash-field pr-9 py-2.5">
                    @foreach($frequencies as $val => $label)
                        <option value="{{ $val }}" @selected($oldFreq === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- تاريخ البداية --}}
    <div>
        <label class="block text-sm font-semibold text-ink mb-1.5">
            تاريخ البداية <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-muted">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </span>
            <input type="date" name="start_date"
                   value="{{ $oldStart }}"
                   class="dash-field pr-9 py-2.5 @error('start_date') dash-field-error @enderror">
        </div>
        @error('start_date')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    {{-- تاريخ الانتهاء (اختياري) --}}
    <div class="space-y-2">
        <label class="flex items-center gap-2.5 cursor-pointer select-none group">
            <div class="relative">
                <input type="checkbox" x-model="hasEndDate"
                       class="w-4 h-4 rounded border-subtle text-brand focus:ring-accent/40 cursor-pointer">
            </div>
            <span class="text-sm font-medium text-ink group-hover:text-brand transition-colors">تحديد تاريخ انتهاء</span>
            <span class="text-xs text-muted">(اختياري)</span>
        </label>
        <div x-show="hasEndDate" x-transition class="relative">
            <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-muted">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </span>
            <input type="date" name="end_date"
                   value="{{ $oldEnd }}"
                   class="dash-field pr-9 py-2.5 @error('end_date') dash-field-error @enderror">
            @error('end_date')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    {{-- الفئة --}}
    <div>
        <label class="block text-sm font-semibold text-ink mb-1.5">
            الفئة
            <span class="text-muted font-normal text-xs">(اختياري)</span>
        </label>
        <div class="relative">
            <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-muted">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
            </span>
            <select name="category_id" class="dash-field pr-9 py-2.5">
                <option value="">— بدون فئة —</option>
                @foreach($categories->groupBy('type') as $catType => $cats)
                    <optgroup label="{{ $catType === 'expense' ? 'مصروفات' : 'دخل' }}">
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
                class="flex-1 inline-flex items-center justify-center gap-2 py-2.5 rounded-btn text-sm font-semibold transition-colors text-white"
                :class="type === 'income' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-brand hover:bg-brand-600'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            {{ $isEdit ? 'حفظ التعديلات' : 'إنشاء الالتزام' }}
        </button>
        <a href="{{ route('recurring.index') }}"
           class="flex-1 inline-flex items-center justify-center gap-2 py-2.5 bg-slate-100 text-slate-700 rounded-btn text-sm font-medium hover:bg-slate-200 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            إلغاء
        </a>
    </div>

</div>

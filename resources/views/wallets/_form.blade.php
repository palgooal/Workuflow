@php
    $isEdit      = isset($wallet);
    $oldName     = old('name',            $isEdit ? $wallet->name            : '');
    $oldDesc     = old('description',     $isEdit ? $wallet->description     : '');
    $oldCurrency = old('currency',        $isEdit ? $wallet->currency        : (auth()->user()->currency ?? 'SAR'));
    $oldBalance  = old('initial_balance', $isEdit ? $wallet->initial_balance : 0);
    $oldColor    = old('color',           $isEdit ? $wallet->color           : '#310E8E');
    $oldIcon     = old('icon',            $isEdit ? $wallet->icon            : '');
    $oldType     = old('type',            $isEdit ? $wallet->type->value     : 'cash');
    $oldActive   = old('is_active',       $isEdit ? $wallet->is_active       : true);

    $presetColors = [
        '#310E8E','#13C597','#3B82F6','#8B5CF6','#F59E0B',
        '#EF4444','#10B981','#06B6D4','#F97316','#6366F1',
        '#EC4899','#64748B',
    ];
@endphp

<div x-data="{
    selectedType: '{{ $oldType }}',
    selectedColor: '{{ $oldColor }}',
    iconValue: '{{ $oldIcon }}'
}" class="space-y-6">

    {{-- اسم الصندوق --}}
    <div>
        <label class="block text-sm font-semibold text-ink mb-1.5">
            اسم الصندوق <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-muted">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </span>
            <input type="text" name="name" value="{{ $oldName }}" required
                   placeholder="مثال: كاش يد، حساب الراجحي..."
                   class="dash-field pr-9 py-2.5 @error('name') dash-field-error @enderror">
        </div>
        @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    {{-- نوع الصندوق --}}
    <div>
        <label class="block text-sm font-semibold text-ink mb-2">
            نوع الصندوق <span class="text-red-500">*</span>
        </label>
        <div class="grid grid-cols-3 gap-3">
            @foreach($types as $type)
            <label class="cursor-pointer" @click="selectedType = '{{ $type->value }}'">
                <input type="radio" name="type" value="{{ $type->value }}"
                       {{ $oldType === $type->value ? 'checked' : '' }}
                       class="sr-only">
                <div class="border-2 rounded-xl p-3.5 text-center transition-all"
                     :class="selectedType === '{{ $type->value }}'
                         ? 'border-brand bg-brand-50'
                         : 'border-subtle hover:border-slate-300 bg-surface'">
                    <div class="text-2xl mb-1.5">{{ $type->icon() }}</div>
                    <div class="text-xs font-semibold"
                         :class="selectedType === '{{ $type->value }}' ? 'text-brand' : 'text-slate-600'">
                        {{ $type->label() }}
                    </div>
                </div>
            </label>
            @endforeach
        </div>
        @error('type')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    {{-- العملة + الرصيد --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">
                العملة <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-muted">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <select name="currency" required class="dash-field pr-9 py-2.5">
                    @foreach($currencies as $code => $label)
                        <option value="{{ $code }}"
                            {{ $oldCurrency === $code ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">الرصيد الافتتاحي</label>
            <div class="relative">
                <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-muted">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </span>
                <input type="number" name="initial_balance" value="{{ $oldBalance }}"
                       step="0.01" min="0"
                       class="dash-field pr-9 py-2.5 nums">
            </div>
        </div>
    </div>

    {{-- اللون --}}
    <div>
        <label class="block text-sm font-semibold text-ink mb-2">لون الصندوق</label>
        <div class="bg-slate-50 border border-subtle rounded-xl p-4 space-y-3">
            {{-- ألوان مسبقة --}}
            <div class="flex flex-wrap gap-2">
                @foreach($presetColors as $color)
                <button type="button"
                        @click="selectedColor = '{{ $color }}'; $refs.colorInput.value = '{{ $color }}'"
                        class="w-8 h-8 rounded-full border-2 transition-all hover:scale-110 focus:outline-none"
                        :class="selectedColor === '{{ $color }}' ? 'border-ink scale-110 shadow-sm' : 'border-transparent'"
                        style="background-color: {{ $color }}"
                        title="{{ $color }}">
                </button>
                @endforeach

                {{-- Custom color picker --}}
                <label class="relative w-8 h-8 rounded-full border-2 cursor-pointer overflow-hidden hover:scale-110 transition-all"
                       :class="!['{{ implode("','", $presetColors) }}'].includes(selectedColor) ? 'border-ink scale-110 shadow-sm' : 'border-dashed border-subtle'"
                       title="لون مخصص"
                       style="background: conic-gradient(red, yellow, lime, aqua, blue, magenta, red)">
                    <input type="color" x-ref="colorInput"
                           :value="selectedColor"
                           @input="selectedColor = $event.target.value"
                           class="absolute inset-0 opacity-0 w-full h-full cursor-pointer">
                </label>
            </div>

            {{-- الحقل الفعلي المُرسَل --}}
            <input type="hidden" name="color" :value="selectedColor">

            {{-- معاينة --}}
            <div class="flex items-center gap-3 pt-1">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl"
                     :style="'background-color: ' + selectedColor + '22'">
                    <span x-text="iconValue || '💰'"></span>
                </div>
                <div>
                    <div class="h-2 rounded-full w-24 mb-1" :style="'background-color: ' + selectedColor"></div>
                    <p class="text-xs text-muted">معاينة لون الصندوق</p>
                </div>
                <div class="mr-auto text-xs font-mono text-muted bg-white border border-subtle px-2 py-1 rounded-lg" x-text="selectedColor"></div>
            </div>
        </div>
    </div>

    {{-- الأيقونة --}}
    <div>
        <label class="block text-sm font-semibold text-ink mb-1.5">أيقونة الصندوق (اختياري)</label>
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-2xl border border-subtle bg-surface shrink-0"
                 :style="'background-color: ' + selectedColor + '15'">
                <span x-text="iconValue || '💰'"></span>
            </div>
            <input type="text" name="icon" x-model="iconValue"
                   placeholder="💰  🏦  💳  🏠  📦  🛒"
                   class="dash-field py-2.5 px-3.5 flex-1">
        </div>
        <p class="text-xs text-muted mt-1.5">الصق إيموجي واحداً من لوحة المفاتيح (Win + .)</p>
    </div>

    {{-- الوصف --}}
    <div>
        <label class="block text-sm font-semibold text-ink mb-1.5">وصف (اختياري)</label>
        <div class="relative">
            <span class="absolute top-2.5 right-3 pointer-events-none text-muted">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h12M4 18h8"/>
                </svg>
            </span>
            <textarea name="description" rows="2" placeholder="ملاحظات عن هذا الصندوق..."
                      class="dash-field pr-9 py-2.5 resize-none">{{ $oldDesc }}</textarea>
        </div>
    </div>

    @if($isEdit)
    {{-- الحالة (تعديل فقط) --}}
    <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-xl border border-subtle">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" id="is_active"
               {{ $oldActive ? 'checked' : '' }}
               class="w-4 h-4 text-brand rounded border-subtle focus:ring-accent/40">
        <div>
            <label for="is_active" class="text-sm font-semibold text-ink cursor-pointer">صندوق نشط</label>
            <p class="text-xs text-muted">إلغاء التفعيل يخفي الصندوق من القوائم الافتراضية</p>
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
            {{ $isEdit ? 'حفظ التعديلات' : 'إنشاء الصندوق' }}
        </button>
        <a href="{{ route('wallets.index') }}"
           class="flex-1 inline-flex items-center justify-center gap-2 py-2.5 bg-slate-100 text-slate-700 rounded-btn text-sm font-medium hover:bg-slate-200 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            إلغاء
        </a>
    </div>

</div>

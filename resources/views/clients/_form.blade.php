<div class="dash-card p-6 sm:p-7 space-y-5">

    {{-- الاسم --}}
    <div>
        <label class="block text-sm font-semibold text-ink mb-1.5">
            الاسم <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-muted">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </span>
            <input type="text" name="name"
                   value="{{ old('name', $client->name ?? '') }}"
                   placeholder="اسم العميل..."
                   class="dash-field pr-9 py-2.5 @error('name') dash-field-error @enderror">
        </div>
        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- الشركة --}}
    <div>
        <label class="block text-sm font-semibold text-ink mb-1.5">
            الشركة / المؤسسة
            <span class="text-muted font-normal text-xs">(اختياري)</span>
        </label>
        <div class="relative">
            <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-muted">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </span>
            <input type="text" name="company"
                   value="{{ old('company', $client->company ?? '') }}"
                   placeholder="اسم الشركة..."
                   class="dash-field pr-9 py-2.5">
        </div>
    </div>

    {{-- الهاتف + البريد --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">
                رقم الهاتف
                <span class="text-muted font-normal text-xs">(اختياري)</span>
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-muted">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                </span>
                <input type="tel" name="phone"
                       value="{{ old('phone', $client->phone ?? '') }}"
                       placeholder="+970 ..."
                       class="dash-field pr-9 py-2.5 nums">
            </div>
        </div>
        <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">
                البريد الإلكتروني
                <span class="text-muted font-normal text-xs">(اختياري)</span>
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-muted">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </span>
                <input type="email" name="email"
                       value="{{ old('email', $client->email ?? '') }}"
                       placeholder="email@example.com"
                       class="dash-field pr-9 py-2.5">
            </div>
        </div>
    </div>

    {{-- ملاحظات --}}
    <div>
        <label class="block text-sm font-semibold text-ink mb-1.5">
            ملاحظات
            <span class="text-muted font-normal text-xs">(اختياري)</span>
        </label>
        <div class="relative">
            <span class="absolute top-3 right-3 pointer-events-none text-muted">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </span>
            <textarea name="notes" rows="3"
                      placeholder="ملاحظات عن العميل..."
                      class="dash-field pr-9 py-2.5 resize-none">{{ old('notes', $client->notes ?? '') }}</textarea>
        </div>
    </div>

    {{-- حالة العميل (تعديل فقط) --}}
    @isset($client)
    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-subtle">
        <div>
            <p class="text-sm font-semibold text-ink">حالة العميل</p>
            <p class="text-xs text-muted mt-0.5">العملاء غير النشطين لا يظهرون في قوائم المشاريع</p>
        </div>
        <label class="relative inline-flex items-center cursor-pointer">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1"
                   {{ old('is_active', $client->is_active) ? 'checked' : '' }}
                   class="sr-only peer">
            <div class="w-11 h-6 bg-slate-200 peer-focus:ring-2 peer-focus:ring-accent/40
                        rounded-full peer peer-checked:bg-brand transition-colors"></div>
            <div class="absolute right-0.5 top-0.5 bg-white w-5 h-5 rounded-full shadow
                        transition-transform peer-checked:translate-x-[-20px]"></div>
        </label>
    </div>
    @endisset

    {{-- أزرار --}}
    <div class="flex items-center gap-3 pt-2 border-t border-subtle">
        <button type="submit"
                class="flex-1 inline-flex items-center justify-center gap-2 py-2.5 bg-brand hover:bg-brand-600
                       text-white text-sm font-semibold rounded-btn transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            {{ isset($client) ? 'حفظ التعديلات' : 'إضافة العميل' }}
        </button>
        <a href="{{ route('clients.index') }}"
           class="flex-1 inline-flex items-center justify-center gap-2 py-2.5 bg-slate-100 text-slate-700
                  rounded-btn text-sm font-medium hover:bg-slate-200 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            إلغاء
        </a>
    </div>

</div>

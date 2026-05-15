<div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-5">

    {{-- Name --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">
            الاسم <span class="text-red-500">*</span>
        </label>
        <input type="text" name="name"
               value="{{ old('name', $client->name ?? '') }}"
               placeholder="اسم العميل..."
               class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm
                      focus:outline-none focus:ring-2 focus:ring-indigo-500
                      @error('name') border-red-300 bg-red-50 @enderror">
        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Company --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">
            الشركة / المؤسسة <span class="text-gray-400 font-normal">(اختياري)</span>
        </label>
        <input type="text" name="company"
               value="{{ old('company', $client->company ?? '') }}"
               placeholder="اسم الشركة..."
               class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm
                      focus:outline-none focus:ring-2 focus:ring-indigo-500">
    </div>

    {{-- Phone + Email --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">رقم الهاتف</label>
            <input type="tel" name="phone"
                   value="{{ old('phone', $client->phone ?? '') }}"
                   placeholder="+970 ..."
                   class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">البريد الإلكتروني</label>
            <input type="email" name="email"
                   value="{{ old('email', $client->email ?? '') }}"
                   placeholder="email@example.com"
                   class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
    </div>

    {{-- Notes --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">
            ملاحظات <span class="text-gray-400 font-normal">(اختياري)</span>
        </label>
        <textarea name="notes" rows="3"
                  placeholder="ملاحظات عن العميل..."
                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm resize-none
                         focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes', $client->notes ?? '') }}</textarea>
    </div>

    {{-- Active (edit only) --}}
    @isset($client)
    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
        <div>
            <p class="text-sm font-medium text-gray-900">حالة العميل</p>
            <p class="text-xs text-gray-400 mt-0.5">العملاء غير النشطين لا يظهرون في قوائم المشاريع</p>
        </div>
        <label class="relative inline-flex items-center cursor-pointer">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1"
                   {{ old('is_active', $client->is_active) ? 'checked' : '' }}
                   class="sr-only peer">
            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-indigo-500
                        rounded-full peer peer-checked:bg-indigo-600 transition-colors"></div>
            <div class="absolute right-0.5 top-0.5 bg-white w-5 h-5 rounded-full shadow
                        transition-transform peer-checked:translate-x-[-20px]"></div>
        </label>
    </div>
    @endisset

    {{-- Actions --}}
    <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
        <a href="{{ route('clients.index') }}"
           class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition">
            إلغاء
        </a>
        <button type="submit"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700
                       text-white text-sm font-medium rounded-xl transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ isset($client) ? 'حفظ التعديلات' : 'إضافة العميل' }}
        </button>
    </div>

</div>

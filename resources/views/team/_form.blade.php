<div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-5"
     x-data="{ selectedType: '{{ old('type', $teamMember->type ?? 'freelancer') }}' }">

    {{-- Name --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">
            الاسم <span class="text-red-500">*</span>
        </label>
        <input type="text" name="name"
               value="{{ old('name', $teamMember->name ?? '') }}"
               placeholder="اسم العضو..."
               class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm
                      focus:outline-none focus:ring-2 focus:ring-indigo-500
                      @error('name') border-red-300 bg-red-50 @enderror">
        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Type --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
            النوع <span class="text-red-500">*</span>
        </label>
        <div class="grid grid-cols-2 gap-3">
            <label class="relative cursor-pointer" @click="selectedType = 'employee'">
                <input type="radio" name="type" value="employee"
                       {{ old('type', $teamMember->type ?? 'freelancer') === 'employee' ? 'checked' : '' }}
                       class="sr-only">
                <div class="flex items-center gap-3 p-4 rounded-xl border-2 transition"
                     :class="selectedType === 'employee'
                         ? 'border-blue-500 bg-blue-50'
                         : 'border-gray-200 hover:border-gray-300'">
                    <svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-gray-900">موظف</p>
                        <p class="text-xs text-gray-400">عمل دائم</p>
                    </div>
                </div>
            </label>
            <label class="relative cursor-pointer" @click="selectedType = 'freelancer'">
                <input type="radio" name="type" value="freelancer"
                       {{ old('type', $teamMember->type ?? 'freelancer') === 'freelancer' ? 'checked' : '' }}
                       class="sr-only">
                <div class="flex items-center gap-3 p-4 rounded-xl border-2 transition"
                     :class="selectedType === 'freelancer'
                         ? 'border-purple-500 bg-purple-50'
                         : 'border-gray-200 hover:border-gray-300'">
                    <svg class="w-5 h-5 text-purple-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-gray-900">فريلانسر</p>
                        <p class="text-xs text-gray-400">عمل حر</p>
                    </div>
                </div>
            </label>
        </div>
        @error('type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Specialty --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">
            التخصص <span class="text-gray-400 font-normal">(اختياري)</span>
        </label>
        <input type="text" name="specialty"
               value="{{ old('specialty', $teamMember->specialty ?? '') }}"
               placeholder="مثال: مصمم، مطور، مصوّر..."
               class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm
                      focus:outline-none focus:ring-2 focus:ring-indigo-500">
        @error('specialty') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Phone + Email --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">رقم الهاتف</label>
            <input type="tel" name="phone"
                   value="{{ old('phone', $teamMember->phone ?? '') }}"
                   placeholder="+970 ..."
                   class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-500">
            @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">البريد الإلكتروني</label>
            <input type="email" name="email"
                   value="{{ old('email', $teamMember->email ?? '') }}"
                   placeholder="email@example.com"
                   class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-500">
            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Default Rate --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">
            السعر الافتراضي للمشروع <span class="text-gray-400 font-normal">(اختياري)</span>
        </label>
        <input type="number" name="default_rate" min="0" step="0.01"
               value="{{ old('default_rate', $teamMember->default_rate ?? '') }}"
               placeholder="0.00"
               class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm
                      focus:outline-none focus:ring-2 focus:ring-indigo-500">
        @error('default_rate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Notes --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">
            ملاحظات <span class="text-gray-400 font-normal">(اختياري)</span>
        </label>
        <textarea name="notes" rows="3"
                  placeholder="ملاحظات عن العضو..."
                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm resize-none
                         focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes', $teamMember->notes ?? '') }}</textarea>
        @error('notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Active toggle (edit only) --}}
    @isset($teamMember)
    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
        <div>
            <p class="text-sm font-medium text-gray-900">حالة العضو</p>
            <p class="text-xs text-gray-400 mt-0.5">الأعضاء غير النشطين لا يظهرون في قوائم تعيين المشاريع</p>
        </div>
        <label class="relative inline-flex items-center cursor-pointer">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1"
                   {{ old('is_active', $teamMember->is_active) ? 'checked' : '' }}
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
        <a href="{{ route('team.index') }}"
           class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition">
            إلغاء
        </a>
        <button type="submit"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700
                       text-white text-sm font-medium rounded-xl transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ isset($teamMember) ? 'حفظ التعديلات' : 'إضافة العضو' }}
        </button>
    </div>

</div>

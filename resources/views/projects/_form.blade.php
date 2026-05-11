{{-- Shared form partial for create & edit --}}
<div class="bg-white rounded-2xl border border-gray-100 p-6"
     x-data="{
         selectedColor: '{{ old('color', $project->color ?? '#6366F1') }}',
         selectedType: '{{ old('type', $project->type->value ?? 'business') }}'
     }">

    <div class="space-y-5">

        {{-- Project Name --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                اسم المشروع <span class="text-red-500">*</span>
            </label>
            <input type="text"
                   name="name"
                   value="{{ old('name', $project->name ?? '') }}"
                   placeholder="مثال: متجر إلكتروني، تطوير تطبيق..."
                   class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                          @error('name') border-red-300 bg-red-50 @enderror">
            @error('name')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Project Type --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                نوع المشروع <span class="text-red-500">*</span>
            </label>
            <div class="grid grid-cols-2 gap-3">
                <label class="relative cursor-pointer"
                       @click="selectedType = 'business'">
                    <input type="radio" name="type" value="business"
                           {{ old('type', $project->type->value ?? 'business') === 'business' ? 'checked' : '' }}
                           class="sr-only">
                    <div class="flex items-center gap-3 p-4 rounded-xl border-2 transition"
                         :class="selectedType === 'business'
                             ? 'border-indigo-500 bg-indigo-50'
                             : 'border-gray-200 hover:border-gray-300'">
                        <span class="text-2xl">💼</span>
                        <div>
                            <p class="text-sm font-medium text-gray-900">تجاري</p>
                            <p class="text-xs text-gray-400">للأعمال والمشاريع التجارية</p>
                        </div>
                    </div>
                </label>
                <label class="relative cursor-pointer"
                       @click="selectedType = 'personal'">
                    <input type="radio" name="type" value="personal"
                           {{ old('type', $project->type->value ?? '') === 'personal' ? 'checked' : '' }}
                           class="sr-only">
                    <div class="flex items-center gap-3 p-4 rounded-xl border-2 transition"
                         :class="selectedType === 'personal'
                             ? 'border-indigo-500 bg-indigo-50'
                             : 'border-gray-200 hover:border-gray-300'">
                        <span class="text-2xl">🏠</span>
                        <div>
                            <p class="text-sm font-medium text-gray-900">شخصي</p>
                            <p class="text-xs text-gray-400">للمصاريف الشخصية والعائلية</p>
                        </div>
                    </div>
                </label>
            </div>
            @error('type')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Currency + Color Row --}}
        <div class="grid grid-cols-2 gap-4">
            {{-- Currency --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    العملة <span class="text-red-500">*</span>
                </label>
                <select name="currency"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm bg-white
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                               @error('currency') border-red-300 bg-red-50 @enderror">
                    @foreach($currencies as $currency)
                        <option value="{{ $currency }}"
                                {{ old('currency', $project->currency ?? auth()->user()->currency) === $currency ? 'selected' : '' }}>
                            {{ $currency }}
                        </option>
                    @endforeach
                </select>
                @error('currency')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Color Picker --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    لون المشروع <span class="text-red-500">*</span>
                </label>
                <input type="hidden" name="color" :value="selectedColor">
                <div class="flex items-center gap-2 flex-wrap">
                    @foreach($colors as $color)
                    <button type="button"
                            @click="selectedColor = '{{ $color }}'"
                            class="w-7 h-7 rounded-lg transition-all duration-150 border-2"
                            :class="selectedColor === '{{ $color }}'
                                ? 'scale-110 border-gray-800'
                                : 'border-transparent hover:scale-105'"
                            style="background-color: {{ $color }}">
                    </button>
                    @endforeach
                    {{-- Custom color input --}}
                    <div class="relative">
                        <input type="color"
                               :value="selectedColor"
                               @input="selectedColor = $event.target.value"
                               class="w-7 h-7 rounded-lg cursor-pointer border border-gray-200"
                               title="اختر لوناً مخصصاً">
                    </div>
                </div>
                {{-- Preview --}}
                <div class="mt-2 flex items-center gap-2">
                    <div class="w-4 h-4 rounded-full" :style="`background-color: ${selectedColor}`"></div>
                    <span class="text-xs text-gray-400" x-text="selectedColor"></span>
                </div>
                @error('color')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Description --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                وصف المشروع <span class="text-gray-400 font-normal">(اختياري)</span>
            </label>
            <textarea name="description"
                      rows="3"
                      placeholder="وصف مختصر للمشروع وأهدافه..."
                      class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm resize-none
                             focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                             @error('description') border-red-300 bg-red-50 @enderror">{{ old('description', $project->description ?? '') }}</textarea>
            @error('description')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Active toggle (only in edit) --}}
        @isset($project)
        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
            <div>
                <p class="text-sm font-medium text-gray-900">حالة المشروع</p>
                <p class="text-xs text-gray-400 mt-0.5">المشاريع المتوقفة لا تظهر في إحصاءات لوحة التحكم</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1"
                       {{ old('is_active', $project->is_active) ? 'checked' : '' }}
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
            <a href="{{ route('projects.index') }}"
               class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition">
                إلغاء
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700
                           text-white text-sm font-medium rounded-xl transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ isset($project) ? 'حفظ التعديلات' : 'إنشاء المشروع' }}
            </button>
        </div>

    </div>
</div>

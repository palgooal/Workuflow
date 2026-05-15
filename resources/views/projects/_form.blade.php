{{-- Shared form partial for create & edit --}}
<div class="bg-white rounded-2xl border border-gray-100 p-6"
     x-data="{
         selectedColor: '{{ old('color', $project->color ?? '#6366F1') }}',
         selectedType: '{{ old('type', $project->type->value ?? 'business') }}',
         services: {{ json_encode(
             old('services', isset($project)
                 ? $project->services->map(fn($s) => [
                     'service_id' => $s->id,
                     'amount'     => $s->pivot->amount,
                     'type'       => $s->pivot->type,
                     'notes'      => $s->pivot->notes ?? '',
                 ])->toArray()
                 : []
             )
         ) }},
         addService() {
             this.services.push({ service_id: '', amount: '', type: 'income', notes: '' });
         },
         removeService(index) {
             this.services.splice(index, 1);
         }
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

        {{-- Client --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                العميل <span class="text-gray-400 font-normal">(اختياري)</span>
            </label>
            @if($clients->isEmpty())
                <div class="flex items-center gap-2 p-3 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-700">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    لا يوجد عملاء بعد.
                    <a href="{{ route('clients.create') }}" class="font-medium underline">أضف عميلاً الآن</a>
                </div>
            @else
                <select name="client_id"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm bg-white
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">— بدون عميل —</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}"
                                {{ old('client_id', $project->client_id ?? '') == $client->id ? 'selected' : '' }}>
                            {{ $client->name }}{{ $client->company ? ' — ' . $client->company : '' }}
                        </option>
                    @endforeach
                </select>
                @error('client_id')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            @endif
        </div>

        {{-- Services --}}
        <div>
            <div class="flex items-center justify-between mb-3">
                <label class="block text-sm font-medium text-gray-700">
                    الخدمات المقدمة <span class="text-gray-400 font-normal">(اختياري)</span>
                </label>
                <button type="button" @click="addService()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium
                               text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    إضافة خدمة
                </button>
            </div>

            {{-- Services List --}}
            <div class="space-y-3" x-show="services.length > 0">
                <template x-for="(svc, index) in services" :key="index">
                    <div class="grid grid-cols-12 gap-2 items-start p-3 bg-gray-50 rounded-xl border border-gray-200">

                        {{-- Service Dropdown --}}
                        <div class="col-span-5">
                            <label class="block text-xs text-gray-500 mb-1">الخدمة</label>
                            <select :name="`services[${index}][service_id]`"
                                    x-model="svc.service_id"
                                    class="w-full px-2.5 py-2 rounded-lg border border-gray-200 text-sm bg-white
                                           focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">اختر خدمة...</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}">
                                        {{ $service->name_ar ?? $service->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Amount --}}
                        <div class="col-span-3">
                            <label class="block text-xs text-gray-500 mb-1">القيمة</label>
                            <input type="number"
                                   :name="`services[${index}][amount]`"
                                   x-model="svc.amount"
                                   min="0"
                                   step="0.01"
                                   placeholder="0.00"
                                   class="w-full px-2.5 py-2 rounded-lg border border-gray-200 text-sm
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        {{-- Type --}}
                        <div class="col-span-3">
                            <label class="block text-xs text-gray-500 mb-1">النوع</label>
                            <select :name="`services[${index}][type]`"
                                    x-model="svc.type"
                                    class="w-full px-2.5 py-2 rounded-lg border border-gray-200 text-sm bg-white
                                           focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="income">💚 دخل</option>
                                <option value="expense">🔴 مصروف</option>
                            </select>
                        </div>

                        {{-- Remove Button --}}
                        <div class="col-span-1 flex items-end pb-0.5">
                            <button type="button"
                                    @click="removeService(index)"
                                    class="w-8 h-8 flex items-center justify-center text-red-400
                                           hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Notes (full width) --}}
                        <div class="col-span-12 mt-1">
                            <input type="text"
                                   :name="`services[${index}][notes]`"
                                   x-model="svc.notes"
                                   placeholder="ملاحظات (اختياري)..."
                                   class="w-full px-2.5 py-1.5 rounded-lg border border-gray-200 text-xs text-gray-600
                                          focus:outline-none focus:ring-1 focus:ring-indigo-400">
                        </div>

                    </div>
                </template>
            </div>

            {{-- Empty state --}}
            <div x-show="services.length === 0"
                 class="flex flex-col items-center gap-2 p-5 border-2 border-dashed border-gray-200 rounded-xl text-center">
                <span class="text-2xl">💼</span>
                <p class="text-sm text-gray-400">لم تُضَف خدمات بعد</p>
                <button type="button" @click="addService()"
                        class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                    + أضف أول خدمة
                </button>
            </div>
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

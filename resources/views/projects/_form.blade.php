{{-- Shared form partial for create & edit --}}
<div class="bg-white rounded-2xl border border-gray-100 p-6"
     x-data="{
         selectedColor: '{{ old('color', $project->color ?? '#6366F1') }}',
         selectedType: '{{ old('type', $project->type->value ?? 'business') }}',
         allServiceOptions: {{ $services->map(fn($s) => ['id' => $s->id, 'name_ar' => $s->name_ar ?? $s->name])->toJson() }},
         teamMembers: {{ $teamMembers->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->toJson() }},
         services: {{ json_encode(
             old('services', isset($project)
                 ? $project->services->map(fn($s) => [
                     'service_id' => (string) $s->id,
                     'amount'     => $s->pivot->amount,
                     'type'       => 'income',
                     'notes'      => $s->pivot->notes ?? '',
                     'members'    => \App\Models\ProjectServiceMember::where('project_service_id', $s->pivot->id)
                                         ->get()
                                         ->map(fn($m) => [
                                             'team_member_id' => (string) $m->team_member_id,
                                             'team_cost'      => $m->team_cost ?? '',
                                         ])->toArray(),
                 ])->toArray()
                 : []
             )
         ) }},
         addService() {
             this.services.push({ service_id: '', amount: '', type: 'income', notes: '', members: [] });
         },
         removeService(index) {
             this.services.splice(index, 1);
         },
         addMember(svcIndex) {
             this.services[svcIndex].members.push({ team_member_id: '', team_cost: '' });
         },
         removeMember(svcIndex, memberIndex) {
             this.services[svcIndex].members.splice(memberIndex, 1);
         },
         serviceMargin(svc) {
             const revenue = parseFloat(svc.amount) || 0;
             const cost    = svc.members.reduce((sum, m) => sum + (parseFloat(m.team_cost) || 0), 0);
             const margin  = revenue - cost;
             const pct     = revenue > 0 ? Math.round((margin / revenue) * 100 * 10) / 10 : null;
             return { revenue, cost, margin, pct };
         },
         marginColor(pct) {
             if (pct === null || pct >= 40) return 'bg-emerald-50 text-emerald-700 border-emerald-200';
             if (pct >= 20) return 'bg-amber-50 text-amber-700 border-amber-200';
             if (pct >= 0)  return 'bg-orange-50 text-orange-700 border-orange-200';
             return 'bg-red-50 text-red-700 border-red-200';
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

        {{-- Contract Value + Expense Budget --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    قيمة العقد
                    <span class="text-gray-400 font-normal">(المبلغ المتفق عليه)</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <input type="number" name="contract_value" step="0.01" min="0"
                           value="{{ old('contract_value', $project->contract_value ?? '') }}"
                           placeholder="0.00"
                           class="w-full pr-9 pl-3.5 py-2.5 rounded-xl border border-gray-200 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500
                                  @error('contract_value') border-red-300 @enderror">
                </div>
                @error('contract_value') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

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
        <div x-data="{
                quickAddOpen: false,
                quickName: '',
                quickLoading: false,
                quickError: '',
                async submitQuick() {
                    if (! this.quickName.trim()) return;
                    this.quickLoading = true;
                    this.quickError   = '';
                    try {
                        const res = await fetch('{{ route('services.quick-store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ name_ar: this.quickName }),
                        });
                        if (! res.ok) throw new Error('فشل الحفظ');
                        const svc = await res.json();
                        allServiceOptions.push({ id: svc.id, name_ar: svc.name_ar });
                        services.push({ service_id: String(svc.id), amount: '', type: 'income', notes: '', team_member_id: '', team_cost: '' });
                        this.quickName   = '';
                        this.quickAddOpen = false;
                    } catch (e) {
                        this.quickError = 'حدث خطأ، حاول مرة أخرى.';
                    } finally {
                        this.quickLoading = false;
                    }
                }
             }">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-3">
                <label class="block text-sm font-medium text-gray-700">
                    الخدمات المقدمة
                    <span class="text-gray-400 font-normal text-xs">(اختياري)</span>
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
            <div class="space-y-2" x-show="services.length > 0">
                <template x-for="(svc, index) in services" :key="index">
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 space-y-3">

                        {{-- Row 1: Service + Remove --}}
                        <div class="flex items-center gap-3">
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">الخدمة</label>
                                <select :name="`services[${index}][service_id]`"
                                        x-model="svc.service_id"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm bg-white
                                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <option value="">— اختر خدمة —</option>
                                    <template x-for="opt in allServiceOptions" :key="opt.id">
                                        <option :value="String(opt.id)" x-text="opt.name_ar"
                                                :selected="String(opt.id) === String(svc.service_id)"></option>
                                    </template>
                                </select>
                            </div>
                            <button type="button"
                                    @click="removeService(index)"
                                    class="mt-5 w-8 h-8 flex-shrink-0 flex items-center justify-center
                                           text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Row 2: Amount --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">القيمة</label>
                            <input type="number"
                                   :name="`services[${index}][amount]`"
                                   x-model="svc.amount"
                                   min="0" step="0.01"
                                   placeholder="0.00"
                                   class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <input type="hidden" :name="`services[${index}][type]`" value="income">
                        </div>

                        {{-- Row 3: Notes --}}
                        <div>
                            <input type="text"
                                   :name="`services[${index}][notes]`"
                                   x-model="svc.notes"
                                   placeholder="ملاحظات (اختياري)..."
                                   class="w-full px-3 py-2 rounded-lg border border-gray-200 text-xs text-gray-600
                                          focus:outline-none focus:ring-1 focus:ring-indigo-400 bg-white">
                        </div>

                        {{-- منفذو الخدمة --}}
                        <div class="pt-2 border-t border-gray-100 mt-1 space-y-2">

                            {{-- عنوان القسم + زر الإضافة --}}
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium text-gray-500">منفذو الخدمة</span>
                                <button type="button" @click="addMember(index)"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium
                                               text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    إضافة منفذ
                                </button>
                            </div>

                            {{-- قائمة المنفذين --}}
                            <template x-for="(member, memberIndex) in svc.members" :key="memberIndex">
                                <div class="flex items-center gap-2 bg-white border border-gray-100 rounded-lg p-2.5">

                                    {{-- اسم المنفذ --}}
                                    <select :name="`services[${index}][members][${memberIndex}][team_member_id]`"
                                            x-model="member.team_member_id"
                                            class="flex-1 px-2.5 py-1.5 rounded-lg border border-gray-200 text-sm bg-white
                                                   focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <option value="">— اختر منفذاً —</option>
                                        <template x-for="tm in teamMembers" :key="tm.id">
                                            <option :value="String(tm.id)" x-text="tm.name"
                                                    :selected="String(tm.id) === String(member.team_member_id)"></option>
                                        </template>
                                    </select>

                                    {{-- تكلفة المنفذ --}}
                                    <div class="relative w-28 flex-shrink-0">
                                        <input type="number"
                                               :name="`services[${index}][members][${memberIndex}][team_cost]`"
                                               x-model="member.team_cost"
                                               min="0" step="0.01" placeholder="0.00"
                                               class="w-full px-2.5 py-1.5 rounded-lg border border-gray-200 text-sm
                                                      focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    </div>

                                    {{-- حذف المنفذ --}}
                                    <button type="button" @click="removeMember(index, memberIndex)"
                                            class="w-6 h-6 flex-shrink-0 flex items-center justify-center
                                                   text-gray-300 hover:text-red-500 rounded transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>

                            {{-- رسالة إذا لا يوجد منفذون --}}
                            <p x-show="svc.members.length === 0"
                               class="text-xs text-gray-400 text-center py-1">
                                لا يوجد منفذون — الخدمة بدون تكاليف تنفيذ
                            </p>

                            {{-- مؤشر الهامش الحي --}}
                            <template x-if="parseFloat(svc.amount) > 0 || svc.members.length > 0">
                                <div class="rounded-lg border px-3 py-2 flex items-center justify-between text-xs font-bold transition-colors"
                                     :class="marginColor(serviceMargin(svc).pct)">
                                    <div class="flex items-center gap-3">
                                        <span>الهامش:</span>
                                        <span x-text="serviceMargin(svc).margin.toLocaleString('ar-SA', {minimumFractionDigits: 0, maximumFractionDigits: 2})"></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <template x-if="serviceMargin(svc).pct !== null">
                                            <span x-text="`${serviceMargin(svc).pct}%`"></span>
                                        </template>
                                        {{-- أيقونة الحالة --}}
                                        <template x-if="serviceMargin(svc).pct !== null && serviceMargin(svc).pct >= 40">
                                            <span title="هامش ممتاز">✓</span>
                                        </template>
                                        <template x-if="serviceMargin(svc).pct !== null && serviceMargin(svc).pct >= 20 && serviceMargin(svc).pct < 40">
                                            <span title="هامش مقبول">!</span>
                                        </template>
                                        <template x-if="serviceMargin(svc).pct !== null && serviceMargin(svc).pct >= 0 && serviceMargin(svc).pct < 20">
                                            <span title="هامش منخفض — راجع التكاليف">⚠</span>
                                        </template>
                                        <template x-if="serviceMargin(svc).margin < 0">
                                            <span title="خسارة">✕</span>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                    </div>
                </template>
            </div>

            {{-- Empty State --}}
            <div x-show="services.length === 0"
                 class="flex flex-col items-center gap-2 py-8 border-2 border-dashed border-gray-200 rounded-xl text-center">
                <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-sm text-gray-400">لا توجد خدمات مضافة بعد</p>
                <button type="button" @click="addService()"
                        class="text-xs font-medium text-indigo-600 hover:text-indigo-800">
                    + أضف خدمة للمشروع
                </button>
            </div>

            {{-- Quick Add Service --}}
            <div class="mt-3">
                <button type="button"
                        @click="quickAddOpen = !quickAddOpen"
                        class="inline-flex items-center gap-1.5 text-xs text-gray-400 hover:text-indigo-600 transition">
                    <svg class="w-3.5 h-3.5 transition-transform" :class="quickAddOpen ? 'rotate-45' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span x-text="quickAddOpen ? 'إلغاء' : 'خدمتك غير موجودة؟ أضفها هنا'"></span>
                </button>

                <div x-show="quickAddOpen" x-transition
                     class="mt-3 p-4 bg-indigo-50 border border-indigo-200 rounded-xl">
                    <p class="text-xs font-medium text-indigo-800 mb-2.5">
                        إضافة خدمة مخصصة
                        <span class="text-indigo-400 font-normal">(ستُحفظ في قائمة خدماتك)</span>
                    </p>
                    <div class="flex gap-2">
                        <input type="text"
                               x-model="quickName"
                               @keydown.enter.prevent="submitQuick()"
                               placeholder="مثال: تصوير منتجات، تدريب، استشارة..."
                               class="flex-1 px-3 py-2 rounded-lg border border-indigo-200 text-sm bg-white
                                      focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <button type="button"
                                @click="submitQuick()"
                                :disabled="quickLoading || !quickName.trim()"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium
                                       rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!quickLoading">إضافة</span>
                            <span x-show="quickLoading">...</span>
                        </button>
                    </div>
                    <p x-show="quickError" x-text="quickError"
                       class="mt-1.5 text-xs text-red-600"></p>
                </div>
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

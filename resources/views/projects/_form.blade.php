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
                     'members'           => \App\Models\ProjectServiceMember::where('project_service_id', $s->pivot->id)
                                               ->get()
                                               ->map(fn($m) => [
                                                   'team_member_id' => (string) $m->team_member_id,
                                                   'team_cost'      => $m->team_cost ?? '',
                                               ])->toArray(),
                     'target_margin_pct' => $s->pivot->target_margin_pct ?? '',
                 ])->toArray()
                 : []
             )
         ) }},
         historyRoute: '{{ route('projects.service-margin-history', '__ID__') }}',
         addService() {
             this.services.push({ service_id: '', amount: '', type: 'income', notes: '', members: [], history: null, historyLoading: false, target_margin_pct: '' });
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
         },
         costPct(svc) {
             const m = this.serviceMargin(svc);
             return m.revenue > 0 ? Math.round((m.cost / m.revenue) * 100) : null;
         },
         targetMarginPct: {{ $targetMarginPct ?? 40 }},
         effectiveMarginPct(svc) {
             // الأولوية: هامش الخدمة المخصص → إعداد المستخدم العام
             const custom = parseInt(svc.target_margin_pct);
             return (custom >= 1 && custom <= 99) ? custom : this.targetMarginPct;
         },
         suggestedPrice(svc) {
             const cost = svc.members.reduce((sum, m) => sum + (parseFloat(m.team_cost) || 0), 0);
             if (cost <= 0) return null;
             const ratio = (100 - this.effectiveMarginPct(svc)) / 100;
             return ratio > 0 ? Math.ceil(cost / ratio) : null;
         },
         async fetchServiceHistory(svc) {
             if (! svc.service_id) { svc.history = null; return; }
             svc.historyLoading = true;
             try {
                 const url = this.historyRoute.replace('__ID__', svc.service_id);
                 const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                 svc.history = await res.json();
             } catch (e) {
                 svc.history = null;
             } finally {
                 svc.historyLoading = false;
             }
         },
         projectMarginSummary() {
             let totalRevenue = 0, totalCost = 0;
             this.services.forEach(svc => {
                 const m = this.serviceMargin(svc);
                 totalRevenue += m.revenue;
                 totalCost    += m.cost;
             });
             const totalMargin = totalRevenue - totalCost;
             const pct = totalRevenue > 0 ? Math.round((totalMargin / totalRevenue) * 100 * 10) / 10 : null;
             return { totalRevenue, totalCost, totalMargin, pct, isLoss: totalMargin < 0 };
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
                                        @change="fetchServiceHistory(svc)"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm bg-white
                                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <option value="">— اختر خدمة —</option>
                                    <template x-for="opt in allServiceOptions" :key="opt.id">
                                        <option :value="String(opt.id)" x-text="opt.name_ar"
                                                :selected="String(opt.id) === String(svc.service_id)"></option>
                                    </template>
                                </select>

                                {{-- التنبيه التاريخي --}}
                                <template x-if="svc.history && svc.history.has_history">
                                    <div class="mt-1.5 flex items-center gap-2 text-xs px-2.5 py-1.5 rounded-lg"
                                         :class="{
                                             'bg-emerald-50 text-emerald-700': svc.history.avg_margin >= 40,
                                             'bg-amber-50 text-amber-700':    svc.history.avg_margin >= 20 && svc.history.avg_margin < 40,
                                             'bg-orange-50 text-orange-700':  svc.history.avg_margin >= 0  && svc.history.avg_margin < 20,
                                             'bg-red-50 text-red-700':        svc.history.avg_margin < 0
                                         }">
                                        <span>📊</span>
                                        <span>
                                            متوسط هامشك لهذه الخدمة:
                                            <strong x-text="`${svc.history.avg_margin}%`"></strong>
                                            (<span x-text="svc.history.label"></span>)
                                            في
                                            <span x-text="svc.history.times_used"></span>
                                            مشروع سابق
                                        </span>
                                    </div>
                                </template>
                                <template x-if="svc.historyLoading">
                                    <p class="mt-1 text-xs text-gray-400">جاري التحقق من السجل...</p>
                                </template>
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
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-xs font-medium text-gray-500">القيمة</label>
                                {{-- اقتراح السعر —يظهر فقط عند وجود تكاليف وغياب قيمة --}}
                                <template x-if="suggestedPrice(svc) !== null && (! svc.amount || parseFloat(svc.amount) === 0)">
                                    <button type="button"
                                            @click="svc.amount = suggestedPrice(svc)"
                                            class="text-xs text-indigo-600 hover:text-indigo-800 font-medium transition">
                                        💡 اقتراح:
                                        <span x-text="suggestedPrice(svc).toLocaleString('ar-SA')"></span>
                                        (<span x-text="effectiveMarginPct(svc)"></span>% هامش)
                                    </button>
                                </template>
                            </div>
                            <input type="number"
                                   :name="`services[${index}][amount]`"
                                   x-model="svc.amount"
                                   min="0" step="0.01"
                                   placeholder="0.00"
                                   class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <input type="hidden" :name="`services[${index}][type]`" value="income">
                        </div>

                        {{-- Row 3: Notes + هامش مخصص --}}
                        <div class="grid grid-cols-[1fr_auto] gap-2 items-start">
                            <input type="text"
                                   :name="`services[${index}][notes]`"
                                   x-model="svc.notes"
                                   placeholder="ملاحظات (اختياري)..."
                                   class="w-full px-3 py-2 rounded-lg border border-gray-200 text-xs text-gray-600
                                          focus:outline-none focus:ring-1 focus:ring-indigo-400 bg-white">

                            {{-- هامش مخصص للخدمة --}}
                            <div class="flex-shrink-0" x-data="{ showMargin: svc.target_margin_pct !== '' }">
                                <template x-if="!showMargin">
                                    <button type="button"
                                            @click="showMargin = true"
                                            :title="`الهامش العام: ${targetMarginPct}%`"
                                            class="flex items-center gap-1 px-2.5 py-2 text-xs text-gray-400
                                                   hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition border border-gray-200 border-dashed">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                        </svg>
                                        <span x-text="`${targetMarginPct}%`"></span>
                                    </button>
                                </template>
                                <template x-if="showMargin">
                                    <div class="flex items-center gap-1">
                                        <div class="relative">
                                            <input type="number"
                                                   :name="`services[${index}][target_margin_pct]`"
                                                   x-model="svc.target_margin_pct"
                                                   min="1" max="99" step="1"
                                                   :placeholder="targetMarginPct"
                                                   class="w-16 px-2 py-2 pl-5 rounded-lg border border-indigo-300 text-xs
                                                          text-indigo-700 font-bold bg-indigo-50
                                                          focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                            <span class="absolute left-1.5 top-1/2 -translate-y-1/2 text-xs text-indigo-500 font-bold pointer-events-none">%</span>
                                        </div>
                                        <button type="button"
                                                @click="svc.target_margin_pct = ''; showMargin = false"
                                                class="text-gray-300 hover:text-red-400 transition"
                                                title="إزالة الهامش المخصص">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
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

                            {{-- اقتراح رفع السعر — عند وجود قيمة وهامش أقل من المستهدف --}}
                            <template x-if="
                                suggestedPrice(svc) !== null &&
                                parseFloat(svc.amount) > 0 &&
                                serviceMargin(svc).pct !== null &&
                                serviceMargin(svc).pct < effectiveMarginPct(svc)
                            ">
                                <div class="flex items-center justify-between rounded-lg bg-indigo-50 border border-indigo-200 px-3 py-2 text-xs text-indigo-800">
                                    <span>
                                        💡 لتحقيق هامش <strong x-text="`${effectiveMarginPct(svc)}%`"></strong>،
                                        السعر الموصى به:
                                        <strong x-text="suggestedPrice(svc).toLocaleString('ar-SA')"></strong>
                                    </span>
                                    <button type="button"
                                            @click="svc.amount = suggestedPrice(svc)"
                                            class="mr-2 px-2 py-0.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded font-medium transition text-xs">
                                        تطبيق
                                    </button>
                                </div>
                            </template>

                            {{-- تنبيه: الهامش الحالي أقل بكثير من المتوسط التاريخي --}}
                            <template x-if="
                                svc.history &&
                                svc.history.has_history &&
                                svc.history.avg_margin >= 20 &&
                                serviceMargin(svc).pct !== null &&
                                serviceMargin(svc).pct < (svc.history.avg_margin * 0.5)
                            ">
                                <div class="flex items-start gap-2 rounded-lg bg-purple-50 border border-purple-200 px-3 py-2 text-xs text-purple-800">
                                    <span class="mt-px flex-shrink-0">📉</span>
                                    <span>
                                        الهامش الحالي
                                        (<strong x-text="`${serviceMargin(svc).pct}%`"></strong>)
                                        أقل بكثير من متوسطك التاريخي
                                        (<strong x-text="`${svc.history.avg_margin}%`"></strong>).
                                        هل راجعت التسعير؟
                                    </span>
                                </div>
                            </template>

                            {{-- تنبيه: تكلفة تتجاوز 80% --}}
                            <template x-if="svc.members.length > 0 && serviceMargin(svc).pct !== null && serviceMargin(svc).pct >= 0 && serviceMargin(svc).pct < 20">
                                <div class="flex items-start gap-2 rounded-lg bg-orange-50 border border-orange-200 px-3 py-2 text-xs text-orange-800">
                                    <span class="mt-px flex-shrink-0">⚠️</span>
                                    <span>
                                        تكلفة المنفذين وصلت
                                        <strong x-text="`${costPct(svc)}%`"></strong>
                                        من قيمة الخدمة — الهامش أقل من 20%.
                                        راجع التسعير أو قلّل التكاليف.
                                    </span>
                                </div>
                            </template>

                            {{-- تنبيه: خسارة --}}
                            <template x-if="svc.members.length > 0 && serviceMargin(svc).margin < 0">
                                <div class="flex items-start gap-2 rounded-lg bg-red-50 border border-red-300 px-3 py-2 text-xs text-red-800 font-semibold">
                                    <span class="mt-px flex-shrink-0">🔴</span>
                                    <span>
                                        خسارة — تكلفة المنفذين
                                        (<strong x-text="serviceMargin(svc).cost.toLocaleString('ar-SA', {maximumFractionDigits: 2})"></strong>)
                                        تتجاوز قيمة الخدمة
                                        (<strong x-text="serviceMargin(svc).revenue.toLocaleString('ar-SA', {maximumFractionDigits: 2})"></strong>).
                                    </span>
                                </div>
                            </template>
                        </div>

                    </div>
                </template>
            </div>

            {{-- تنبيه على مستوى المشروع --}}
            <template x-if="services.length > 1 && projectMarginSummary().totalCost > 0">
                <div class="mt-3">
                    {{-- خسارة إجمالية --}}
                    <template x-if="projectMarginSummary().isLoss">
                        <div class="flex items-start gap-2 rounded-xl bg-red-50 border border-red-300 px-4 py-3 text-sm text-red-800">
                            <span class="mt-0.5 flex-shrink-0 text-base">🔴</span>
                            <div>
                                <p class="font-bold">إجمالي تكاليف الفريق يتجاوز إجمالي إيرادات الخدمات</p>
                                <p class="mt-0.5 text-xs font-normal text-red-600">
                                    الإيرادات:
                                    <strong x-text="projectMarginSummary().totalRevenue.toLocaleString('ar-SA', {maximumFractionDigits: 2})"></strong>
                                    · التكاليف:
                                    <strong x-text="projectMarginSummary().totalCost.toLocaleString('ar-SA', {maximumFractionDigits: 2})"></strong>
                                </p>
                            </div>
                        </div>
                    </template>
                    {{-- هامش إجمالي منخفض --}}
                    <template x-if="!projectMarginSummary().isLoss && projectMarginSummary().pct !== null && projectMarginSummary().pct < 20">
                        <div class="flex items-start gap-2 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                            <span class="mt-0.5 flex-shrink-0 text-base">⚠️</span>
                            <div>
                                <p class="font-semibold">
                                    الهامش الإجمالي للمشروع
                                    <span x-text="`${projectMarginSummary().pct}%`" class="font-bold"></span>
                                    — أقل من 20%
                                </p>
                                <p class="mt-0.5 text-xs font-normal text-amber-700">
                                    راجع تسعير الخدمات أو توزيع تكاليف الفريق قبل الحفظ.
                                </p>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

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

        {{-- حالة المشروع --}}
        @php
            $currentStatus = old('status', isset($project) ? $project->status->value : 'active');
            $statuses = [
                ['value' => 'active',    'label' => 'نشط',    'icon' => '🟢', 'desc' => 'يُعمل عليه الآن'],
                ['value' => 'completed', 'label' => 'مكتمل',  'icon' => '✅', 'desc' => 'تم التسليم والإغلاق'],
                ['value' => 'on_hold',   'label' => 'متوقف',  'icon' => '⏸',  'desc' => 'مؤجل مؤقتاً'],
                ['value' => 'cancelled', 'label' => 'ملغي',   'icon' => '❌', 'desc' => 'تم الإلغاء'],
            ];
        @endphp
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">حالة المشروع</label>
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                @foreach($statuses as $s)
                <label class="cursor-pointer">
                    <input type="radio" name="status" value="{{ $s['value'] }}"
                           {{ $currentStatus === $s['value'] ? 'checked' : '' }}
                           class="sr-only peer">
                    <div class="flex flex-col items-center gap-1 px-3 py-3 rounded-xl border-2 text-center
                                transition cursor-pointer text-xs font-medium
                                border-gray-200 text-gray-500
                                peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-700">
                        <span class="text-base">{{ $s['icon'] }}</span>
                        <span class="font-bold">{{ $s['label'] }}</span>
                        <span class="text-gray-400 font-normal text-[10px] leading-tight peer-checked:text-indigo-500">{{ $s['desc'] }}</span>
                    </div>
                </label>
                @endforeach
            </div>
            @error('status')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

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

{{-- Shared form partial for create & edit --}}
<div class="dash-card p-6 sm:p-8"
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

    <div class="space-y-6">

        {{-- ── اسم المشروع ── --}}
        <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">
                اسم المشروع <span class="text-red-500">*</span>
            </label>
            <input type="text"
                   name="name"
                   value="{{ old('name', $project->name ?? '') }}"
                   placeholder="مثال: متجر إلكتروني، تطوير تطبيق..."
                   autofocus
                   class="dash-field px-3.5 py-2.5 @error('name') dash-field-error @enderror">
            @error('name')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- ── نوع المشروع ── --}}
        <div>
            <label class="block text-sm font-semibold text-ink mb-2">
                نوع المشروع <span class="text-red-500">*</span>
            </label>
            <div class="grid grid-cols-2 gap-3">
                <label class="relative cursor-pointer" @click="selectedType = 'business'">
                    <input type="radio" name="type" value="business"
                           {{ old('type', $project->type->value ?? 'business') === 'business' ? 'checked' : '' }}
                           class="sr-only">
                    <div class="flex items-center gap-3 p-4 rounded-xl border-2 transition-all"
                         :class="selectedType === 'business'
                             ? 'border-brand bg-brand-50 shadow-sm'
                             : 'border-subtle hover:border-slate-300 bg-surface'">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0"
                             :class="selectedType === 'business' ? 'bg-brand/10' : 'bg-slate-100'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"
                                 :class="selectedType === 'business' ? 'text-brand' : 'text-slate-400'">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold" :class="selectedType === 'business' ? 'text-brand-700' : 'text-ink'">تجاري</p>
                            <p class="text-xs text-muted">للأعمال والمشاريع التجارية</p>
                        </div>
                    </div>
                </label>
                <label class="relative cursor-pointer" @click="selectedType = 'personal'">
                    <input type="radio" name="type" value="personal"
                           {{ old('type', $project->type->value ?? '') === 'personal' ? 'checked' : '' }}
                           class="sr-only">
                    <div class="flex items-center gap-3 p-4 rounded-xl border-2 transition-all"
                         :class="selectedType === 'personal'
                             ? 'border-brand bg-brand-50 shadow-sm'
                             : 'border-subtle hover:border-slate-300 bg-surface'">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0"
                             :class="selectedType === 'personal' ? 'bg-brand/10' : 'bg-slate-100'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"
                                 :class="selectedType === 'personal' ? 'text-brand' : 'text-slate-400'">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold" :class="selectedType === 'personal' ? 'text-brand-700' : 'text-ink'">شخصي</p>
                            <p class="text-xs text-muted">للمصاريف الشخصية والعائلية</p>
                        </div>
                    </div>
                </label>
            </div>
            @error('type')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- ── العملة + اللون ── --}}
        <div class="grid grid-cols-2 gap-5">
            {{-- العملة --}}
            <div>
                <label class="block text-sm font-semibold text-ink mb-1.5">
                    العملة <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <select name="currency"
                            class="dash-field pr-9 pl-3.5 py-2.5 @error('currency') dash-field-error @enderror">
                        @foreach($currencies as $code => $label)
                            <option value="{{ $code }}"
                                    {{ old('currency', $project->currency ?? auth()->user()->currency) === $code ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('currency')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- لون المشروع --}}
            <div>
                <label class="block text-sm font-semibold text-ink mb-1.5">
                    لون المشروع <span class="text-red-500">*</span>
                </label>
                <input type="hidden" name="color" :value="selectedColor">
                <div class="flex items-center gap-2 flex-wrap">
                    @foreach($colors as $color)
                    <button type="button"
                            @click="selectedColor = '{{ $color }}'"
                            class="w-7 h-7 rounded-lg transition-all duration-150 border-2"
                            :class="selectedColor === '{{ $color }}'
                                ? 'scale-110 border-slate-700 ring-2 ring-offset-1 ring-slate-400/40'
                                : 'border-transparent hover:scale-105'"
                            style="background-color: {{ $color }}">
                    </button>
                    @endforeach
                    <div class="relative" title="لون مخصص">
                        <input type="color"
                               :value="selectedColor"
                               @input="selectedColor = $event.target.value"
                               class="w-7 h-7 rounded-lg cursor-pointer border border-slate-200 p-0">
                    </div>
                </div>
                <div class="mt-2 flex items-center gap-2">
                    <div class="w-3.5 h-3.5 rounded-full border border-black/10" :style="`background-color: ${selectedColor}`"></div>
                    <span class="text-xs text-muted font-mono" x-text="selectedColor"></span>
                </div>
                @error('color')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- ── وصف المشروع ── --}}
        <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">
                وصف المشروع
                <span class="text-muted font-normal text-xs">(اختياري)</span>
            </label>
            <textarea name="description"
                      rows="3"
                      placeholder="وصف مختصر للمشروع وأهدافه..."
                      class="dash-field px-3.5 py-2.5 resize-none @error('description') dash-field-error @enderror">{{ old('description', $project->description ?? '') }}</textarea>
            @error('description')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- ── قيمة العقد + العميل ── --}}
        <div class="grid grid-cols-2 gap-5">
            {{-- قيمة العقد --}}
            <div>
                <label class="block text-sm font-semibold text-ink mb-1.5">
                    قيمة العقد
                    <span class="text-muted font-normal text-xs">(المتفق عليه)</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <input type="number" name="contract_value" step="0.01" min="0"
                           value="{{ old('contract_value', $project->contract_value ?? '') }}"
                           placeholder="0.00"
                           class="dash-field pr-9 pl-3.5 py-2.5 @error('contract_value') dash-field-error @enderror">
                </div>
                @error('contract_value')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- العميل --}}
            <div>
                <label class="block text-sm font-semibold text-ink mb-1.5">
                    العميل
                    <span class="text-muted font-normal text-xs">(اختياري)</span>
                </label>
                @if($clients->isEmpty())
                    <div class="flex items-start gap-2 p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-700">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>لا يوجد عملاء بعد.
                            <a href="{{ route('clients.create') }}" class="font-semibold underline">أضف عميلاً</a>
                        </span>
                    </div>
                @else
                    <div class="relative">
                        <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <select name="client_id" class="dash-field pr-9 pl-3.5 py-2.5">
                            <option value="">— بدون عميل —</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}"
                                        {{ old('client_id', $project->client_id ?? '') == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }}{{ $client->company ? ' — ' . $client->company : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('client_id')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                @endif
            </div>
        </div>

        {{-- ── حالة المشروع ── --}}
        @php
            $currentStatus = old('status', isset($project) ? $project->status->value : 'active');
            $statuses = [
                [
                    'value' => 'active',
                    'label' => 'نشط',
                    'desc'  => 'يُعمل عليه الآن',
                    'icon'  => '<circle cx="12" cy="12" r="5" fill="currentColor"/><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5" fill="none"/>',
                    'color' => 'text-emerald-600',
                    'activeBg' => 'border-emerald-500 bg-emerald-50',
                ],
                [
                    'value' => 'completed',
                    'label' => 'مكتمل',
                    'desc'  => 'تم التسليم',
                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                    'color' => 'text-brand-600',
                    'activeBg' => 'border-brand bg-brand-50',
                ],
                [
                    'value' => 'on_hold',
                    'label' => 'متوقف',
                    'desc'  => 'مؤجل مؤقتاً',
                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                    'color' => 'text-amber-600',
                    'activeBg' => 'border-amber-400 bg-amber-50',
                ],
                [
                    'value' => 'cancelled',
                    'label' => 'ملغي',
                    'desc'  => 'تم الإلغاء',
                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                    'color' => 'text-red-500',
                    'activeBg' => 'border-red-400 bg-red-50',
                ],
            ];
        @endphp
        <div x-data="{ selectedStatus: '{{ $currentStatus }}' }">
            <label class="block text-sm font-semibold text-ink mb-2">حالة المشروع</label>
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                @foreach($statuses as $s)
                <label class="cursor-pointer" @click="selectedStatus = '{{ $s['value'] }}'">
                    <input type="radio" name="status" value="{{ $s['value'] }}"
                           {{ $currentStatus === $s['value'] ? 'checked' : '' }}
                           class="sr-only">
                    <div class="flex flex-col items-center gap-1.5 px-3 py-3.5 rounded-xl border-2 text-center transition-all cursor-pointer"
                         :class="selectedStatus === '{{ $s['value'] }}'
                             ? '{{ $s['activeBg'] }} {{ $s['color'] }}'
                             : 'border-subtle bg-surface text-slate-400'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            {!! $s['icon'] !!}
                        </svg>
                        <span class="text-xs font-bold"
                              :class="selectedStatus === '{{ $s['value'] }}' ? '{{ $s['color'] }}' : 'text-ink'">{{ $s['label'] }}</span>
                        <span class="text-[10px] text-muted leading-tight">{{ $s['desc'] }}</span>
                    </div>
                </label>
                @endforeach
            </div>
            @error('status')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- ── الخدمات المقدمة ── --}}
        <div class="border-t border-subtle pt-6" x-data="{
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
                        this.quickName    = '';
                        this.quickAddOpen = false;
                    } catch (e) {
                        this.quickError = 'حدث خطأ، حاول مرة أخرى.';
                    } finally {
                        this.quickLoading = false;
                    }
                }
             }">

            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm font-semibold text-ink">الخدمات المقدمة</p>
                    <p class="text-xs text-muted mt-0.5">أضف الخدمات وتكاليف التنفيذ لحساب الهامش</p>
                </div>
                <button type="button" @click="addService()"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold
                               text-brand bg-brand-50 hover:bg-brand-100 rounded-xl border border-brand/20 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    إضافة خدمة
                </button>
            </div>

            {{-- قائمة الخدمات --}}
            <div class="space-y-3" x-show="services.length > 0">
                <template x-for="(svc, index) in services" :key="index">
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-3">

                        {{-- الخدمة + حذف --}}
                        <div class="flex items-start gap-3">
                            <div class="flex-1">
                                <label class="block text-xs font-semibold text-slate-500 mb-1.5">الخدمة</label>
                                <select :name="`services[${index}][service_id]`"
                                        x-model="svc.service_id"
                                        @change="fetchServiceHistory(svc)"
                                        class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm bg-white
                                               focus:outline-none focus:ring-2 focus:ring-accent/40 focus:border-transparent">
                                    <option value="">— اختر خدمة —</option>
                                    <template x-for="opt in allServiceOptions" :key="opt.id">
                                        <option :value="String(opt.id)" x-text="opt.name_ar"
                                                :selected="String(opt.id) === String(svc.service_id)"></option>
                                    </template>
                                </select>
                                <template x-if="svc.history && svc.history.has_history">
                                    <div class="mt-1.5 flex items-center gap-2 text-xs px-2.5 py-1.5 rounded-lg"
                                         :class="{
                                             'bg-emerald-50 text-emerald-700': svc.history.avg_margin >= 40,
                                             'bg-amber-50 text-amber-700':    svc.history.avg_margin >= 20 && svc.history.avg_margin < 40,
                                             'bg-orange-50 text-orange-700':  svc.history.avg_margin >= 0  && svc.history.avg_margin < 20,
                                             'bg-red-50 text-red-700':        svc.history.avg_margin < 0
                                         }">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                        </svg>
                                        <span>
                                            متوسط هامشك:
                                            <strong x-text="`${svc.history.avg_margin}%`"></strong>
                                            (<span x-text="svc.history.label"></span>)
                                            في <span x-text="svc.history.times_used"></span> مشروع
                                        </span>
                                    </div>
                                </template>
                                <template x-if="svc.historyLoading">
                                    <p class="mt-1 text-xs text-muted">جاري التحقق...</p>
                                </template>
                            </div>
                            <button type="button"
                                    @click="removeService(index)"
                                    class="mt-6 w-8 h-8 flex-shrink-0 flex items-center justify-center
                                           text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        {{-- القيمة --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-xs font-semibold text-slate-500">القيمة</label>
                                <template x-if="suggestedPrice(svc) !== null && (! svc.amount || parseFloat(svc.amount) === 0)">
                                    <button type="button"
                                            @click="svc.amount = suggestedPrice(svc)"
                                            class="text-xs text-brand hover:text-brand-700 font-semibold transition flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                        </svg>
                                        اقتراح: <span x-text="suggestedPrice(svc).toLocaleString('ar-SA')"></span>
                                        (<span x-text="effectiveMarginPct(svc)"></span>% هامش)
                                    </button>
                                </template>
                            </div>
                            <input type="number"
                                   :name="`services[${index}][amount]`"
                                   x-model="svc.amount"
                                   min="0" step="0.01"
                                   placeholder="0.00"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm bg-white
                                          focus:outline-none focus:ring-2 focus:ring-accent/40 focus:border-transparent">
                            <input type="hidden" :name="`services[${index}][type]`" value="income">
                        </div>

                        {{-- الملاحظات + الهامش المخصص --}}
                        <div class="grid grid-cols-[1fr_auto] gap-2 items-start">
                            <input type="text"
                                   :name="`services[${index}][notes]`"
                                   x-model="svc.notes"
                                   placeholder="ملاحظات (اختياري)..."
                                   class="w-full px-3 py-2 rounded-lg border border-slate-200 text-xs text-slate-600
                                          focus:outline-none focus:ring-1 focus:ring-accent/40 bg-white">
                            <div class="flex-shrink-0" x-data="{ showMargin: svc.target_margin_pct !== '' }">
                                <template x-if="!showMargin">
                                    <button type="button"
                                            @click="showMargin = true"
                                            :title="`الهامش العام: ${targetMarginPct}%`"
                                            class="flex items-center gap-1 px-2.5 py-2 text-xs text-slate-400
                                                   hover:text-brand hover:bg-brand-50 rounded-lg transition border border-slate-200 border-dashed">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
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
                                                   class="w-16 px-2 py-2 pl-5 rounded-lg border border-brand/40 text-xs
                                                          text-brand-600 font-bold bg-brand-50
                                                          focus:outline-none focus:ring-2 focus:ring-accent/40">
                                            <span class="absolute left-1.5 top-1/2 -translate-y-1/2 text-xs text-brand font-bold pointer-events-none">%</span>
                                        </div>
                                        <button type="button"
                                                @click="svc.target_margin_pct = ''; showMargin = false"
                                                class="text-slate-300 hover:text-red-400 transition"
                                                title="إزالة الهامش المخصص">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- منفذو الخدمة --}}
                        <div class="pt-3 border-t border-slate-100 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-500">منفذو الخدمة</span>
                                <button type="button" @click="addMember(index)"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium
                                               text-brand hover:bg-brand-50 rounded-lg transition">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    إضافة منفذ
                                </button>
                            </div>

                            <template x-for="(member, memberIndex) in svc.members" :key="memberIndex">
                                <div class="flex items-center gap-2 bg-white border border-slate-100 rounded-lg p-2.5">
                                    <select :name="`services[${index}][members][${memberIndex}][team_member_id]`"
                                            x-model="member.team_member_id"
                                            class="flex-1 px-2.5 py-1.5 rounded-lg border border-slate-200 text-sm bg-white
                                                   focus:outline-none focus:ring-2 focus:ring-accent/40">
                                        <option value="">— اختر منفذاً —</option>
                                        <template x-for="tm in teamMembers" :key="tm.id">
                                            <option :value="String(tm.id)" x-text="tm.name"
                                                    :selected="String(tm.id) === String(member.team_member_id)"></option>
                                        </template>
                                    </select>
                                    <div class="relative w-28 flex-shrink-0">
                                        <input type="number"
                                               :name="`services[${index}][members][${memberIndex}][team_cost]`"
                                               x-model="member.team_cost"
                                               min="0" step="0.01" placeholder="التكلفة"
                                               class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-sm
                                                      focus:outline-none focus:ring-2 focus:ring-accent/40">
                                    </div>
                                    <button type="button" @click="removeMember(index, memberIndex)"
                                            class="w-6 h-6 flex-shrink-0 flex items-center justify-center
                                                   text-slate-300 hover:text-red-500 rounded transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>

                            <p x-show="svc.members.length === 0"
                               class="text-xs text-muted text-center py-1">
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
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" title="هامش ممتاز">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </template>
                                        <template x-if="serviceMargin(svc).pct !== null && serviceMargin(svc).pct >= 20 && serviceMargin(svc).pct < 40">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" title="هامش مقبول">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </template>
                                        <template x-if="serviceMargin(svc).pct !== null && serviceMargin(svc).pct >= 0 && serviceMargin(svc).pct < 20">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" title="هامش منخفض">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                        </template>
                                        <template x-if="serviceMargin(svc).margin < 0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" title="خسارة">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <template x-if="
                                suggestedPrice(svc) !== null &&
                                parseFloat(svc.amount) > 0 &&
                                serviceMargin(svc).pct !== null &&
                                serviceMargin(svc).pct < effectiveMarginPct(svc)
                            ">
                                <div class="flex items-center justify-between rounded-lg bg-brand-50 border border-brand/30 px-3 py-2 text-xs text-brand-700">
                                    <span>
                                        لتحقيق هامش <strong x-text="`${effectiveMarginPct(svc)}%`"></strong>،
                                        السعر الموصى به: <strong x-text="suggestedPrice(svc).toLocaleString('ar-SA')"></strong>
                                    </span>
                                    <button type="button"
                                            @click="svc.amount = suggestedPrice(svc)"
                                            class="mr-2 px-2 py-0.5 bg-brand hover:bg-brand-600 text-white rounded font-medium transition text-xs">
                                        تطبيق
                                    </button>
                                </div>
                            </template>

                            <template x-if="
                                svc.history && svc.history.has_history &&
                                svc.history.avg_margin >= 20 &&
                                serviceMargin(svc).pct !== null &&
                                serviceMargin(svc).pct < (svc.history.avg_margin * 0.5)
                            ">
                                <div class="flex items-start gap-2 rounded-lg bg-purple-50 border border-purple-200 px-3 py-2 text-xs text-purple-800">
                                    <svg class="w-3.5 h-3.5 mt-px shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                                    </svg>
                                    <span>
                                        الهامش الحالي (<strong x-text="`${serviceMargin(svc).pct}%`"></strong>)
                                        أقل من متوسطك التاريخي (<strong x-text="`${svc.history.avg_margin}%`"></strong>).
                                        هل راجعت التسعير؟
                                    </span>
                                </div>
                            </template>

                            <template x-if="svc.members.length > 0 && serviceMargin(svc).pct !== null && serviceMargin(svc).pct >= 0 && serviceMargin(svc).pct < 20">
                                <div class="flex items-start gap-2 rounded-lg bg-orange-50 border border-orange-200 px-3 py-2 text-xs text-orange-800">
                                    <svg class="w-3.5 h-3.5 mt-px shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <span>
                                        تكلفة المنفذين وصلت <strong x-text="`${costPct(svc)}%`"></strong>
                                        من قيمة الخدمة — الهامش أقل من 20%.
                                    </span>
                                </div>
                            </template>

                            <template x-if="svc.members.length > 0 && serviceMargin(svc).margin < 0">
                                <div class="flex items-start gap-2 rounded-lg bg-red-50 border border-red-300 px-3 py-2 text-xs text-red-800 font-semibold">
                                    <svg class="w-3.5 h-3.5 mt-px shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
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
                    <template x-if="projectMarginSummary().isLoss">
                        <div class="flex items-start gap-2 rounded-xl bg-red-50 border border-red-300 px-4 py-3 text-sm text-red-800">
                            <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="font-bold">إجمالي تكاليف الفريق يتجاوز إجمالي إيرادات الخدمات</p>
                                <p class="mt-0.5 text-xs font-normal text-red-600">
                                    الإيرادات: <strong x-text="projectMarginSummary().totalRevenue.toLocaleString('ar-SA', {maximumFractionDigits: 2})"></strong>
                                    · التكاليف: <strong x-text="projectMarginSummary().totalCost.toLocaleString('ar-SA', {maximumFractionDigits: 2})"></strong>
                                </p>
                            </div>
                        </div>
                    </template>
                    <template x-if="!projectMarginSummary().isLoss && projectMarginSummary().pct !== null && projectMarginSummary().pct < 20">
                        <div class="flex items-start gap-2 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                            <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
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

            {{-- Empty state خدمات --}}
            <div x-show="services.length === 0"
                 class="flex flex-col items-center gap-2 py-8 border-2 border-dashed border-slate-200 rounded-xl text-center">
                <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-sm text-muted">لا توجد خدمات مضافة بعد</p>
                <button type="button" @click="addService()"
                        class="text-xs font-semibold text-brand hover:text-brand-700 transition">
                    + أضف خدمة للمشروع
                </button>
            </div>

            {{-- إضافة خدمة مخصصة --}}
            <div class="mt-3">
                <button type="button"
                        @click="quickAddOpen = !quickAddOpen"
                        class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-brand transition">
                    <svg class="w-3.5 h-3.5 transition-transform" :class="quickAddOpen ? 'rotate-45' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span x-text="quickAddOpen ? 'إلغاء' : 'خدمتك غير موجودة؟ أضفها هنا'"></span>
                </button>
                <div x-show="quickAddOpen" x-transition class="mt-3 p-4 bg-brand-50 border border-brand/30 rounded-xl">
                    <p class="text-xs font-semibold text-brand-700 mb-2.5">
                        إضافة خدمة مخصصة
                        <span class="text-brand/70 font-normal">(ستُحفظ في قائمة خدماتك)</span>
                    </p>
                    <div class="flex gap-2">
                        <input type="text"
                               x-model="quickName"
                               @keydown.enter.prevent="submitQuick()"
                               placeholder="مثال: تصوير منتجات، تدريب، استشارة..."
                               class="flex-1 px-3 py-2 rounded-lg border border-brand/30 text-sm bg-white
                                      focus:outline-none focus:ring-2 focus:ring-accent/40">
                        <button type="button"
                                @click="submitQuick()"
                                :disabled="quickLoading || !quickName.trim()"
                                class="px-4 py-2 bg-brand hover:bg-brand-600 text-white text-xs font-semibold
                                       rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!quickLoading">إضافة</span>
                            <span x-show="quickLoading">...</span>
                        </button>
                    </div>
                    <p x-show="quickError" x-text="quickError" class="mt-1.5 text-xs text-red-600"></p>
                </div>
            </div>

        </div>

        {{-- ── أزرار الحفظ ── --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-subtle">
            <a href="{{ route('projects.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-muted
                      hover:text-ink hover:bg-slate-100 rounded-btn transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                إلغاء
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-brand hover:bg-brand-600
                           text-white text-sm font-semibold rounded-btn transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                {{ isset($project) ? 'حفظ التعديلات' : 'إنشاء المشروع' }}
            </button>
        </div>

    </div>
</div>

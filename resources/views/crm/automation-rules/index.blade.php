<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-slate-800 leading-tight">⚙️ قواعد الأتمتة</h2>
                <p class="text-sm text-slate-500 mt-0.5">أتمتة إجراءات تلقائية عند وقوع أحداث على العملاء</p>
            </div>
            <a href="{{ route('clients.index') }}"
               class="text-sm text-muted hover:text-ink transition-colors flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                العملاء
            </a>
        </div>
    </x-slot>

    {{-- ==================== TOAST ==================== --}}
    @if (session('success'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 3500)"
            x-transition.opacity
            class="fixed top-5 left-1/2 -translate-x-1/2 z-50 bg-emerald-600 text-white px-5 py-3 rounded-xl shadow-lg text-sm font-medium pointer-events-none"
        >
            {{ session('success') }}
        </div>
    @endif

    <div
        x-data="automationManager()"
        x-init="init()"
        class="py-8"
    >
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ==================== Header Stats ==================== --}}
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 text-center">
                    <div class="text-3xl font-bold text-slate-800" x-text="rules.length">{{ $rules->count() }}</div>
                    <div class="text-sm text-slate-500 mt-1">إجمالي القواعد</div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 text-center">
                    <div class="text-3xl font-bold text-emerald-600" x-text="rules.filter(r => r.is_active).length">{{ $rules->where('is_active', true)->count() }}</div>
                    <div class="text-sm text-slate-500 mt-1">قواعد نشطة</div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 text-center">
                    <div class="text-3xl font-bold text-brand" x-text="rules.reduce((s,r) => s + r.run_count, 0)">{{ $rules->sum('run_count') }}</div>
                    <div class="text-sm text-slate-500 mt-1">مرات التنفيذ</div>
                </div>
            </div>

            {{-- ==================== Toolbar ==================== --}}
            <div class="flex items-center justify-between">
                <h3 class="text-base font-semibold text-slate-700">قواعدي</h3>
                <button
                    @click="openCreate()"
                    class="inline-flex items-center gap-2 bg-brand hover:bg-brand-600 text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-sm"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    قاعدة جديدة
                </button>
            </div>

            {{-- ==================== Rules List ==================== --}}
            <div class="space-y-3">

                {{-- Empty state --}}
                <div x-show="rules.length === 0" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-12 text-center">
                    <div class="text-5xl mb-4">⚙️</div>
                    <h3 class="text-lg font-semibold text-slate-700 mb-2">لا توجد قواعد أتمتة بعد</h3>
                    <p class="text-sm text-slate-500 mb-6">أنشئ قاعدة لأتمتة الإجراءات تلقائياً على عملائك</p>
                    <button @click="openCreate()"
                            class="bg-brand text-white text-sm px-5 py-2.5 rounded-xl hover:bg-brand-600 transition">
                        + إنشاء أول قاعدة
                    </button>
                </div>

                {{-- Rule cards --}}
                <template x-for="rule in rules" :key="rule.id">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 transition hover:shadow-md">
                        <div class="flex items-start justify-between gap-4">

                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-3 mb-2">
                                    {{-- Active toggle --}}
                                    <button
                                        @click="toggleRule(rule)"
                                        :class="rule.is_active ? 'bg-emerald-500' : 'bg-slate-300'"
                                        class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 focus:outline-none"
                                        :title="rule.is_active ? 'إيقاف القاعدة' : 'تفعيل القاعدة'"
                                    >
                                        <span
                                            :class="rule.is_active ? 'translate-x-4' : 'translate-x-0'"
                                            class="inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200"
                                        ></span>
                                    </button>
                                    <h4 class="font-semibold text-slate-800 truncate" x-text="rule.name"></h4>
                                    <span
                                        :class="rule.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                                        class="text-xs px-2 py-0.5 rounded-full font-medium shrink-0"
                                        x-text="rule.is_active ? 'نشطة' : 'متوقفة'"
                                    ></span>
                                </div>

                                <div class="flex flex-wrap items-center gap-3 text-sm text-slate-500">
                                    {{-- Trigger badge --}}
                                    <span class="inline-flex items-center gap-1.5 bg-brand-50 text-brand-600 px-2.5 py-1 rounded-lg text-xs font-medium">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                                        </svg>
                                        <span x-text="triggerLabel(rule.trigger)"></span>
                                    </span>

                                    {{-- Actions count --}}
                                    <span class="text-xs text-slate-400">
                                        <span x-text="rule.actions ? rule.actions.length : 0"></span> إجراء
                                    </span>

                                    {{-- Run count --}}
                                    <span class="text-xs text-slate-400" x-show="rule.run_count > 0">
                                        نُفِّذت <span x-text="rule.run_count"></span> مرة
                                    </span>

                                    {{-- Last run --}}
                                    <span class="text-xs text-slate-400" x-show="rule.last_run_at">
                                        آخر تشغيل: <span x-text="formatDate(rule.last_run_at)"></span>
                                    </span>
                                </div>

                                {{-- Actions preview --}}
                                <div class="flex flex-wrap gap-1.5 mt-3" x-show="rule.actions && rule.actions.length > 0">
                                    <template x-for="(action, i) in rule.actions" :key="i">
                                        <span class="inline-flex items-center gap-1 bg-slate-50 border border-slate-200 text-slate-600 text-xs px-2 py-0.5 rounded-lg">
                                            <span x-text="actionIcon(action.type)"></span>
                                            <span x-text="actionLabel(action.type)"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>

                            {{-- Actions menu --}}
                            <div class="flex items-center gap-2 shrink-0">
                                <button
                                    @click="openEdit(rule)"
                                    class="p-2 text-slate-400 hover:text-brand hover:bg-brand-50 rounded-lg transition"
                                    title="تعديل"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button
                                    @click="deleteRule(rule)"
                                    class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                                    title="حذف"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>

                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- ==================== CREATE / EDIT MODAL ==================== --}}
        <div
            x-show="modalOpen"
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-start justify-center bg-black/50 backdrop-blur-sm p-4 overflow-y-auto"
            @click.self="closeModal()"
            style="display:none"
        >
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl my-8" @click.stop>

                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800" x-text="editingId ? '✏️ تعديل القاعدة' : '➕ قاعدة أتمتة جديدة'"></h3>
                    <button @click="closeModal()" class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-5">

                    {{-- Name + Active --}}
                    <div class="flex items-end gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-semibold text-ink mb-1.5">اسم القاعدة <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                x-model="form.name"
                                placeholder="مثال: تنبيه عند انخفاض مؤشر الصحة"
                                maxlength="120"
                                class="w-full border-slate-300 rounded-xl focus:ring-accent/40 focus:border-accent text-sm"
                            >
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer mb-2">
                            <input type="checkbox" x-model="form.is_active" class="w-4 h-4 rounded text-brand border-slate-300 focus:ring-accent/40">
                            <span class="text-sm text-slate-700">نشطة</span>
                        </label>
                    </div>

                    {{-- Trigger --}}
                    <div>
                        <label class="block text-sm font-semibold text-ink mb-1.5">الحدث المُشغِّل <span class="text-red-500">*</span></label>
                        <select x-model="form.trigger" class="w-full border-slate-300 rounded-xl focus:ring-accent/40 focus:border-accent text-sm">
                            <option value="">— اختر الحدث —</option>
                            @foreach($triggers as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-400 mt-1">سيتم تشغيل هذه القاعدة عند وقوع هذا الحدث على أي عميل</p>
                    </div>

                    {{-- Priority --}}
                    <div>
                        <label class="block text-sm font-semibold text-ink mb-1.5">الأولوية</label>
                        <div class="flex items-center gap-3">
                            <input type="range" x-model.number="form.priority" min="1" max="100" step="1" class="flex-1 accent-brand">
                            <span class="text-sm font-medium text-brand w-8 text-center" x-text="form.priority"></span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">رقم أصغر = تنفيذ أسبق عند تعدد القواعد</p>
                    </div>

                    {{-- Conditions (simple UI) --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-slate-700">الشروط <span class="text-slate-400 font-normal">(اختياري)</span></label>
                            <button @click="addCondition()" type="button"
                                    class="text-xs text-brand hover:text-brand-700 font-medium">
                                + إضافة شرط
                            </button>
                        </div>

                        <div x-show="form.conditions.length === 0" class="text-xs text-slate-400 bg-slate-50 rounded-xl p-3 text-center">
                            بدون شروط — ستُطبَّق القاعدة على جميع العملاء عند وقوع الحدث
                        </div>

                        <div class="space-y-2">
                            <template x-for="(cond, i) in form.conditions" :key="i">
                                <div class="flex items-center gap-2 bg-slate-50 rounded-xl p-3">
                                    <select x-model="cond.field" class="flex-1 border-slate-300 rounded-lg text-sm focus:ring-accent/40">
                                        <option value="health_score">مؤشر الصحة</option>
                                        <option value="status">الحالة</option>
                                        <option value="source">المصدر</option>
                                        <option value="days_since_contact">أيام بدون تواصل</option>
                                    </select>
                                    <select x-model="cond.op" class="border-slate-300 rounded-lg text-sm focus:ring-accent/40">
                                        <option value="equals">يساوي</option>
                                        <option value="not_equals">لا يساوي</option>
                                        <option value="less_than">أقل من</option>
                                        <option value="greater_than">أكبر من</option>
                                    </select>
                                    <input type="text" x-model="cond.value" placeholder="القيمة"
                                           class="flex-1 border-slate-300 rounded-lg text-sm focus:ring-accent/40">
                                    <button @click="removeCondition(i)" type="button"
                                            class="text-slate-400 hover:text-red-500 p-1 rounded transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-slate-700">الإجراءات <span class="text-red-500">*</span></label>
                            <button @click="addAction()" type="button"
                                    class="text-xs text-brand hover:text-brand-700 font-medium">
                                + إضافة إجراء
                            </button>
                        </div>

                        <div x-show="form.actions.length === 0" class="text-xs text-red-400 bg-red-50 rounded-xl p-3 text-center">
                            يجب إضافة إجراء واحد على الأقل
                        </div>

                        <div class="space-y-3">
                            <template x-for="(action, i) in form.actions" :key="i">
                                <div class="bg-brand-50 rounded-xl p-4 space-y-3">
                                    <div class="flex items-center gap-3">
                                        <select x-model="action.type" @change="resetActionParams(action)"
                                                class="flex-1 border-slate-300 rounded-lg text-sm focus:ring-accent/40">
                                            <option value="">— اختر الإجراء —</option>
                                            @foreach($actionTypes as $at)
                                                <option value="{{ $at['type'] }}">{{ $at['label'] }}</option>
                                            @endforeach
                                        </select>
                                        <button @click="removeAction(i)" type="button"
                                                class="text-slate-400 hover:text-red-500 p-1.5 rounded transition shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>

                                    {{-- Params per action type --}}
                                    <div x-show="action.type === 'assign_tag'">
                                        <input type="text" x-model="action.params.tag_slug" placeholder="slug الوسم (مثال: vip)"
                                               class="w-full border-slate-300 rounded-lg text-sm focus:ring-accent/40">
                                    </div>
                                    <div x-show="action.type === 'create_follow_up'" class="grid grid-cols-2 gap-2">
                                        <input type="text" x-model="action.params.message" placeholder="رسالة المتابعة"
                                               class="border-slate-300 rounded-lg text-sm focus:ring-accent/40">
                                        <div class="flex items-center gap-2">
                                            <input type="number" x-model.number="action.params.days_from_now" min="1" max="90"
                                                   placeholder="الأيام" class="w-24 border-slate-300 rounded-lg text-sm focus:ring-accent/40">
                                            <span class="text-xs text-slate-500">يوم من الآن</span>
                                        </div>
                                    </div>
                                    <div x-show="action.type === 'send_notification'">
                                        <input type="text" x-model="action.params.message" placeholder="نص الإشعار"
                                               class="w-full border-slate-300 rounded-lg text-sm focus:ring-accent/40">
                                    </div>
                                    <div x-show="action.type === 'update_status'">
                                        <select x-model="action.params.status" class="w-full border-slate-300 rounded-lg text-sm focus:ring-accent/40">
                                            <option value="">— اختر الحالة الجديدة —</option>
                                            <option value="active">نشط</option>
                                            <option value="inactive">غير نشط</option>
                                            <option value="lead">عميل محتمل</option>
                                            <option value="churned">فقد الاهتمام</option>
                                        </select>
                                    </div>
                                    <div x-show="action.type === 'log_note'">
                                        <input type="text" x-model="action.params.note" placeholder="نص الملاحظة"
                                               class="w-full border-slate-300 rounded-lg text-sm focus:ring-accent/40">
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Error --}}
                    <div x-show="errorMsg" x-text="errorMsg" class="text-sm text-red-600 bg-red-50 rounded-xl px-4 py-2.5"></div>

                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-between gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50 rounded-b-2xl">
                    <button @click="closeModal()" class="px-4 py-2 text-sm text-slate-600 border border-slate-200 rounded-xl hover:bg-white transition">
                        إلغاء
                    </button>
                    <button
                        @click="submit()"
                        :disabled="saving"
                        class="px-5 py-2 bg-brand text-white text-sm font-medium rounded-xl hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center gap-2"
                    >
                        <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-text="saving ? 'جاري الحفظ...' : (editingId ? 'تحديث القاعدة' : 'إنشاء القاعدة')"></span>
                    </button>
                </div>

            </div>
        </div>

    </div>

    @push('scripts')
    <script>
    function automationManager() {
        return {
            // ---- State ----
            rules:     @json($rules),
            triggers:  @json($triggers),
            actions:   @json($actionTypes),
            modalOpen: false,
            editingId: null,
            saving:    false,
            errorMsg:  '',

            form: {
                name:       '',
                trigger:    '',
                is_active:  true,
                priority:   10,
                conditions: [],
                actions:    [],
            },

            // ---- Init ----
            init() {},

            // ---- Helpers ----
            triggerLabel(trigger) {
                return this.triggers[trigger] || trigger;
            },
            actionLabel(type) {
                const a = this.actions.find(a => a.type === type);
                return a ? a.label : type;
            },
            actionIcon(type) {
                const icons = {
                    assign_tag:        '🏷️',
                    create_follow_up:  '📅',
                    send_notification: '🔔',
                    update_status:     '🔄',
                    log_note:          '📝',
                };
                return icons[type] || '⚙️';
            },
            formatDate(dt) {
                if (!dt) return '';
                return new Date(dt).toLocaleDateString('ar-SA', { day: 'numeric', month: 'short', year: 'numeric' });
            },

            // ---- Form builders ----
            resetForm() {
                this.form = { name: '', trigger: '', is_active: true, priority: 10, conditions: [], actions: [] };
                this.errorMsg = '';
                this.editingId = null;
            },

            openCreate() {
                this.resetForm();
                this.modalOpen = true;
            },

            openEdit(rule) {
                this.editingId = rule.id;
                this.form = {
                    name:       rule.name,
                    trigger:    rule.trigger,
                    is_active:  rule.is_active,
                    priority:   rule.priority,
                    conditions: JSON.parse(JSON.stringify(rule.conditions || [])),
                    actions:    JSON.parse(JSON.stringify(rule.actions   || [])),
                };
                this.errorMsg = '';
                this.modalOpen = true;
            },

            closeModal() {
                this.modalOpen = false;
                this.resetForm();
            },

            // ---- Conditions ----
            addCondition() {
                this.form.conditions.push({ field: 'health_score', op: 'less_than', value: '' });
            },
            removeCondition(i) {
                this.form.conditions.splice(i, 1);
            },

            // ---- Actions ----
            addAction() {
                this.form.actions.push({ type: '', params: {} });
            },
            removeAction(i) {
                this.form.actions.splice(i, 1);
            },
            resetActionParams(action) {
                action.params = {};
            },

            // ---- Submit ----
            async submit() {
                if (!this.form.name.trim()) { this.errorMsg = 'يرجى إدخال اسم القاعدة'; return; }
                if (!this.form.trigger)     { this.errorMsg = 'يرجى اختيار الحدث المُشغِّل'; return; }
                if (this.form.actions.length === 0) { this.errorMsg = 'يرجى إضافة إجراء واحد على الأقل'; return; }
                if (this.form.actions.some(a => !a.type)) { this.errorMsg = 'يرجى اختيار نوع كل إجراء'; return; }

                this.errorMsg = '';
                this.saving   = true;

                const url    = this.editingId
                    ? `/clients/automation-rules/${this.editingId}`
                    : '/clients/automation-rules';
                const method = this.editingId ? 'PUT' : 'POST';

                const payload = {
                    ...this.form,
                    conditions: this.form.conditions.length ? { operator: 'AND', conditions: this.form.conditions } : null,
                    _token: document.querySelector('meta[name="csrf-token"]').content,
                };

                try {
                    const res = await fetch(url, {
                        method,
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify(payload),
                    });
                    const data = await res.json();

                    if (!res.ok) {
                        const msgs = data.errors ? Object.values(data.errors).flat().join(' | ') : (data.message || 'حدث خطأ');
                        this.errorMsg = msgs;
                        return;
                    }

                    if (this.editingId) {
                        const idx = this.rules.findIndex(r => r.id === this.editingId);
                        if (idx !== -1) this.rules.splice(idx, 1, data.data);
                    } else {
                        this.rules.push(data.data);
                    }

                    this.closeModal();
                } catch (e) {
                    this.errorMsg = 'فشل الاتصال بالخادم';
                } finally {
                    this.saving = false;
                }
            },

            // ---- Toggle ----
            async toggleRule(rule) {
                try {
                    const res = await fetch(`/clients/automation-rules/${rule.id}/toggle`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                    });
                    if (res.ok) {
                        const data = await res.json();
                        rule.is_active = data.data.is_active;
                    }
                } catch (e) {}
            },

            // ---- Delete ----
            async deleteRule(rule) {
                if (!confirm(`هل أنت متأكد من حذف قاعدة "${rule.name}"؟`)) return;
                try {
                    const res = await fetch(`/clients/automation-rules/${rule.id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                    });
                    if (res.ok) {
                        this.rules = this.rules.filter(r => r.id !== rule.id);
                    }
                } catch (e) {}
            },
        };
    }
    </script>
    @endpush

</x-app-layout>

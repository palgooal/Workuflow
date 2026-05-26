<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                    <a href="{{ route('clients.index') }}" class="hover:text-gray-700">العملاء</a>
                    <span>/</span>
                    <span class="text-gray-700 font-medium">لوحة المتابعات</span>
                </div>
                <h2 class="text-xl font-bold text-gray-900">📋 لوحة المتابعات</h2>
            </div>
            <button @click="$dispatch('open-add-modal')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition shadow-sm">
                + إضافة متابعة
            </button>
        </div>
    </x-slot>

    <div class="py-6" x-data>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ==================== شريط الإحصاءات ==================== --}}
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-red-50 border border-red-200 rounded-xl px-5 py-4 text-center">
                    <div class="text-2xl font-bold text-red-700">{{ $overdue->count() }}</div>
                    <div class="text-xs text-red-600 mt-0.5 font-medium">🚨 متأخرة</div>
                </div>
                <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 text-center">
                    <div class="text-2xl font-bold text-amber-700">{{ $today->count() }}</div>
                    <div class="text-xs text-amber-600 mt-0.5 font-medium">⏰ اليوم</div>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-xl px-5 py-4 text-center">
                    <div class="text-2xl font-bold text-blue-700">{{ $thisWeek->count() }}</div>
                    <div class="text-xs text-blue-600 mt-0.5 font-medium">📅 هذا الأسبوع</div>
                </div>
            </div>

            {{-- ==================== الأعمدة الثلاثة ==================== --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                {{-- عمود 1: متأخرة --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-lg">🚨</span>
                        <h3 class="font-bold text-gray-800">متأخرة</h3>
                        @if($overdue->count())
                            <span class="bg-red-100 text-red-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ $overdue->count() }}</span>
                        @endif
                    </div>

                    @forelse($overdue as $followUp)
                        <x-crm-follow-up-card :follow-up="$followUp" color="red" />
                    @empty
                        <div class="bg-white border border-dashed border-gray-200 rounded-xl p-6 text-center text-gray-400">
                            <div class="text-2xl mb-1">✅</div>
                            <p class="text-sm">لا توجد متابعات متأخرة</p>
                        </div>
                    @endforelse
                </div>

                {{-- عمود 2: اليوم --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-lg">⏰</span>
                        <h3 class="font-bold text-gray-800">اليوم</h3>
                        @if($today->count())
                            <span class="bg-amber-100 text-amber-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ $today->count() }}</span>
                        @endif
                    </div>

                    @forelse($today as $followUp)
                        <x-crm-follow-up-card :follow-up="$followUp" color="amber" />
                    @empty
                        <div class="bg-white border border-dashed border-gray-200 rounded-xl p-6 text-center text-gray-400">
                            <div class="text-2xl mb-1">☀️</div>
                            <p class="text-sm">لا متابعات لليوم</p>
                        </div>
                    @endforelse
                </div>

                {{-- عمود 3: هذا الأسبوع --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-lg">📅</span>
                        <h3 class="font-bold text-gray-800">هذا الأسبوع</h3>
                        @if($thisWeek->count())
                            <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ $thisWeek->count() }}</span>
                        @endif
                    </div>

                    @forelse($thisWeek as $followUp)
                        <x-crm-follow-up-card :follow-up="$followUp" color="blue" />
                    @empty
                        <div class="bg-white border border-dashed border-gray-200 rounded-xl p-6 text-center text-gray-400">
                            <div class="text-2xl mb-1">📆</div>
                            <p class="text-sm">لا متابعات هذا الأسبوع</p>
                        </div>
                    @endforelse
                </div>

            </div>{{-- /grid --}}
        </div>
    </div>

    {{-- ==================== Modal إضافة متابعة سريعة ==================== --}}
    <div x-data="addFollowUpModal()"
         @open-add-modal.window="open = true"
         x-show="open"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
         @keydown.escape.window="open = false">

        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 overflow-hidden"
             @click.outside="open = false">

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-bold text-gray-900 text-lg">📋 إضافة متابعة جديدة</h3>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600 text-xl leading-none">✕</button>
            </div>

            {{-- Form --}}
            <form @submit.prevent="submit()" class="px-6 py-5 space-y-4">

                {{-- العميل --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">العميل <span class="text-red-500">*</span></label>
                    <select x-model="form.client_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            required>
                        <option value="">— اختر عميلاً —</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">
                                {{ $client->name }}{{ $client->company ? ' — ' . $client->company : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- العنوان --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">عنوان المتابعة <span class="text-red-500">*</span></label>
                    <input x-model="form.title"
                           type="text"
                           placeholder="مثال: متابعة الدفعة المعلقة"
                           maxlength="200"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"
                           required>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- تاريخ الاستحقاق --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الاستحقاق <span class="text-red-500">*</span></label>
                        <input x-model="form.due_at"
                               type="datetime-local"
                               :min="minDate"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"
                               required>
                    </div>

                    {{-- النوع --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">النوع</label>
                        <select x-model="form.type"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="call">📞 مكالمة</option>
                            <option value="email">📧 بريد إلكتروني</option>
                            <option value="meeting">🤝 اجتماع</option>
                            <option value="task">✅ مهمة</option>
                            <option value="other">📌 أخرى</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- الأولوية --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">الأولوية</label>
                        <select x-model="form.priority"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="1">🔴 عالية</option>
                            <option value="2" selected>🟡 متوسطة</option>
                            <option value="3">🟢 منخفضة</option>
                        </select>
                    </div>

                    {{-- تذكير --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">تذكيرني في</label>
                        <input x-model="form.reminder_at"
                               type="datetime-local"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                {{-- ملاحظات --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
                    <textarea x-model="form.notes"
                              rows="2"
                              placeholder="تفاصيل اختيارية…"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
                </div>

                {{-- Error --}}
                <p x-show="error" x-text="error" class="text-sm text-red-600"></p>

                {{-- Buttons --}}
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="open = false"
                            class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                        إلغاء
                    </button>
                    <button type="submit" :disabled="saving"
                            class="flex-1 px-4 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition">
                        <span x-show="!saving">+ حفظ المتابعة</span>
                        <span x-show="saving">جاري الحفظ…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Toast --}}
    <div x-data="{ show: false, msg: '', isError: false }"
         @show-toast.window="show = true; msg = $event.detail.msg; isError = !!$event.detail.error; setTimeout(() => show = false, 3500)"
         x-show="show" x-cloak x-transition
         class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 px-5 py-3 rounded-xl shadow-lg text-sm font-medium text-white"
         :class="isError ? 'bg-red-600' : 'bg-gray-900'">
        <span x-text="msg"></span>
    </div>

    <style>[x-cloak] { display: none !important; }</style>

    <script>
    const CSRF         = '{{ csrf_token() }}';
    const QUICK_URL    = '{{ route("clients.follow-ups.quick-store") }}';
    const CLIENT_BASE  = '{{ url("/clients") }}';
    const FOLLOWUP_BASE = '{{ url("/clients") }}'; // used with public_id routing

    // تنسيق datetime-local بالتوقيت المحلي
    function localDatetimeMin() {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        return now.toISOString().slice(0, 16);
    }

    function addFollowUpModal() {
        return {
            open: false,
            saving: false,
            error: '',
            minDate: localDatetimeMin(),
            form: {
                client_id:   '',
                title:       '',
                due_at:      '',
                type:        'call',
                priority:    '2',
                reminder_at: '',
                notes:       '',
            },

            async submit() {
                this.error = '';
                if (!this.form.client_id || !this.form.title || !this.form.due_at) {
                    this.error = 'يرجى تعبئة الحقول المطلوبة.';
                    return;
                }
                this.saving = true;
                try {
                    const res = await fetch(QUICK_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(this.form),
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        const msg = data.errors
                            ? Object.values(data.errors).flat()[0]
                            : (data.message || 'خطأ في الحفظ');
                        throw new Error(msg);
                    }
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { msg: '✅ تم إنشاء المتابعة بنجاح.' }
                    }));
                    this.open = false;
                    // إعادة تحميل الصفحة لعرض المتابعة في العمود الصحيح
                    setTimeout(() => window.location.reload(), 1000);
                } catch (e) {
                    this.error = e.message;
                } finally {
                    this.saving = false;
                }
            },
        };
    }
    </script>
</x-app-layout>

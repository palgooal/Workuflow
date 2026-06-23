<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                    <a href="{{ route('clients.index') }}" class="hover:text-slate-700">العملاء</a>
                    <span>/</span>
                    <span class="text-slate-700 font-medium">إدارة الوسوم</span>
                </div>
                <h2 class="text-xl font-bold text-slate-900">🏷️ إدارة الوسوم</h2>
            </div>
            <a href="{{ route('clients.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition">
                ← العودة للعملاء
            </a>
        </div>
    </x-slot>

    {{-- Sortable.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>

    <div class="py-6" x-data="tagManager()" x-init="init()">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-green-800 text-sm flex items-center gap-2">
                    <span>✅</span> {{ session('success') }}
                </div>
            @endif

            {{-- ==================== إنشاء وسم جديد ==================== --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-800 flex items-center gap-2">
                        <span class="text-lg">✨</span> إضافة وسم جديد
                    </h3>
                    <button @click="showCreateForm = !showCreateForm"
                            class="text-sm text-brand hover:text-brand-700 font-medium">
                        <span x-text="showCreateForm ? '← إخفاء' : '+ إظهار النموذج'"></span>
                    </button>
                </div>

                <div x-show="showCreateForm" x-cloak x-transition class="px-6 py-5">
                    <form @submit.prevent="createTag()" class="flex flex-wrap gap-4 items-end">
                        {{-- الاسم --}}
                        <div class="flex-1 min-w-48">
                            <label class="block text-xs font-medium text-slate-600 mb-1">اسم الوسم *</label>
                            <input x-model="newTag.name"
                                   type="text"
                                   placeholder="مثال: مدير"
                                   maxlength="50"
                                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-accent/40 focus:border-accent">
                        </div>

                        {{-- اللون --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">اللون</label>
                            <div class="flex items-center gap-2">
                                <input x-model="newTag.color"
                                       type="color"
                                       class="w-10 h-10 rounded-lg border border-slate-300 cursor-pointer p-0.5">
                                <span class="text-xs text-slate-500 font-mono" x-text="newTag.color"></span>
                            </div>
                        </div>

                        {{-- الأيقونة --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">أيقونة (Emoji)</label>
                            <input x-model="newTag.icon"
                                   type="text"
                                   placeholder="🏷️"
                                   maxlength="4"
                                   class="w-20 border border-slate-300 rounded-lg px-3 py-2 text-sm text-center focus:ring-2 focus:ring-accent/40">
                        </div>

                        {{-- معاينة --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">معاينة</label>
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium text-white"
                                  :style="{ backgroundColor: newTag.color }">
                                <span x-text="newTag.icon || '🏷️'"></span>
                                <span x-text="newTag.name || 'الوسم'"></span>
                            </span>
                        </div>

                        {{-- زر الحفظ --}}
                        <div>
                            <button type="submit"
                                    :disabled="saving || !newTag.name.trim()"
                                    class="px-5 py-2 bg-brand text-white text-sm font-medium rounded-lg hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed transition">
                                <span x-show="!saving">+ إضافة الوسم</span>
                                <span x-show="saving">جاري الحفظ…</span>
                            </button>
                        </div>
                    </form>

                    {{-- Error --}}
                    <p x-show="createError" x-text="createError" class="mt-2 text-sm text-red-600"></p>
                </div>
            </div>

            {{-- ==================== وسوم مخصصة ==================== --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-slate-800 flex items-center gap-2">
                            <span class="text-lg">🎨</span> الوسوم المخصصة
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">يمكنك تعديلها وحذفها وإعادة ترتيبها بالسحب</p>
                    </div>
                    <span class="text-xs text-slate-400 bg-slate-100 px-2 py-1 rounded-full">
                        {{ $customTags->count() }} وسم
                    </span>
                </div>

                @if($customTags->isEmpty())
                    <div class="px-6 py-12 text-center text-slate-400">
                        <div class="text-4xl mb-3">🏷️</div>
                        <p class="font-medium">لا توجد وسوم مخصصة بعد</p>
                        <p class="text-sm mt-1">أضف وسماً جديداً من النموذج أعلاه لتصنيف عملائك</p>
                    </div>
                @else
                    <div id="custom-tags-list" class="divide-y divide-slate-50">
                        @foreach($customTags as $tag)
                            <div class="tag-row flex items-center gap-4 px-6 py-4 hover:bg-slate-50 transition group"
                                 data-id="{{ $tag->id }}"
                                 x-data="tagRow({{ $tag->id }}, '{{ e($tag->name) }}', '{{ $tag->color }}', '{{ e($tag->icon ?? '') }}')">

                                {{-- Drag Handle --}}
                                <div class="drag-handle cursor-grab active:cursor-grabbing text-slate-300 hover:text-slate-500 shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                    </svg>
                                </div>

                                {{-- الوسم (عرض) --}}
                                <div x-show="!editing" class="flex items-center gap-3 flex-1 min-w-0">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium text-white shrink-0"
                                          :style="{ backgroundColor: color }">
                                        <span x-text="icon || '🏷️'"></span>
                                        <span x-text="name"></span>
                                    </span>
                                    <span class="text-xs text-slate-400">
                                        {{ $tag->clients_count }} {{ $tag->clients_count == 1 ? 'عميل' : 'عملاء' }}
                                    </span>
                                    @if($tag->clients_count > 0)
                                        <a href="{{ route('clients.index', ['tag' => $tag->id]) }}"
                                           class="text-xs text-brand hover:text-brand-600 opacity-0 group-hover:opacity-100 transition">
                                            عرض العملاء ←
                                        </a>
                                    @endif
                                </div>

                                {{-- نموذج التعديل --}}
                                <div x-show="editing" x-cloak class="flex items-end gap-3 flex-1 flex-wrap">
                                    <div>
                                        <label class="block text-xs text-slate-500 mb-1">الاسم</label>
                                        <input x-model="editName" type="text" maxlength="50"
                                               class="border border-slate-300 rounded-lg px-3 py-1.5 text-sm w-36 focus:ring-2 focus:ring-accent/40">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-slate-500 mb-1">اللون</label>
                                        <input x-model="editColor" type="color"
                                               class="w-9 h-9 rounded-lg border border-slate-300 p-0.5 cursor-pointer">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-slate-500 mb-1">أيقونة</label>
                                        <input x-model="editIcon" type="text" maxlength="4" placeholder="🏷️"
                                               class="border border-slate-300 rounded-lg px-3 py-1.5 text-sm w-16 text-center focus:ring-2 focus:ring-accent/40">
                                    </div>
                                    {{-- معاينة --}}
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium text-white shrink-0"
                                          :style="{ backgroundColor: editColor }">
                                        <span x-text="editIcon || '🏷️'"></span>
                                        <span x-text="editName || 'الوسم'"></span>
                                    </span>
                                </div>

                                {{-- أزرار الإجراءات --}}
                                <div class="flex items-center gap-2 shrink-0">
                                    {{-- تعديل --}}
                                    <template x-if="!editing">
                                        <button @click="startEdit()"
                                                class="text-xs text-slate-400 hover:text-brand opacity-0 group-hover:opacity-100 transition px-2 py-1 rounded hover:bg-brand-50">
                                            ✏️ تعديل
                                        </button>
                                    </template>

                                    {{-- حفظ التعديل --}}
                                    <template x-if="editing">
                                        <div class="flex gap-2">
                                            <button @click="saveEdit()"
                                                    :disabled="saving"
                                                    class="text-xs text-white bg-brand hover:bg-brand-600 px-3 py-1.5 rounded-lg disabled:opacity-50 transition">
                                                <span x-show="!saving">💾 حفظ</span>
                                                <span x-show="saving">…</span>
                                            </button>
                                            <button @click="cancelEdit()"
                                                    class="text-xs text-slate-500 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition">
                                                إلغاء
                                            </button>
                                        </div>
                                    </template>

                                    {{-- حذف --}}
                                    <template x-if="!editing">
                                        <button @click="confirmDelete()"
                                                class="text-xs text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition px-2 py-1 rounded hover:bg-red-50">
                                            🗑️
                                        </button>
                                    </template>
                                </div>

                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- تلميح الترتيب --}}
                @if($customTags->count() > 1)
                    <div class="px-6 py-3 border-t border-slate-50 bg-slate-50 rounded-b-xl">
                        <p class="text-xs text-slate-400 flex items-center gap-1">
                            <span>💡</span> اسحب الصفوف لإعادة ترتيب الوسوم — يُحفظ الترتيب تلقائياً
                        </p>
                    </div>
                @endif
            </div>

            {{-- ==================== وسوم النظام ==================== --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-slate-800 flex items-center gap-2">
                            <span class="text-lg">🔒</span> وسوم النظام
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">وسوم مدمجة ومشتركة بين جميع المستخدمين — للعرض فقط</p>
                    </div>
                    <span class="text-xs text-slate-400 bg-slate-100 px-2 py-1 rounded-full">
                        {{ $systemTags->count() }} وسم
                    </span>
                </div>
                <div class="px-6 py-5 flex flex-wrap gap-3">
                    @foreach($systemTags as $tag)
                        <div class="flex flex-col items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium text-white ring-2 ring-white shadow-sm"
                                  style="background-color: {{ $tag->color }}">
                                {{ $tag->icon ?? '🏷️' }} {{ $tag->name }}
                            </span>
                            <span class="text-xs text-slate-400">
                                {{ $tag->clients_count }} {{ $tag->clients_count == 1 ? 'عميل' : 'عملاء' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>{{-- /max-w --}}
    </div>{{-- /x-data --}}

    {{-- ==================== Modal تأكيد الحذف ==================== --}}
    <div id="delete-modal"
         x-data="{ open: false, tagId: null, tagName: '' }"
         @open-delete-modal.window="open = true; tagId = $event.detail.id; tagName = $event.detail.name"
         x-show="open"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
         @keydown.escape.window="open = false">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6">
            <div class="text-center">
                <div class="text-4xl mb-3">🗑️</div>
                <h3 class="text-lg font-bold text-slate-900 mb-1">حذف الوسم</h3>
                <p class="text-sm text-slate-600 mb-1">هل أنت متأكد من حذف وسم</p>
                <p class="text-sm font-bold text-slate-800 mb-4" x-text='"«" + tagName + "»"'></p>
                <p class="text-xs text-red-500 mb-6">سيُزال هذا الوسم من جميع العملاء المرتبطين به.</p>

                <div class="flex gap-3">
                    <button @click="open = false"
                            class="flex-1 px-4 py-2 text-sm border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 transition">
                        إلغاء
                    </button>
                    <button @click="$dispatch('confirm-delete', { id: tagId }); open = false"
                            class="flex-1 px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">
                        نعم، احذف
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    const CSRF = '{{ csrf_token() }}';
    const REORDER_URL = '{{ route("clients.tags.reorder") }}';
    const STORE_URL   = '{{ route("clients.tags.store") }}';
    const TAG_BASE    = '{{ url("/clients/tags") }}';

    // ==================== tagRow (per-row Alpine component) ====================
    function tagRow(id, name, color, icon) {
        return {
            id, name, color, icon,
            editing: false,
            saving: false,
            editName: name,
            editColor: color,
            editIcon: icon,

            startEdit() {
                this.editName  = this.name;
                this.editColor = this.color;
                this.editIcon  = this.icon;
                this.editing   = true;
            },

            cancelEdit() {
                this.editing = false;
            },

            async saveEdit() {
                if (!this.editName.trim()) return;
                this.saving = true;
                try {
                    const res = await fetch(`${TAG_BASE}/${this.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            name:  this.editName.trim(),
                            color: this.editColor,
                            icon:  this.editIcon,
                        }),
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'خطأ');
                    this.name    = this.editName.trim();
                    this.color   = this.editColor;
                    this.icon    = this.editIcon;
                    this.editing = false;
                    window.dispatchEvent(new CustomEvent('show-toast', { detail: { msg: '✅ تم تحديث الوسم.' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('show-toast', { detail: { msg: '❌ ' + e.message, error: true } }));
                } finally {
                    this.saving = false;
                }
            },

            confirmDelete() {
                window.dispatchEvent(new CustomEvent('open-delete-modal', {
                    detail: { id: this.id, name: this.name }
                }));
                this.$el.addEventListener('confirm-delete', async (e) => {
                    if (e.detail.id !== this.id) return;
                    await this.doDelete();
                }, { once: true });
            },

            async doDelete() {
                try {
                    const res = await fetch(`${TAG_BASE}/${this.id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'خطأ في الحذف');
                    this.$el.remove();
                    window.dispatchEvent(new CustomEvent('show-toast', { detail: { msg: '🗑️ تم حذف الوسم.' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('show-toast', { detail: { msg: '❌ ' + e.message, error: true } }));
                }
            },
        };
    }

    // ==================== tagManager (page-level Alpine component) ====================
    function tagManager() {
        return {
            showCreateForm: false,
            saving: false,
            createError: '',
            newTag: { name: '', color: '#6366F1', icon: '' },

            init() {
                // Drag-and-drop بـ Sortable.js
                const list = document.getElementById('custom-tags-list');
                if (list && typeof Sortable !== 'undefined') {
                    Sortable.create(list, {
                        handle: '.drag-handle',
                        animation: 150,
                        ghostClass: 'bg-brand-50',
                        onEnd: () => this.saveOrder(list),
                    });
                }
            },

            async saveOrder(list) {
                const ids = [...list.querySelectorAll('.tag-row')].map(el => el.dataset.id);
                await fetch(REORDER_URL, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ order: ids }),
                });
                window.dispatchEvent(new CustomEvent('show-toast', { detail: { msg: '✅ تم حفظ الترتيب.' } }));
            },

            async createTag() {
                this.createError = '';
                if (!this.newTag.name.trim()) {
                    this.createError = 'اسم الوسم مطلوب.';
                    return;
                }
                this.saving = true;
                try {
                    const res = await fetch(STORE_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(this.newTag),
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        const firstError = data.errors ? Object.values(data.errors)[0]?.[0] : data.message;
                        throw new Error(firstError || 'خطأ في إنشاء الوسم');
                    }
                    // إعادة تحميل الصفحة لعرض الوسم الجديد في القائمة
                    window.location.reload();
                } catch (e) {
                    this.createError = e.message;
                } finally {
                    this.saving = false;
                }
            },
        };
    }
    </script>

    {{-- Toast Notification --}}
    <div x-data="{ show: false, msg: '', error: false }"
         @show-toast.window="show = true; msg = $event.detail.msg; error = !!$event.detail.error; setTimeout(() => show = false, 3000)"
         x-show="show"
         x-cloak
         x-transition
         class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 px-5 py-3 rounded-xl shadow-lg text-sm font-medium text-white"
         :class="error ? 'bg-red-600' : 'bg-slate-900'">
        <span x-text="msg"></span>
    </div>

    <style>
    [x-cloak] { display: none !important; }
    .drag-handle:active { cursor: grabbing; }
    .sortable-ghost { opacity: 0.4; }
    </style>
</x-app-layout>

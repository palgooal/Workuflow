@extends('layouts.app')

@section('title', 'الفئات')

@section('content')
<div class="space-y-6" x-data="categoriesPage()">

    {{-- Header --}}
    <x-page-header title="الفئات" subtitle="تنظيم معاملاتك حسب الفئات">
        <x-slot name="actions">
            <button @click="openCreate('income')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand hover:bg-brand-600
                           text-white text-sm font-semibold rounded-btn transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                فئة جديدة
            </button>
        </x-slot>
    </x-page-header>

    {{-- Stats --}}
    @php
        $totalCount = $income->count() + $expenses->count();
        $totalTx    = $income->sum('transactions_count') + $expenses->sum('transactions_count');
    @endphp
    <x-stat-grid cols="3">
        <x-stats-card title="إجمالي الفئات" color="brand" :value="$totalCount">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
            </x-slot>
        </x-stats-card>

        <x-stats-card title="فئات الدخل" color="green" :value="$income->count()">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                </svg>
            </x-slot>
        </x-stats-card>

        <x-stats-card title="فئات المصروفات" color="red" :value="$expenses->count()">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                </svg>
            </x-slot>
        </x-stats-card>
    </x-stat-grid>

    {{-- Two columns: Income + Expenses --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- ===== Income Categories ===== --}}
        <div class="dash-card overflow-hidden">
            {{-- شريط علوي أخضر --}}
            <div class="h-1 bg-gradient-to-l from-emerald-400 to-green-500" aria-hidden="true"></div>

            <div class="px-5 py-4 border-b border-subtle flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-success-soft flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 text-success-700" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                        </svg>
                    </div>
                    <h2 class="font-bold text-ink text-sm">فئات الدخل</h2>
                    <x-badge color="green">{{ $income->count() }}</x-badge>
                </div>
                <button @click="openCreate('income')"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs text-brand
                               hover:bg-brand-50 border border-brand/20 hover:border-brand/40
                               font-semibold rounded-lg transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    إضافة
                </button>
            </div>

            @if($income->isEmpty())
                <div class="py-12">
                    <x-empty-state
                        title="لا توجد فئات دخل"
                        description="أضف فئة لتصنيف مصادر دخلك"
                        action="#"
                        actionLabel="إضافة فئة دخل"
                        x-on:click.prevent="openCreate('income')" />
                </div>
            @else
                <ul class="divide-y divide-subtle/70">
                    @foreach($income as $category)
                        <li class="relative flex items-center gap-3 px-5 py-3.5 dash-row group">
                            {{-- شريط لوني عند hover --}}
                            <div class="absolute end-0 top-2 bottom-2 w-0.5 rounded-full
                                        opacity-0 group-hover:opacity-100 transition-opacity"
                                 style="background-color: {{ $category->color }}"
                                 aria-hidden="true"></div>

                            {{-- Icon --}}
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 text-lg
                                        ring-1 ring-inset ring-white/50 transition-transform group-hover:scale-105"
                                 style="background-color: {{ $category->color }}20">
                                {{ $category->icon }}
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-ink truncate">{{ $category->name }}</p>
                                <p class="text-xs text-muted flex items-center gap-1 mt-0.5">
                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <span class="nums">{{ $category->transactions_count }}</span> معاملة
                                </p>
                            </div>

                            @if($category->is_default)
                                <x-badge color="gray">افتراضية</x-badge>
                            @endif

                            {{-- Actions --}}
                            <div class="flex items-center gap-1 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                                <button @click="openEdit({{ $category->toJson() }})"
                                        class="row-action hover:text-brand hover:bg-brand-50"
                                        aria-label="تعديل {{ $category->name }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                @if(!$category->is_default)
                                <form method="POST"
                                      action="{{ route('categories.destroy', $category) }}"
                                      onsubmit="return confirm('حذف فئة {{ addslashes($category->name) }}؟')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="row-action hover:text-red-600 hover:bg-red-50"
                                            aria-label="حذف {{ $category->name }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- ===== Expense Categories ===== --}}
        <div class="dash-card overflow-hidden">
            {{-- شريط علوي أحمر --}}
            <div class="h-1 bg-gradient-to-l from-red-400 to-rose-500" aria-hidden="true"></div>

            <div class="px-5 py-4 border-b border-subtle flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-error-soft flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                        </svg>
                    </div>
                    <h2 class="font-bold text-ink text-sm">فئات المصروفات</h2>
                    <x-badge color="red">{{ $expenses->count() }}</x-badge>
                </div>
                <button @click="openCreate('expense')"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs text-brand
                               hover:bg-brand-50 border border-brand/20 hover:border-brand/40
                               font-semibold rounded-lg transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    إضافة
                </button>
            </div>

            @if($expenses->isEmpty())
                <div class="py-12">
                    <x-empty-state
                        title="لا توجد فئات مصروفات"
                        description="أضف فئة لتصنيف مصاريفك"
                        action="#"
                        actionLabel="إضافة فئة مصروف"
                        x-on:click.prevent="openCreate('expense')" />
                </div>
            @else
                <ul class="divide-y divide-subtle/70">
                    @foreach($expenses as $category)
                        <li class="relative flex items-center gap-3 px-5 py-3.5 dash-row group">
                            {{-- شريط لوني عند hover --}}
                            <div class="absolute end-0 top-2 bottom-2 w-0.5 rounded-full
                                        opacity-0 group-hover:opacity-100 transition-opacity"
                                 style="background-color: {{ $category->color }}"
                                 aria-hidden="true"></div>

                            {{-- Icon --}}
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 text-lg
                                        ring-1 ring-inset ring-white/50 transition-transform group-hover:scale-105"
                                 style="background-color: {{ $category->color }}20">
                                {{ $category->icon }}
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-ink truncate">{{ $category->name }}</p>
                                <p class="text-xs text-muted flex items-center gap-1 mt-0.5">
                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <span class="nums">{{ $category->transactions_count }}</span> معاملة
                                </p>
                            </div>

                            @if($category->is_default)
                                <x-badge color="gray">افتراضية</x-badge>
                            @endif

                            {{-- Actions --}}
                            <div class="flex items-center gap-1 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                                <button @click="openEdit({{ $category->toJson() }})"
                                        class="row-action hover:text-brand hover:bg-brand-50"
                                        aria-label="تعديل {{ $category->name }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                @if(!$category->is_default)
                                <form method="POST"
                                      action="{{ route('categories.destroy', $category) }}"
                                      onsubmit="return confirm('حذف فئة {{ addslashes($category->name) }}؟')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="row-action hover:text-red-600 hover:bg-red-50"
                                            aria-label="حذف {{ $category->name }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

    </div>

    {{-- ===== Modal: Create / Edit ===== --}}
    <div x-show="modalOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-modal flex items-center justify-center p-4 bg-ink/40 backdrop-blur-sm"
         style="display:none"
         @click.self="modalOpen = false">

        <div x-show="modalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-surface rounded-2xl shadow-pop w-full max-w-lg">

            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-subtle">
                <h3 class="font-bold text-ink" x-text="editMode ? 'تعديل الفئة' : 'إضافة فئة جديدة'"></h3>
                <button @click="modalOpen = false" class="text-muted hover:text-ink transition-colors"
                        aria-label="إغلاق">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Form --}}
            <form :action="editMode ? '/categories/' + currentId : '{{ route('categories.store') }}'"
                  method="POST" class="p-6 space-y-4">
                @csrf
                <span x-show="editMode" style="display:none">
                    <input type="hidden" name="_method" value="PUT">
                </span>

                {{-- Name --}}
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1.5">
                        اسم الفئة <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" x-model="form.name"
                           placeholder="مثال: راتب، إيجار، طعام..."
                           class="dash-field px-3.5 py-2.5">
                </div>

                {{-- Type --}}
                <div>
                    <label class="block text-sm font-semibold text-ink mb-2">النوع</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="income" x-model="form.type" class="sr-only">
                            <div class="flex items-center gap-2 p-3 rounded-xl border-2 transition-colors"
                                 :class="form.type === 'income' ? 'border-success bg-success-soft' : 'border-subtle'">
                                <svg class="w-4 h-4 text-success-700 shrink-0" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                </svg>
                                <span class="text-sm font-semibold text-ink">دخل</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="expense" x-model="form.type" class="sr-only">
                            <div class="flex items-center gap-2 p-3 rounded-xl border-2 transition-colors"
                                 :class="form.type === 'expense' ? 'border-error bg-error-soft' : 'border-subtle'">
                                <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                                </svg>
                                <span class="text-sm font-semibold text-ink">مصروف</span>
                            </div>
                        </label>
                    </div>
                    <input type="hidden" name="type" :value="form.type">
                </div>

                {{-- Icon Picker --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-semibold text-ink">الأيقونة</label>
                        {{-- معاينة الاختيار الحالي --}}
                        <div class="flex items-center gap-1.5">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-lg transition-all"
                                 :style="`background-color: ${form.color}22`"
                                 x-text="form.icon"></div>
                        </div>
                    </div>
                    <input type="hidden" name="icon" :value="form.icon">
                    <div class="max-h-52 overflow-y-auto rounded-xl border border-subtle p-3 space-y-3 bg-slate-50/50">
                        @foreach($icons as $groupName => $groupIcons)
                        <div>
                            <p class="text-[10px] font-semibold text-muted/70 uppercase tracking-wider mb-1.5">{{ $groupName }}</p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($groupIcons as $icon)
                                <button type="button"
                                        @click="form.icon = '{{ $icon }}'"
                                        class="w-10 h-10 rounded-xl text-xl flex items-center justify-center transition-all duration-150"
                                        :class="form.icon === '{{ $icon }}'
                                            ? 'scale-110 shadow-sm ring-2 ring-offset-1 bg-white'
                                            : 'bg-white hover:bg-slate-100 hover:scale-105'"
                                        :style="form.icon === '{{ $icon }}' ? `ring-color: ${form.color}; outline: 2px solid ${form.color}; outline-offset: 2px; background-color: ${form.color}15` : ''">
                                    {{ $icon }}
                                </button>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Color Picker --}}
                <div>
                    <label class="block text-sm font-semibold text-ink mb-2">اللون</label>
                    <input type="hidden" name="color" :value="form.color">
                    <div class="flex flex-wrap gap-2">
                        @foreach($colors as $color)
                        <button type="button"
                                @click="form.color = '{{ $color }}'"
                                class="w-7 h-7 rounded-lg border-2 transition-all"
                                :class="form.color === '{{ $color }}' ? 'scale-110 border-slate-800' : 'border-transparent'"
                                style="background-color: {{ $color }}">
                        </button>
                        @endforeach
                    </div>
                    {{-- معاينة الفئة الكاملة --}}
                    <div class="mt-3 flex items-center gap-2.5 px-3 py-2.5 rounded-xl bg-slate-50 border border-subtle">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg shrink-0 transition-all"
                             :style="`background-color: ${form.color}20`"
                             x-text="form.icon"></div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-ink truncate"
                               x-text="form.name || 'اسم الفئة'"></p>
                            <p class="text-xs text-muted" x-text="form.type === 'income' ? 'دخل' : 'مصروف'"></p>
                        </div>
                        <div class="w-2.5 h-2.5 rounded-full shrink-0 ms-auto"
                             :style="`background-color: ${form.color}`"></div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-between gap-3 pt-2 border-t border-subtle">
                    <button type="button" @click="modalOpen = false"
                            class="px-4 py-2.5 text-sm font-medium text-muted hover:text-ink border border-subtle
                                   rounded-xl hover:bg-slate-50 transition-colors">
                        إلغاء
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand hover:bg-brand-600
                                   text-white text-sm font-semibold rounded-btn transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span x-text="editMode ? 'حفظ التعديلات' : 'إضافة الفئة'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function categoriesPage() {
    return {
        modalOpen: false,
        editMode: false,
        currentId: null,
        form: {
            name: '',
            type: 'income',
            icon: '💰',
            color: '#6366F1',
        },

        openCreate(type = 'income') {
            this.editMode = false;
            this.currentId = null;
            this.form = { name: '', type: type, icon: type === 'income' ? '💰' : '🛒', color: '#6366F1' };
            this.modalOpen = true;
        },

        openEdit(category) {
            this.editMode = true;
            this.currentId = category.id;
            this.form = {
                name:  category.name,
                type:  category.type,
                icon:  category.icon,
                color: category.color,
            };
            this.modalOpen = true;
        },
    }
}
</script>
@endsection

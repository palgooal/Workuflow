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

    {{-- Two columns: Income + Expenses --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- ===== Income Categories ===== --}}
        <div class="dash-card overflow-hidden">
            <div class="px-5 py-4 border-b border-subtle flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-green-500"></div>
                    <h2 class="font-bold text-ink text-sm">فئات الدخل</h2>
                    <x-badge color="green">{{ $income->count() }}</x-badge>
                </div>
                <button @click="openCreate('income')"
                        class="text-xs text-brand hover:text-brand-700 font-semibold flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    إضافة
                </button>
            </div>

            @if($income->isEmpty())
                <div class="py-10">
                    <x-empty-state title="لا توجد فئات دخل" description="أضف فئة لتصنيف مصادر دخلك" />
                </div>
            @else
                <ul class="divide-y divide-subtle/70">
                    @foreach($income as $category)
                        <li class="flex items-center gap-3 px-5 py-3.5 dash-row group">
                            {{-- Icon + Color --}}
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-lg"
                                 style="background-color: {{ $category->color }}1A">
                                {{ $category->icon }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-ink">{{ $category->name }}</p>
                                <p class="text-xs text-muted nums">{{ $category->transactions_count }} معاملة</p>
                            </div>
                            @if($category->is_default)
                                <x-badge color="gray">افتراضية</x-badge>
                            @endif
                            {{-- Actions --}}
                            <div class="flex items-center gap-1 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                                <button @click="openEdit({{ $category->toJson() }})"
                                        class="row-action hover:text-brand hover:bg-brand-50">
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
                                            class="row-action hover:text-red-600 hover:bg-red-50">
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
            <div class="px-5 py-4 border-b border-subtle flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-red-500"></div>
                    <h2 class="font-bold text-ink text-sm">فئات المصروفات</h2>
                    <x-badge color="red">{{ $expenses->count() }}</x-badge>
                </div>
                <button @click="openCreate('expense')"
                        class="text-xs text-brand hover:text-brand-700 font-semibold flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    إضافة
                </button>
            </div>

            @if($expenses->isEmpty())
                <div class="py-10">
                    <x-empty-state title="لا توجد فئات مصروفات" description="أضف فئة لتصنيف مصاريفك" />
                </div>
            @else
                <ul class="divide-y divide-subtle/70">
                    @foreach($expenses as $category)
                        <li class="flex items-center gap-3 px-5 py-3.5 dash-row group">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-lg"
                                 style="background-color: {{ $category->color }}1A">
                                {{ $category->icon }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-ink">{{ $category->name }}</p>
                                <p class="text-xs text-muted nums">{{ $category->transactions_count }} معاملة</p>
                            </div>
                            @if($category->is_default)
                                <x-badge color="gray">افتراضية</x-badge>
                            @endif
                            <div class="flex items-center gap-1 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                                <button @click="openEdit({{ $category->toJson() }})"
                                        class="row-action hover:text-brand hover:bg-brand-50">
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
                                            class="row-action hover:text-red-600 hover:bg-red-50">
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
             class="bg-surface rounded-2xl shadow-pop w-full max-w-md">

            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-subtle">
                <h3 class="font-bold text-ink" x-text="editMode ? 'تعديل الفئة' : 'إضافة فئة جديدة'"></h3>
                <button @click="modalOpen = false" class="text-muted hover:text-ink transition-colors">
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
                                <span class="text-lg">📈</span>
                                <span class="text-sm font-semibold text-ink">دخل</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="expense" x-model="form.type" class="sr-only">
                            <div class="flex items-center gap-2 p-3 rounded-xl border-2 transition-colors"
                                 :class="form.type === 'expense' ? 'border-error bg-error-soft' : 'border-subtle'">
                                <span class="text-lg">📉</span>
                                <span class="text-sm font-semibold text-ink">مصروف</span>
                            </div>
                        </label>
                    </div>
                    <input type="hidden" name="type" :value="form.type">
                </div>

                {{-- Icon Picker --}}
                <div>
                    <label class="block text-sm font-semibold text-ink mb-2">الأيقونة</label>
                    <input type="hidden" name="icon" :value="form.icon">
                    <div class="flex flex-wrap gap-2 max-h-28 overflow-y-auto p-1">
                        @foreach($icons as $icon)
                        <button type="button"
                                @click="form.icon = '{{ $icon }}'"
                                class="w-9 h-9 rounded-lg text-lg flex items-center justify-center transition"
                                :class="form.icon === '{{ $icon }}'
                                    ? 'bg-brand-100 ring-2 ring-accent/40 scale-110'
                                    : 'bg-slate-50 hover:bg-slate-100'">
                            {{ $icon }}
                        </button>
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
                    {{-- Preview --}}
                    <div class="mt-2 flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg text-base flex items-center justify-center"
                             :style="`background-color: ${form.color}1A`"
                             x-text="form.icon"></div>
                        <span class="text-xs text-muted" x-text="form.name || 'معاينة الفئة'"></span>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-2 border-t border-subtle">
                    <button type="button" @click="modalOpen = false"
                            class="px-4 py-2.5 text-sm font-medium text-muted hover:text-ink transition-colors">
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

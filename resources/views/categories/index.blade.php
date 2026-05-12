@extends('layouts.app')

@section('title', 'الفئات')

@section('content')
<div class="space-y-6" x-data="categoriesPage()">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">الفئات</h1>
            <p class="mt-0.5 text-sm text-gray-500">تنظيم معاملاتك حسب الفئات</p>
        </div>
        <button @click="openCreate('income')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700
                       text-white text-sm font-medium rounded-xl transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            فئة جديدة
        </button>
    </div>

    {{-- Two columns: Income + Expenses --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- ===== Income Categories ===== --}}
        <div class="bg-white rounded-2xl border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-green-500"></div>
                    <h2 class="font-semibold text-gray-900">فئات الدخل</h2>
                    <x-badge color="green">{{ $income->count() }}</x-badge>
                </div>
                <button @click="openCreate('income')"
                        class="text-xs text-indigo-600 hover:text-indigo-700 font-medium flex items-center gap-1">
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
                <ul class="divide-y divide-gray-50">
                    @foreach($income as $category)
                        <li class="flex items-center gap-3 px-5 py-3.5 hover:bg-gray-50 transition group">
                            {{-- Icon + Color --}}
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-lg"
                                 style="background-color: {{ $category->color }}1A">
                                {{ $category->icon }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $category->name }}</p>
                                <p class="text-xs text-gray-400">{{ $category->transactions_count }} معاملة</p>
                            </div>
                            @if($category->is_default)
                                <x-badge color="gray">افتراضية</x-badge>
                            @endif
                            {{-- Actions --}}
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                                <button @click="openEdit({{ $category->toJson() }})"
                                        class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
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
                                            class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
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
        <div class="bg-white rounded-2xl border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-red-500"></div>
                    <h2 class="font-semibold text-gray-900">فئات المصروفات</h2>
                    <x-badge color="red">{{ $expenses->count() }}</x-badge>
                </div>
                <button @click="openCreate('expense')"
                        class="text-xs text-indigo-600 hover:text-indigo-700 font-medium flex items-center gap-1">
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
                <ul class="divide-y divide-gray-50">
                    @foreach($expenses as $category)
                        <li class="flex items-center gap-3 px-5 py-3.5 hover:bg-gray-50 transition group">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-lg"
                                 style="background-color: {{ $category->color }}1A">
                                {{ $category->icon }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $category->name }}</p>
                                <p class="text-xs text-gray-400">{{ $category->transactions_count }} معاملة</p>
                            </div>
                            @if($category->is_default)
                                <x-badge color="gray">افتراضية</x-badge>
                            @endif
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                                <button @click="openEdit({{ $category->toJson() }})"
                                        class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
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
                                            class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
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
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
         style="display:none"
         @click.self="modalOpen = false">

        <div x-show="modalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-2xl shadow-xl w-full max-w-md">

            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900" x-text="editMode ? 'تعديل الفئة' : 'إضافة فئة جديدة'"></h3>
                <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-600 transition">
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
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        اسم الفئة <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" x-model="form.name"
                           placeholder="مثال: راتب، إيجار، طعام..."
                           class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                {{-- Type --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">النوع</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="income" x-model="form.type" class="sr-only">
                            <div class="flex items-center gap-2 p-3 rounded-xl border-2 transition"
                                 :class="form.type === 'income' ? 'border-green-500 bg-green-50' : 'border-gray-200'">
                                <span class="text-lg">📈</span>
                                <span class="text-sm font-medium text-gray-900">دخل</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="expense" x-model="form.type" class="sr-only">
                            <div class="flex items-center gap-2 p-3 rounded-xl border-2 transition"
                                 :class="form.type === 'expense' ? 'border-red-500 bg-red-50' : 'border-gray-200'">
                                <span class="text-lg">📉</span>
                                <span class="text-sm font-medium text-gray-900">مصروف</span>
                            </div>
                        </label>
                    </div>
                    <input type="hidden" name="type" :value="form.type">
                </div>

                {{-- Icon Picker --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الأيقونة</label>
                    <input type="hidden" name="icon" :value="form.icon">
                    <div class="flex flex-wrap gap-2 max-h-28 overflow-y-auto p-1">
                        @foreach($icons as $icon)
                        <button type="button"
                                @click="form.icon = '{{ $icon }}'"
                                class="w-9 h-9 rounded-lg text-lg flex items-center justify-center transition"
                                :class="form.icon === '{{ $icon }}'
                                    ? 'bg-indigo-100 ring-2 ring-indigo-500 scale-110'
                                    : 'bg-gray-50 hover:bg-gray-100'">
                            {{ $icon }}
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- Color Picker --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">اللون</label>
                    <input type="hidden" name="color" :value="form.color">
                    <div class="flex flex-wrap gap-2">
                        @foreach($colors as $color)
                        <button type="button"
                                @click="form.color = '{{ $color }}'"
                                class="w-7 h-7 rounded-lg border-2 transition-all"
                                :class="form.color === '{{ $color }}' ? 'scale-110 border-gray-800' : 'border-transparent'"
                                style="background-color: {{ $color }}">
                        </button>
                        @endforeach
                    </div>
                    {{-- Preview --}}
                    <div class="mt-2 flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg text-base flex items-center justify-center"
                             :style="`background-color: ${form.color}1A`"
                             x-text="form.icon"></div>
                        <span class="text-xs text-gray-500" x-text="form.name || 'معاينة الفئة'"></span>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                    <button type="button" @click="modalOpen = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition">
                        إلغاء
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700
                                   text-white text-sm font-medium rounded-xl transition">
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

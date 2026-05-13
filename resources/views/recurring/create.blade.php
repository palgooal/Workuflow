@extends('layouts.app')

@section('title', 'التزام متكرر جديد')

@section('content')
<div class="max-w-xl mx-auto" x-data="{
    type: 'expense',
    hasEndDate: false,
}">

    <div class="mb-6">
        <a href="{{ route('recurring.index') }}"
           class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            الالتزامات المتكررة
        </a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-2">التزام متكرر جديد</h1>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
        <form method="POST" action="{{ route('recurring.store') }}" class="space-y-5">
            @csrf

            {{-- النوع --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">النوع</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition"
                           :class="type === 'expense'
                               ? 'border-red-400 bg-red-50 dark:bg-red-900/20'
                               : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800'">
                        <input type="radio" name="type" value="expense" x-model="type" class="accent-red-500">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">مصروف</p>
                            <p class="text-xs text-gray-500">إيجار، اشتراك، ...</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition"
                           :class="type === 'income'
                               ? 'border-emerald-400 bg-emerald-50 dark:bg-emerald-900/20'
                               : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800'">
                        <input type="radio" name="type" value="income" x-model="type" class="accent-emerald-500">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">دخل</p>
                            <p class="text-xs text-gray-500">راتب، عائد، ...</p>
                        </div>
                    </label>
                </div>
                @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- الوصف --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">الوصف</label>
                <input type="text" name="description"
                       value="{{ old('description') }}"
                       placeholder="مثال: إيجار المكتب"
                       class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 @error('description') border-red-500 @enderror">
                @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- المبلغ --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">المبلغ</label>
                <input type="number" name="amount" step="0.01" min="0.01"
                       value="{{ old('amount') }}"
                       placeholder="0.00"
                       class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 @error('amount') border-red-500 @enderror">
                @error('amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- التكرار --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">التكرار</label>
                <select name="frequency"
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    @foreach($frequencies as $val => $label)
                        <option value="{{ $val }}" @selected(old('frequency', 'monthly') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- تاريخ البداية --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">تاريخ البداية</label>
                <input type="date" name="start_date"
                       value="{{ old('start_date', today()->toDateString()) }}"
                       class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 @error('start_date') border-red-500 @enderror">
                @error('start_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- تاريخ الانتهاء (اختياري) --}}
            <div>
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" x-model="hasEndDate" class="rounded accent-indigo-600">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">تحديد تاريخ انتهاء</span>
                </label>
                <div x-show="hasEndDate" x-transition class="mt-2">
                    <input type="date" name="end_date"
                           value="{{ old('end_date') }}"
                           class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 @error('end_date') border-red-500 @enderror">
                    @error('end_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- الفئة --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    الفئة <span class="text-gray-400">(اختياري)</span>
                </label>
                <select name="category_id"
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    <option value="">— بدون فئة —</option>
                    @foreach($categories->groupBy('type') as $catType => $cats)
                        <optgroup label="{{ $catType === 'expense' ? 'مصروفات' : 'دخل' }}">
                            @foreach($cats as $cat)
                                <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>

            {{-- المشروع --}}
            @if($projects->isNotEmpty())
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    المشروع <span class="text-gray-400">(اختياري)</span>
                </label>
                <select name="project_id"
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    <option value="">— كل المشاريع —</option>
                    @foreach($projects as $proj)
                        <option value="{{ $proj->id }}" @selected(old('project_id') == $proj->id)>{{ $proj->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Submit --}}
            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="flex-1 py-2.5 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition text-sm">
                    إنشاء الالتزام
                </button>
                <a href="{{ route('recurring.index') }}"
                   class="px-6 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-xl font-medium hover:bg-gray-200 dark:hover:bg-gray-700 transition text-sm">
                    إلغاء
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

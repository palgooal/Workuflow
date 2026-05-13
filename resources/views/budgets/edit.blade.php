@extends('layouts.app')

@section('title', 'تعديل الميزانية')

@section('content')
<div class="max-w-xl mx-auto" x-data="{
    period: '{{ $budget->period }}',
    month: {{ $budget->month ?? 'null' }},
    year: {{ $budget->year }},
}">

    <div class="mb-6">
        <a href="{{ route('budget.index') }}"
           class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            الميزانيات
        </a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-2">تعديل الميزانية</h1>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
        <form method="POST" action="{{ route('budget.update', $budget) }}" class="space-y-5">
            @csrf @method('PUT')

            @if($errors->has('duplicate'))
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 px-4 py-3 rounded-xl text-sm">
                    {{ $errors->first('duplicate') }}
                </div>
            @endif

            {{-- المبلغ --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">المبلغ المخصص</label>
                <input type="number" name="amount" step="0.01" min="1"
                       value="{{ old('amount', $budget->amount) }}"
                       class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 @error('amount') border-red-500 @enderror">
                @error('amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- الفترة --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">الفترة</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition"
                           :class="period === 'monthly'
                               ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20'
                               : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800'">
                        <input type="radio" name="period" value="monthly" x-model="period" class="accent-indigo-600">
                        <span class="text-sm font-medium text-gray-900 dark:text-white">شهرية</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition"
                           :class="period === 'yearly'
                               ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20'
                               : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800'">
                        <input type="radio" name="period" value="yearly" x-model="period" class="accent-indigo-600">
                        <span class="text-sm font-medium text-gray-900 dark:text-white">سنوية</span>
                    </label>
                </div>
            </div>

            {{-- الشهر والسنة --}}
            <div class="grid grid-cols-2 gap-4">
                <div x-show="period === 'monthly'" x-transition>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">الشهر</label>
                    <select name="month"
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        @foreach($months as $num => $name)
                            <option value="{{ $num }}" @selected(old('month', $budget->month) == $num)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div :class="period === 'yearly' ? 'col-span-2' : ''">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">السنة</label>
                    <select name="year"
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        @foreach($years as $y)
                            <option value="{{ $y }}" @selected(old('year', $budget->year) == $y)>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- الفئة --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">الفئة</label>
                <select name="category_id"
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    <option value="">— عام —</option>
                    @foreach($categories->groupBy('type') as $type => $cats)
                        <optgroup label="{{ $type === 'expense' ? 'مصروفات' : 'دخل' }}">
                            @foreach($cats as $cat)
                                <option value="{{ $cat->id }}" @selected(old('category_id', $budget->category_id) == $cat->id)>
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
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">المشروع</label>
                <select name="project_id"
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    <option value="">— كل المشاريع —</option>
                    @foreach($projects as $proj)
                        <option value="{{ $proj->id }}" @selected(old('project_id', $budget->project_id) == $proj->id)>{{ $proj->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Submit --}}
            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="flex-1 py-2.5 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition text-sm">
                    حفظ التعديلات
                </button>
                <a href="{{ route('budget.index') }}"
                   class="px-6 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-xl font-medium hover:bg-gray-200 dark:hover:bg-gray-700 transition text-sm">
                    إلغاء
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@extends('layouts.app')
@section('title', 'صندوق جديد')
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">

        <h2 class="text-lg font-bold text-gray-900 mb-6">إنشاء صندوق جديد</h2>

        <form method="POST" action="{{ route('wallets.store') }}" class="space-y-5">
            @csrf

            {{-- الاسم --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">اسم الصندوق <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       placeholder="مثال: كاش يد، حساب الراجحي..."
                       class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-400 @enderror">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- النوع --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">النوع <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-3 gap-3">
                    @foreach($types as $type)
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="{{ $type->value }}"
                               {{ old('type', 'cash') === $type->value ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="border-2 rounded-xl p-3 text-center transition
                                    peer-checked:border-indigo-500 peer-checked:bg-indigo-50 border-gray-200 hover:border-gray-300">
                            <div class="text-2xl mb-1">{{ $type->icon() }}</div>
                            <div class="text-xs font-medium text-gray-700">{{ $type->label() }}</div>
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- العملة + الرصيد الافتراضي --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">العملة <span class="text-red-500">*</span></label>
                    <select name="currency" required
                            class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach($currencies as $code => $label)
                            <option value="{{ $code }}"
                                {{ old('currency', auth()->user()->currency ?? 'SAR') === $code ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الرصيد الافتتاحي</label>
                    <input type="number" name="initial_balance" value="{{ old('initial_balance', 0) }}"
                           step="0.01" min="0"
                           class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            {{-- اللون + الأيقونة --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">اللون</label>
                    <input type="color" name="color" value="{{ old('color', '#6366f1') }}"
                           class="w-full h-10 rounded-xl border-gray-200 cursor-pointer">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">أيقونة (اختياري)</label>
                    <input type="text" name="icon" value="{{ old('icon') }}"
                           placeholder="💰 🏦 💳 ..."
                           class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            {{-- الوصف --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">وصف (اختياري)</label>
                <textarea name="description" rows="2" placeholder="ملاحظات..."
                          class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('description') }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="flex-1 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition">
                    إنشاء الصندوق
                </button>
                <a href="{{ route('wallets.index') }}"
                   class="flex-1 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium text-center hover:bg-gray-200 transition">
                    إلغاء
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

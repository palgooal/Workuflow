@extends('layouts.app')
@section('title', 'تحويل بين الصناديق')
@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">

        <h2 class="text-lg font-bold text-gray-900 mb-6">تحويل بين الصناديق</h2>

        @if($wallets->count() < 2)
            <div class="text-center py-8 text-gray-400">
                <p class="text-4xl mb-3">🏦</p>
                <p class="text-sm">تحتاج إلى صندوقين على الأقل للتحويل.</p>
                <a href="{{ route('wallets.create') }}" class="mt-4 inline-block text-indigo-600 text-sm font-medium">+ أضف صندوقاً</a>
            </div>
        @else
        <form method="POST" action="{{ route('wallets.transfer.store') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">من <span class="text-red-500">*</span></label>
                    <select name="from_wallet_id" required
                            class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('from_wallet_id') border-red-400 @enderror">
                        <option value="">اختر...</option>
                        @foreach($wallets as $w)
                            <option value="{{ $w->id }}" {{ old('from_wallet_id') === $w->id ? 'selected' : '' }}>
                                {{ $w->icon ?: $w->type->icon() }} {{ $w->name }} ({{ $w->currency }})
                            </option>
                        @endforeach
                    </select>
                    @error('from_wallet_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">إلى <span class="text-red-500">*</span></label>
                    <select name="to_wallet_id" required
                            class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('to_wallet_id') border-red-400 @enderror">
                        <option value="">اختر...</option>
                        @foreach($wallets as $w)
                            <option value="{{ $w->id }}" {{ old('to_wallet_id') === $w->id ? 'selected' : '' }}>
                                {{ $w->icon ?: $w->type->icon() }} {{ $w->name }} ({{ $w->currency }})
                            </option>
                        @endforeach
                    </select>
                    @error('to_wallet_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">المبلغ <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" value="{{ old('amount') }}"
                           step="0.01" min="0.01" required
                           class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('amount') border-red-400 @enderror">
                    @error('amount')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">رسوم التحويل</label>
                    <input type="number" name="fee" value="{{ old('fee', 0) }}"
                           step="0.01" min="0"
                           class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ التحويل <span class="text-red-500">*</span></label>
                <input type="date" name="transferred_at"
                       value="{{ old('transferred_at', now()->toDateString()) }}" required
                       class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الوصف</label>
                    <input type="text" name="description" value="{{ old('description') }}"
                           placeholder="سبب التحويل..."
                           class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">رقم المرجع</label>
                    <input type="text" name="reference" value="{{ old('reference') }}"
                           placeholder="اختياري..."
                           class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="flex-1 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition">
                    تنفيذ التحويل
                </button>
                <a href="{{ route('wallets.index') }}"
                   class="flex-1 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium text-center hover:bg-gray-200 transition">
                    إلغاء
                </a>
            </div>
        </form>
        @endif
    </div>
</div>
@endsection

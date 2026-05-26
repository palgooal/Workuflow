@extends('layouts.app')

@section('title', 'تعديل بيانات ' . $client->name)

@section('content')
<div class="max-w-3xl mx-auto space-y-5">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('clients.show', $client->public_id) }}"
           class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">تعديل: {{ $client->name }}</h1>
            <p class="text-sm text-gray-500">تحديث بيانات العميل</p>
        </div>
    </div>

    <form id="edit-form" method="POST" action="{{ route('clients.update', $client->public_id) }}" class="space-y-5">
        @csrf
        @method('PUT')

        {{-- البيانات الأساسية --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5 space-y-4">
            <h2 class="text-sm font-semibold text-gray-700">البيانات الأساسية</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">الاسم الكامل <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $client->name) }}" required
                           class="w-full px-4 py-2.5 text-sm border rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none
                                  {{ $errors->has('name') ? 'border-red-400' : 'border-gray-200' }}">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email', $client->email) }}"
                           class="w-full px-4 py-2.5 text-sm border rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none
                                  {{ $errors->has('email') ? 'border-red-400' : 'border-gray-200' }}">
                    @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الهاتف</label>
                    <input type="text" name="phone" value="{{ old('phone', $client->phone) }}"
                           class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الشركة</label>
                    <input type="text" name="company" value="{{ old('company', $client->company) }}"
                           class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">المنصب</label>
                    <input type="text" name="position" value="{{ old('position', $client->position) }}"
                           class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
            </div>
        </div>

        {{-- التصنيف --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5 space-y-4">
            <h2 class="text-sm font-semibold text-gray-700">التصنيف والمصدر</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الحالة</label>
                    <select name="status"
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                        <option value="prospect" {{ old('status', $client->status) === 'prospect' ? 'selected' : '' }}>⭐ عميل محتمل</option>
                        <option value="active"   {{ old('status', $client->status) === 'active'   ? 'selected' : '' }}>✅ نشط</option>
                        <option value="inactive" {{ old('status', $client->status) === 'inactive' ? 'selected' : '' }}>⏸ غير نشط</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">مصدر الاكتساب</label>
                    <select name="source"
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                        <option value="">غير محدد</option>
                        <option value="direct"       {{ old('source', $client->source) === 'direct'       ? 'selected' : '' }}>🤝 مباشر</option>
                        <option value="referral"     {{ old('source', $client->source) === 'referral'     ? 'selected' : '' }}>📢 إحالة</option>
                        <option value="social_media" {{ old('source', $client->source) === 'social_media' ? 'selected' : '' }}>📱 وسائل التواصل</option>
                        <option value="website"      {{ old('source', $client->source) === 'website'      ? 'selected' : '' }}>🌐 الموقع الإلكتروني</option>
                        <option value="other"        {{ old('source', $client->source) === 'other'        ? 'selected' : '' }}>📌 أخرى</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- الوسوم --}}
        @if($tags->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-100 p-5 space-y-3">
            <h2 class="text-sm font-semibold text-gray-700">الوسوم</h2>
            <div class="flex flex-wrap gap-2">
                @php $currentTagIds = old('tag_ids', $client->tags->pluck('id')->toArray()) @endphp
                @foreach($tags as $tag)
                <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}"
                           {{ in_array($tag->id, $currentTagIds) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium text-white"
                          style="background-color: {{ $tag->color ?? '#6366f1' }}">
                        @if($tag->icon){{ $tag->icon }} @endif{{ $tag->name }}
                    </span>
                </label>
                @endforeach
            </div>
        </div>
        @endif

        {{-- معلومات تكميلية --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5 space-y-4">
            <h2 class="text-sm font-semibold text-gray-700">معلومات تكميلية</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الموقع الإلكتروني</label>
                    <input type="url" name="website" value="{{ old('website', $client->website) }}"
                           class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">المدينة</label>
                    <input type="text" name="city" value="{{ old('city', $client->city) }}"
                           class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الدولة</label>
                    <input type="text" name="country" value="{{ old('country', $client->country ?? 'PS') }}"
                           class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
                <textarea name="notes" rows="3"
                          class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none resize-none">{{ old('notes', $client->notes) }}</textarea>
            </div>
        </div>

        {{-- أزرار التعديل --}}
        <div class="flex items-center justify-between">
            {{-- زر الحذف: يُرسل فورم الحذف المنفصل خارج الـ form --}}
            @can('delete', $client)
            <button type="submit" form="delete-form"
                    onclick="return confirm('هل أنت متأكد من حذف هذا العميل نهائياً؟')"
                    class="px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 border border-red-200
                           rounded-xl transition">
                حذف العميل
            </button>
            @else <div></div> @endcan

            <div class="flex gap-3">
                <a href="{{ route('clients.show', $client->public_id) }}"
                   class="px-4 py-2.5 text-sm text-gray-600 bg-white border border-gray-200
                          rounded-xl hover:bg-gray-50 transition">
                    إلغاء
                </a>
                <button type="submit" form="edit-form"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm
                               font-medium rounded-xl transition">
                    حفظ التعديلات
                </button>
            </div>
        </div>

    </form>

    {{-- فورم الحذف منفصل تماماً خارج فورم التعديل --}}
    @can('delete', $client)
    <form id="delete-form" method="POST" action="{{ route('clients.destroy', $client->public_id) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
    @endcan

</div>
@endsection

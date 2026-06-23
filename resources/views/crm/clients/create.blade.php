@extends('layouts.app')

@section('title', 'إضافة عميل جديد')

@section('content')
<div class="max-w-3xl mx-auto space-y-5">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('clients.index') }}"
           class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-ink tracking-tight">إضافة عميل جديد</h1>
            <p class="text-sm text-slate-500">أدخل بيانات العميل لإضافته إلى قاعدة عملائك</p>
        </div>
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('clients.store') }}" class="space-y-5">
        @csrf

        {{-- البيانات الأساسية --}}
        <div class="dash-card p-5 space-y-4">
            <h2 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                البيانات الأساسية
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- الاسم --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-ink mb-1">
                        الاسم الكامل <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           placeholder="مثال: أحمد محمد"
                           class="w-full px-4 py-2.5 text-sm border rounded-xl focus:ring-2 focus:ring-accent/40 outline-none
                                  {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-slate-200' }}">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- البريد --}}
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           placeholder="ahmed@example.com"
                           class="w-full px-4 py-2.5 text-sm border rounded-xl focus:ring-2 focus:ring-accent/40 outline-none
                                  {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-slate-200' }}">
                    @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- الهاتف --}}
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1">رقم الهاتف</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           placeholder="+970501234567"
                           class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl
                                  focus:ring-2 focus:ring-accent/40 outline-none">
                    @error('phone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- الشركة --}}
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1">اسم الشركة</label>
                    <input type="text" name="company" value="{{ old('company') }}"
                           placeholder="اسم الشركة أو المؤسسة"
                           class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl
                                  focus:ring-2 focus:ring-accent/40 outline-none">
                </div>

                {{-- المنصب --}}
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1">المنصب / الوظيفة</label>
                    <input type="text" name="position" value="{{ old('position') }}"
                           placeholder="مثال: مدير تقني"
                           class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl
                                  focus:ring-2 focus:ring-accent/40 outline-none">
                </div>
            </div>
        </div>

        {{-- الحالة والمصدر --}}
        <div class="dash-card p-5 space-y-4">
            <h2 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                التصنيف والمصدر
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- الحالة --}}
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1">
                        الحالة <span class="text-red-500">*</span>
                    </label>
                    <select name="status" required
                            class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl
                                   focus:ring-2 focus:ring-accent/40 outline-none bg-white">
                        <option value="prospect" {{ old('status', 'prospect') === 'prospect' ? 'selected' : '' }}>⭐ عميل محتمل</option>
                        <option value="active"   {{ old('status') === 'active'               ? 'selected' : '' }}>✅ نشط</option>
                        <option value="inactive" {{ old('status') === 'inactive'             ? 'selected' : '' }}>⏸ غير نشط</option>
                    </select>
                    @error('status') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- المصدر --}}
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1">مصدر الاكتساب</label>
                    <select name="source"
                            class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl
                                   focus:ring-2 focus:ring-accent/40 outline-none bg-white">
                        <option value="">غير محدد</option>
                        <option value="direct"      {{ old('source') === 'direct'      ? 'selected' : '' }}>🤝 مباشر</option>
                        <option value="referral"    {{ old('source') === 'referral'    ? 'selected' : '' }}>📢 إحالة</option>
                        <option value="social_media"{{ old('source') === 'social_media'? 'selected' : '' }}>📱 وسائل التواصل</option>
                        <option value="website"     {{ old('source') === 'website'     ? 'selected' : '' }}>🌐 الموقع الإلكتروني</option>
                        <option value="other"       {{ old('source') === 'other'       ? 'selected' : '' }}>📌 أخرى</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- الوسوم --}}
        @if($tags->isNotEmpty())
        <div class="dash-card p-5 space-y-3">
            <h2 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                الوسوم
            </h2>
            <div class="flex flex-wrap gap-2">
                @foreach($tags as $tag)
                <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}"
                           {{ in_array($tag->id, old('tag_ids', [])) ? 'checked' : '' }}
                           class="rounded border-slate-300 text-brand focus:ring-accent/40">
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
        <div class="dash-card p-5 space-y-4">
            <h2 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                معلومات تكميلية
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1">الموقع الإلكتروني</label>
                    <input type="url" name="website" value="{{ old('website') }}"
                           placeholder="https://example.com"
                           class="w-full px-4 py-2.5 text-sm border rounded-xl focus:ring-2 focus:ring-accent/40 outline-none
                                  {{ $errors->has('website') ? 'border-red-400 bg-red-50' : 'border-slate-200' }}">
                    @error('website') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1">المدينة</label>
                    <input type="text" name="city" value="{{ old('city') }}"
                           placeholder="المدينة"
                           class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl
                                  focus:ring-2 focus:ring-accent/40 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1">الدولة <span class="text-slate-400 text-xs">(رمز دولة مكوّن من حرفين: PS، IL…)</span></label>
                    <input type="text" name="country" value="{{ old('country', 'PS') }}"
                           placeholder="PS" maxlength="2"
                           class="w-full px-4 py-2.5 text-sm border rounded-xl focus:ring-2 focus:ring-accent/40 outline-none
                                  {{ $errors->has('country') ? 'border-red-400 bg-red-50' : 'border-slate-200' }}">
                    @error('country') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-ink mb-1">ملاحظات</label>
                <textarea name="notes" rows="3" placeholder="أي ملاحظات إضافية عن العميل…"
                          class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl
                                 focus:ring-2 focus:ring-accent/40 outline-none resize-none">{{ old('notes') }}</textarea>
            </div>
        </div>

        {{-- أزرار الحفظ --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('clients.index') }}"
               class="px-4 py-2.5 text-sm text-slate-600 bg-white border border-slate-200
                      rounded-xl hover:bg-slate-50 transition">
                إلغاء
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-brand hover:bg-brand-600 text-white text-sm
                           font-medium rounded-xl transition">
                إضافة العميل
            </button>
        </div>

    </form>

</div>
@endsection

@extends('layouts.app')
@section('title', 'الشرائح وصحة العملاء')
@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-bold text-gray-900">الشرائح وصحة العملاء</h1>
    <a href="{{ route('clients.index') }}"
       class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        العملاء
    </a>
</div>

    {{-- ==================== TOAST ==================== --}}
    <div
        x-data="{ show: false, message: '', type: 'success' }"
        x-on:show-toast.window="show = true; message = $event.detail.message; type = $event.detail.type || 'success'; setTimeout(() => show = false, 3500)"
        x-show="show"
        x-transition.opacity
        class="fixed top-5 left-1/2 -translate-x-1/2 z-50 px-5 py-3 rounded-xl shadow-lg text-white text-sm font-medium pointer-events-none"
        :class="type === 'success' ? 'bg-emerald-600' : 'bg-red-600'"
        style="display:none"
    >
        <span x-text="message"></span>
    </div>

    {{-- ==================== SAVE MODAL ==================== --}}
    <div
        x-data="saveModal()"
        @open-save-modal.window="open = true; name = ''; pinned = false"
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
        @click.self="open = false"
        style="display:none"
    >
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" @click.stop>
            <h3 class="text-lg font-bold text-gray-800 mb-4">💾 حفظ الشريحة</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">اسم الشريحة</label>
                    <input type="text" x-model="name" placeholder="مثال: عملاء VIP نشطون"
                           @keydown.enter="submit()"
                           class="w-full border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                           maxlength="80">
                </div>
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input type="checkbox" x-model="pinned"
                           class="w-4 h-4 rounded text-indigo-600 border-gray-300 focus:ring-indigo-500">
                    <span class="text-sm text-gray-700">📌 تثبيت في القائمة</span>
                </label>
            </div>
            <div class="mt-6 flex items-center gap-3 justify-end">
                <button @click="open = false"
                        class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                    إلغاء
                </button>
                <button @click="submit()"
                        :disabled="!name.trim() || saving"
                        class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition disabled:opacity-50">
                    <span x-text="saving ? 'جاري الحفظ...' : 'حفظ'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ==================== MAIN PAGE ==================== --}}
    <div class="py-8" x-data="segmentPage()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- TABS --}}
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex gap-6">
                    <button @click="tab = 'builder'"
                            :class="tab === 'builder' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition">
                        🔍 بناء الشرائح
                    </button>
                    <button @click="tab = 'health'"
                            :class="tab === 'health' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition">
                        💯 صحة العملاء
                    </button>
                </nav>
            </div>

            {{-- ==================== BUILDER TAB ==================== --}}
            <div x-show="tab === 'builder'" x-transition>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- Builder Panel --}}
                    <div class="lg:col-span-2 space-y-4">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h3 class="text-base font-semibold text-gray-800 mb-4">بناء فلتر جديد</h3>

                            {{-- Filter Rows --}}
                            <div class="space-y-3">
                                <template x-for="(filter, index) in filters" :key="index">
                                    <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg border border-gray-200">

                                        <select x-model="filter.field"
                                                @change="filter.value = ''"
                                                class="text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="">— اختر حقلاً —</option>
                                            <option value="status">الحالة</option>
                                            <option value="source">المصدر</option>
                                            <option value="health_min">الصحة (من)</option>
                                            <option value="health_max">الصحة (إلى)</option>
                                            <option value="tag_ids">الوسم</option>
                                            <option value="has_follow_up">متابعة معلقة</option>
                                            <option value="search">بحث نصي</option>
                                        </select>

                                        <div class="flex-1">
                                            <template x-if="filter.field === 'status'">
                                                <select x-model="filter.value" class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                                    <option value="">— اختر —</option>
                                                    @foreach($statuses as $s)
                                                        <option value="{{ $s->value }}">{{ $s->label() }}</option>
                                                    @endforeach
                                                </select>
                                            </template>

                                            <template x-if="filter.field === 'source'">
                                                <select x-model="filter.value" class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                                    <option value="">— اختر —</option>
                                                    @foreach($sources as $src)
                                                        <option value="{{ $src->value }}">{{ $src->label() }}</option>
                                                    @endforeach
                                                </select>
                                            </template>

                                            <template x-if="filter.field === 'health_min' || filter.field === 'health_max'">
                                                <div class="flex items-center gap-2">
                                                    <input type="number" x-model="filter.value" min="0" max="100" placeholder="0–100"
                                                           class="w-24 text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                                    <span class="text-xs text-gray-400">/ 100</span>
                                                </div>
                                            </template>

                                            <template x-if="filter.field === 'tag_ids'">
                                                <select x-model="filter.value" class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                                    <option value="">— اختر وسماً —</option>
                                                    @foreach($tags as $tag)
                                                        <option value="{{ $tag->id }}">{{ $tag->icon ?? '' }} {{ $tag->name }}</option>
                                                    @endforeach
                                                </select>
                                            </template>

                                            <template x-if="filter.field === 'has_follow_up'">
                                                <select x-model="filter.value" class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                                    <option value="">— اختر —</option>
                                                    <option value="1">نعم — لديه متابعة معلقة</option>
                                                    <option value="0">لا — بدون متابعة معلقة</option>
                                                </select>
                                            </template>

                                            <template x-if="filter.field === 'search'">
                                                <input type="text" x-model="filter.value" placeholder="ابحث بالاسم أو الشركة..."
                                                       class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                            </template>

                                            <template x-if="!filter.field">
                                                <div class="text-xs text-gray-400 py-2 px-1">اختر حقلاً أولاً</div>
                                            </template>
                                        </div>

                                        <button @click="removeFilter(index)"
                                                class="text-red-400 hover:text-red-600 p-1 rounded transition shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </template>

                                <div x-show="filters.length === 0" class="text-sm text-gray-400 py-4 text-center border-2 border-dashed border-gray-200 rounded-lg">
                                    انقر «إضافة شرط» لبدء بناء الفلتر
                                </div>
                            </div>

                            <button @click="filters.push({ field: '', value: '' }); previewCount = null"
                                    class="mt-3 flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-800 font-medium transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                إضافة شرط
                            </button>

                            {{-- Action Buttons --}}
                            <div class="mt-5 pt-5 border-t border-gray-100 flex flex-wrap items-center gap-3">

                                <button @click="previewResults()"
                                        :disabled="filters.length === 0 || previewing"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition disabled:opacity-50">
                                    <template x-if="!previewing">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </template>
                                    <template x-if="previewing">
                                        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8"/>
                                        </svg>
                                    </template>
                                    <span x-text="previewing ? 'جاري الحساب...' : 'معاينة'"></span>
                                </button>

                                <div x-show="previewCount !== null"
                                     x-transition
                                     class="px-4 py-2 bg-indigo-50 text-indigo-700 text-sm font-semibold rounded-lg">
                                    <span x-text="previewCount + ' عميل مطابق'"></span>
                                </div>

                                <div class="flex-1"></div>

                                <button @click="$dispatch('open-save-modal')"
                                        :disabled="filters.length === 0"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition disabled:opacity-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                    </svg>
                                    حفظ الشريحة
                                </button>

                                <button @click="applyToList()"
                                        :disabled="filters.length === 0"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition disabled:opacity-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                                    </svg>
                                    تطبيق على القائمة
                                </button>
                            </div>
                        </div>

                        {{-- Preview Results List --}}
                        <div x-show="previewClients.length > 0" x-transition class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                نتائج المعاينة
                                <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-2 py-0.5 rounded-full" x-text="previewCount"></span>
                            </h4>
                            <div class="divide-y divide-gray-100">
                                <template x-for="client in previewClients" :key="client.id">
                                    <div class="flex items-center justify-between py-2.5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold"
                                                 x-text="client.name ? client.name[0].toUpperCase() : '?'"></div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-800" x-text="client.name"></div>
                                                <div class="text-xs text-gray-400" x-text="client.company || ''"></div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <template x-if="client.health_score !== null && client.health_score !== undefined">
                                                <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                                      :class="client.health_score >= 80 ? 'bg-emerald-100 text-emerald-700' :
                                                              client.health_score >= 60 ? 'bg-blue-100 text-blue-700' :
                                                              client.health_score >= 40 ? 'bg-amber-100 text-amber-700' :
                                                              'bg-red-100 text-red-700'"
                                                      x-text="client.health_score + '/100'"></span>
                                            </template>
                                            <a :href="'{{ url('/clients') }}/' + client.public_id"
                                               class="text-xs text-indigo-600 hover:underline">عرض</a>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div x-show="previewCount > previewClients.length" class="mt-3 text-xs text-gray-400 text-center">
                                + <span x-text="previewCount - previewClients.length"></span> عميل آخر — انقر «تطبيق على القائمة» لعرض الجميع
                            </div>
                        </div>
                    </div>

                    {{-- Saved Segments Sidebar --}}
                    <div class="space-y-4">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                            <h3 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                </svg>
                                الشرائح المحفوظة
                                <span class="mr-auto bg-indigo-50 text-indigo-600 text-xs font-semibold px-2 py-0.5 rounded-full">
                                    {{ $segments->count() }}
                                </span>
                            </h3>

                            @if($segments->isEmpty())
                                <div class="text-center py-8 text-gray-400">
                                    <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                    <p class="text-sm">لا توجد شرائح محفوظة بعد</p>
                                    <p class="text-xs mt-1">ابنِ فلتراً وانقر «حفظ الشريحة»</p>
                                </div>
                            @else
                                <div class="space-y-2">
                                    @foreach($segments as $seg)
                                        <div class="group flex items-center gap-2 p-3 rounded-lg border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50 transition"
                                             x-data="{ removing: false }">

                                            {{-- Pin --}}
                                            <button @click="$dispatch('pin-segment', { id: '{{ $seg->id }}', pinned: {{ $seg->is_pinned ? 'false' : 'true' }} })"
                                                    class="shrink-0 transition"
                                                    title="{{ $seg->is_pinned ? 'إلغاء التثبيت' : 'تثبيت' }}">
                                                @if($seg->is_pinned)
                                                    <svg class="w-4 h-4 text-amber-500" viewBox="0 0 24 24" fill="currentColor">
                                                        <path d="M16 12V4h1V2H7v2h1v8l-2 2v2h5.2v6h1.6v-6H18v-2l-2-2z"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-400" viewBox="0 0 24 24" fill="currentColor">
                                                        <path d="M16 12V4h1V2H7v2h1v8l-2 2v2h5.2v6h1.6v-6H18v-2l-2-2z"/>
                                                    </svg>
                                                @endif
                                            </button>

                                            {{-- Info --}}
                                            <div class="flex-1 min-w-0">
                                                <div class="text-sm font-medium text-gray-800 truncate">{{ $seg->name }}</div>
                                                <div class="text-xs text-gray-400">
                                                    @if($seg->client_count !== null)
                                                        {{ $seg->client_count }} عميل
                                                        @if($seg->last_executed_at)
                                                            · {{ $seg->last_executed_at->diffForHumans() }}
                                                        @endif
                                                    @else
                                                        {{ count($seg->filters ?? []) }} شرط
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- Action Buttons --}}
                                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition shrink-0">
                                                <button @click="$dispatch('load-segment', { filters: {{ json_encode($seg->filters ?? new stdClass) }} })"
                                                        title="تحميل الفلاتر"
                                                        class="p-1 text-indigo-500 hover:text-indigo-700 rounded">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                                    </svg>
                                                </button>
                                                <button @click="$dispatch('run-segment', { id: '{{ $seg->id }}' })"
                                                        title="تشغيل ومعاينة"
                                                        class="p-1 text-emerald-500 hover:text-emerald-700 rounded">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </button>
                                                <button @click="$dispatch('delete-segment', { id: '{{ $seg->id }}', el: $el.closest('[x-data]') })"
                                                        :disabled="removing"
                                                        title="حذف"
                                                        class="p-1 text-red-400 hover:text-red-600 rounded disabled:opacity-50">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Tips --}}
                        <div class="bg-indigo-50 rounded-xl border border-indigo-100 p-4 space-y-2">
                            <div class="text-sm font-semibold text-indigo-800 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                نصائح
                            </div>
                            <ul class="text-xs text-indigo-600 space-y-1 list-disc list-inside">
                                <li>أضف شروطاً متعددة لتصفية دقيقة</li>
                                <li>«معاينة» تُظهر عدد العملاء المطابقين</li>
                                <li>«تطبيق» يفتح قائمة العملاء بالفلاتر</li>
                                <li>ثبّت الشرائح المتكررة للوصول السريع</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ==================== HEALTH SCORE TAB ==================== --}}
            <div x-show="tab === 'health'" x-transition>

                @php
                    $total   = $distribution->total ?? 0;
                    $grades  = [
                        ['label' => 'ممتاز', 'key' => 'excellent', 'bg'   => 'bg-emerald-50', 'text'   => 'text-emerald-700', 'border' => 'border-emerald-200', 'bar'  => 'bg-emerald-500'],
                        ['label' => 'جيد',   'key' => 'good',      'bg'   => 'bg-blue-50',    'text'   => 'text-blue-700',    'border' => 'border-blue-200',    'bar'  => 'bg-blue-500'],
                        ['label' => 'مقبول', 'key' => 'fair',      'bg'   => 'bg-amber-50',   'text'   => 'text-amber-700',   'border' => 'border-amber-200',   'bar'  => 'bg-amber-500'],
                        ['label' => 'ضعيف',  'key' => 'poor',      'bg'   => 'bg-red-50',     'text'   => 'text-red-700',     'border' => 'border-red-200',     'bar'  => 'bg-red-500'],
                    ];
                @endphp

                {{-- Grade Cards --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                    @foreach($grades as $grade)
                        @php
                            $count   = $distribution->{$grade['key']} ?? 0;
                            $percent = $total > 0 ? round(($count / $total) * 100) : 0;
                        @endphp
                        <div class="bg-white rounded-xl border {{ $grade['border'] }} p-4 shadow-sm">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-semibold {{ $grade['text'] }}">{{ $grade['label'] }}</span>
                                <span class="text-2xl font-bold text-gray-800">{{ number_format($count) }}</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2 mb-1">
                                <div class="{{ $grade['bar'] }} h-2 rounded-full transition-all" style="width: {{ $percent }}%"></div>
                            </div>
                            <div class="text-xs text-gray-400">{{ $percent }}%</div>
                        </div>
                    @endforeach
                </div>

                @if($total > 0)
                    {{-- Distribution Bar --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-gray-700">توزيع درجات الصحة</h3>
                            <div class="text-sm text-gray-500">
                                متوسط الدرجة:
                                <span class="font-bold text-gray-800 mr-1">{{ $distribution->avg_score ?? '—' }}/100</span>
                                @if($withoutScore > 0)
                                    · <span class="text-amber-600 text-xs">{{ $withoutScore }} بدون تقييم</span>
                                @endif
                            </div>
                        </div>
                        @php
                            $ex = $distribution->excellent ?? 0;
                            $go = $distribution->good ?? 0;
                            $fa = $distribution->fair ?? 0;
                            $po = $distribution->poor ?? 0;
                        @endphp
                        <div class="flex h-7 rounded-full overflow-hidden gap-px bg-gray-100">
                            @if($ex > 0)<div class="bg-emerald-500 transition-all flex items-center justify-center text-white text-xs font-bold" style="width: {{ ($ex/$total)*100 }}%">@if(($ex/$total)*100 > 8){{ $ex }}@endif</div>@endif
                            @if($go > 0)<div class="bg-blue-500 transition-all flex items-center justify-center text-white text-xs font-bold" style="width: {{ ($go/$total)*100 }}%">@if(($go/$total)*100 > 8){{ $go }}@endif</div>@endif
                            @if($fa > 0)<div class="bg-amber-400 transition-all flex items-center justify-center text-white text-xs font-bold" style="width: {{ ($fa/$total)*100 }}%">@if(($fa/$total)*100 > 8){{ $fa }}@endif</div>@endif
                            @if($po > 0)<div class="bg-red-500 transition-all flex items-center justify-center text-white text-xs font-bold" style="width: {{ ($po/$total)*100 }}%">@if(($po/$total)*100 > 8){{ $po }}@endif</div>@endif
                        </div>
                        <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>ممتاز (80+)</span>
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"></span>جيد (60–79)</span>
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-amber-400 inline-block"></span>مقبول (40–59)</span>
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block"></span>ضعيف (0–39)</span>
                        </div>
                    </div>
                @else
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 mb-6 text-center">
                        <svg class="w-10 h-10 mx-auto mb-2 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        <p class="font-semibold text-amber-800">لا توجد بيانات صحة بعد</p>
                        <p class="text-sm text-amber-600 mt-1">يُحسب المؤشر تلقائياً بعد إضافة تفاعلات العملاء</p>
                    </div>
                @endif

                {{-- Worst & Best --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    {{-- Worst --}}
                    <div class="bg-white rounded-xl shadow-sm border border-red-100 overflow-hidden">
                        <div class="bg-red-50 px-5 py-3 border-b border-red-100 flex items-center gap-2">
                            <span>🔴</span>
                            <h3 class="text-sm font-semibold text-red-800">يحتاجون اهتماماً — أدنى صحة</h3>
                            <span class="mr-auto text-xs text-red-500 font-medium">{{ $worstClients->count() }}</span>
                        </div>
                        @if($worstClients->isEmpty())
                            <div class="py-10 text-center text-gray-400 text-sm">✅ لا يوجد عملاء بتقييم ضعيف</div>
                        @else
                            <div class="divide-y divide-red-50">
                                @foreach($worstClients as $cl)
                                    <div class="flex items-center gap-3 px-5 py-3 hover:bg-red-50 transition">
                                        <div class="w-8 h-8 rounded-full bg-red-100 text-red-700 flex items-center justify-center text-xs font-bold shrink-0">
                                            {{ mb_substr($cl->name, 0, 1) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <a href="{{ route('clients.show', $cl->public_id) }}"
                                               class="text-sm font-medium text-gray-800 hover:text-red-700 truncate block">
                                                {{ $cl->name }}
                                            </a>
                                            @if($cl->last_contact_at)
                                                <div class="text-xs text-gray-400">آخر تواصل: {{ $cl->last_contact_at->diffForHumans() }}</div>
                                            @else
                                                <div class="text-xs text-red-400">لا يوجد تواصل مسجل</div>
                                            @endif
                                        </div>
                                        <div class="shrink-0 text-center">
                                            <div class="text-xl font-bold text-red-600">{{ $cl->health_score }}</div>
                                            <div class="text-xs text-red-400">/100</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="px-5 py-3 border-t border-red-50">
                                <a href="{{ route('clients.index', ['health_max' => 39]) }}"
                                   class="text-xs text-red-600 hover:underline font-medium">
                                    عرض جميع العملاء ذوي التقييم الضعيف ←
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- Best --}}
                    <div class="bg-white rounded-xl shadow-sm border border-emerald-100 overflow-hidden">
                        <div class="bg-emerald-50 px-5 py-3 border-b border-emerald-100 flex items-center gap-2">
                            <span>🌟</span>
                            <h3 class="text-sm font-semibold text-emerald-800">مرشحون للـ VIP — أعلى صحة</h3>
                            <span class="mr-auto text-xs text-emerald-600 font-medium">{{ $bestClients->count() }}</span>
                        </div>
                        @if($bestClients->isEmpty())
                            <div class="py-10 text-center text-gray-400 text-sm">لم يبلغ أي عميل مستوى ممتاز بعد</div>
                        @else
                            <div class="divide-y divide-emerald-50">
                                @foreach($bestClients as $cl)
                                    <div class="flex items-center gap-3 px-5 py-3 hover:bg-emerald-50 transition">
                                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold shrink-0">
                                            {{ mb_substr($cl->name, 0, 1) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <a href="{{ route('clients.show', $cl->public_id) }}"
                                               class="text-sm font-medium text-gray-800 hover:text-emerald-700 truncate block">
                                                {{ $cl->name }}
                                            </a>
                                            <div class="text-xs text-gray-400">
                                                إيراد: {{ number_format((float)$cl->total_revenue, 2) }}
                                            </div>
                                        </div>
                                        <div class="shrink-0 text-center">
                                            <div class="text-xl font-bold text-emerald-600">{{ $cl->health_score }}</div>
                                            <div class="text-xs text-emerald-400">/100</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="px-5 py-3 border-t border-emerald-50">
                                <a href="{{ route('clients.index', ['health_min' => 80]) }}"
                                   class="text-xs text-emerald-600 hover:underline font-medium">
                                    عرض جميع العملاء الممتازين ←
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                @if($withoutScore > 0)
                <div x-data="{ loading: false, done: false, processed: 0, error: '' }"
                     class="mt-4 bg-amber-50 rounded-xl border border-amber-200 p-4 flex items-start gap-3 text-sm text-gray-700">
                    <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="flex-1">
                        <template x-if="!done">
                            <div class="flex items-center justify-between gap-4 flex-wrap">
                                <span>
                                    <strong>{{ $withoutScore }}</strong> عميل من أصل <strong>{{ $totalClients }}</strong> لم يُحسب لهم مؤشر الصحة بعد.
                                </span>
                                <button @click="
                                        loading = true; error = '';
                                        fetch('{{ route('clients.segments.recalculate-health') }}', {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'Accept': 'application/json',
                                            }
                                        })
                                        .then(r => r.json())
                                        .then(d => { done = true; processed = d.processed; })
                                        .catch(() => { error = 'حدث خطأ، يرجى المحاولة مرة أخرى.'; loading = false; })
                                    "
                                    :disabled="loading"
                                    class="inline-flex items-center gap-2 px-4 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium transition disabled:opacity-60 shrink-0">
                                    <svg x-show="loading" class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                    </svg>
                                    <span x-text="loading ? 'جارٍ الحساب...' : 'احسب المؤشرات الآن'"></span>
                                </button>
                            </div>
                        </template>
                        <template x-if="done">
                            <div class="flex items-center gap-2 text-emerald-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>تم حساب مؤشر الصحة لـ <strong x-text="processed"></strong> عميل. أعد تحميل الصفحة لرؤية النتائج.</span>
                                <button @click="window.location.reload()" class="underline text-xs text-emerald-600 hover:text-emerald-800">تحديث</button>
                            </div>
                        </template>
                        <p x-show="error" class="mt-1 text-red-500 text-xs" x-text="error"></p>
                    </div>
                </div>
                @endif

            </div>{{-- /health tab --}}

        </div>{{-- /max-w --}}
    </div>{{-- /py-8 --}}

    {{-- ==================== ALPINE SCRIPTS ==================== --}}
    <script>
    function segmentPage() {
        return {
            tab: 'builder',
            filters: [],
            previewing: false,
            previewCount: null,
            previewClients: [],

            init() {
                this.$el.addEventListener('pin-segment',    (e) => this.pinSegment(e.detail.id, e.detail.pinned));
                this.$el.addEventListener('delete-segment', (e) => this.deleteSegment(e.detail.id, e.detail.el));
                this.$el.addEventListener('load-segment',   (e) => this.loadSegment(e.detail.filters));
                this.$el.addEventListener('run-segment',    (e) => this.runSegmentPreview(e.detail.id));
            },

            removeFilter(index) {
                this.filters.splice(index, 1);
                this.previewCount = null;
            },

            buildFiltersPayload() {
                const payload = {};
                const tagIds  = [];

                for (const f of this.filters) {
                    if (!f.field || f.value === '' || f.value === null) continue;
                    if (f.field === 'tag_ids') {
                        tagIds.push(parseInt(f.value));
                    } else if (f.field === 'health_min') {
                        payload.health_min = parseInt(f.value);
                    } else if (f.field === 'health_max') {
                        payload.health_max = parseInt(f.value);
                    } else if (f.field === 'has_follow_up') {
                        payload.has_follow_up = f.value === '1';
                    } else {
                        payload[f.field] = f.value;
                    }
                }

                if (tagIds.length) payload.tag_ids = tagIds;
                return payload;
            },

            async previewResults() {
                if (this.filters.length === 0) return;
                this.previewing = true;
                this.previewCount = null;
                this.previewClients = [];

                try {
                    const payload = this.buildFiltersPayload();
                    const res = await fetch('{{ route('clients.segments.preview') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ filters: payload, per_page: 10 }),
                    });

                    const json = await res.json();
                    if (json.data) {
                        const items = Array.isArray(json.data) ? json.data : (json.data.data ?? []);
                        this.previewClients = items;
                        this.previewCount   = json.data.total ?? items.length;
                    }
                } catch {
                    window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'خطأ في الاتصال', type: 'error' } }));
                } finally {
                    this.previewing = false;
                }
            },

            applyToList() {
                if (this.filters.length === 0) return;
                const payload = this.buildFiltersPayload();
                const params  = new URLSearchParams();
                for (const [k, v] of Object.entries(payload)) {
                    if (Array.isArray(v)) {
                        v.forEach(i => params.append(k + '[]', i));
                    } else {
                        params.set(k, v);
                    }
                }
                window.location.href = '{{ route('clients.index') }}?' + params.toString();
            },

            loadSegment(filters) {
                this.filters = [];
                if (!filters || typeof filters !== 'object') return;

                for (const [key, val] of Object.entries(filters)) {
                    if (key === 'tag_ids' && Array.isArray(val)) {
                        val.forEach(v => this.filters.push({ field: 'tag_ids', value: String(v) }));
                    } else if (key === 'has_follow_up') {
                        this.filters.push({ field: 'has_follow_up', value: val ? '1' : '0' });
                    } else {
                        this.filters.push({ field: key, value: String(val ?? '') });
                    }
                }

                this.previewCount  = null;
                this.previewClients = [];
                window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'تم تحميل فلاتر الشريحة', type: 'success' } }));
            },

            async runSegmentPreview(segmentId) {
                try {
                    const res = await fetch(`/clients/segments/${segmentId}/execute`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ per_page: 10 }),
                    });
                    const json = await res.json();
                    if (json.data) {
                        const items = Array.isArray(json.data) ? json.data : (json.data.data ?? []);
                        this.previewClients = items;
                        this.previewCount   = json.data.total ?? items.length;
                        this.tab = 'builder';
                        window.dispatchEvent(new CustomEvent('show-toast', {
                            detail: { message: `${this.previewCount} عميل مطابق`, type: 'success' }
                        }));
                    }
                } catch {
                    window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'فشل التنفيذ', type: 'error' } }));
                }
            },

            async pinSegment(segmentId, pinned) {
                try {
                    const res = await fetch(`/clients/segments/${segmentId}/pin`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ pinned }),
                    });
                    const json = await res.json();
                    window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: json.message, type: 'success' } }));
                    setTimeout(() => window.location.reload(), 600);
                } catch {
                    window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'فشل التثبيت', type: 'error' } }));
                }
            },

            async deleteSegment(segmentId, rowEl) {
                if (!confirm('هل أنت متأكد من حذف هذه الشريحة؟')) return;
                try {
                    const res = await fetch(`/clients/segments/${segmentId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                        },
                    });
                    const json = await res.json();
                    if (res.ok && rowEl) {
                        rowEl.remove();
                        window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: json.message, type: 'success' } }));
                    }
                } catch {
                    window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'فشل الحذف', type: 'error' } }));
                }
            },
        };
    }

    function saveModal() {
        return {
            open: false,
            name: '',
            pinned: false,
            saving: false,

            async submit() {
                if (!this.name.trim()) return;
                this.saving = true;

                // Get filters from main page component
                const mainEl = document.querySelector('[x-data="segmentPage()"]');
                const mainData = mainEl ? Alpine.$data(mainEl) : null;
                const payload = mainData ? mainData.buildFiltersPayload() : {};

                try {
                    const res = await fetch('{{ route('clients.segments.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            name:    this.name.trim(),
                            filters: payload,
                            pinned:  this.pinned,
                        }),
                    });

                    const json = await res.json();
                    if (res.ok) {
                        this.open = false;
                        window.dispatchEvent(new CustomEvent('show-toast', {
                            detail: { message: 'تم حفظ الشريحة بنجاح ✓', type: 'success' }
                        }));
                        setTimeout(() => window.location.reload(), 800);
                    } else {
                        throw new Error(json.message || 'فشل الحفظ');
                    }
                } catch (err) {
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { message: err.message, type: 'error' }
                    }));
                } finally {
                    this.saving = false;
                }
            },
        };
    }
    </script>

@endsection

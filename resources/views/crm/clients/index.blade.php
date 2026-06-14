@extends('layouts.app')

@section('title', 'إدارة العملاء')

@section('content')
<div class="space-y-5" x-data="clientList()" x-init="init()">

    {{-- ==================== Header ==================== --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">العملاء</h1>
            <p class="mt-0.5 text-sm text-gray-500">إدارة قاعدة عملائك وتتبع علاقاتك التجارية</p>
        </div>
        <div class="flex items-center gap-2">
            @can('exportClients', App\Models\Client::class)
            <a href="{{ route('clients.export.download', ['format' => 'xlsx']) }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 text-sm text-gray-600 bg-white border
                      border-gray-200 rounded-xl hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                تصدير Excel
            </a>
            @endcan

            @can('importClients', App\Models\Client::class)
            <button @click="$dispatch('open-import-modal')"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm text-gray-600 bg-white border
                           border-gray-200 rounded-xl hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l4-4m0 0l4 4m-4-4v12"/>
                </svg>
                استيراد Excel
            </button>
            @endcan

            @can('create', App\Models\Client::class)
            <a href="{{ route('clients.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700
                      text-white text-sm font-medium rounded-xl transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                عميل جديد
            </a>
            @else
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-400
                        text-sm font-medium rounded-xl cursor-not-allowed"
                 title="وصلت للحد الأقصى من العملاء في خطتك">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                الحد الأقصى
            </div>
            @endcan
        </div>
    </div>

    {{-- ==================== Stats Bar ==================== --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        {{-- الكل --}}
        <a href="{{ route('clients.index') }}"
           class="flex items-center gap-3 p-3 bg-white rounded-xl border border-gray-100
                  hover:border-indigo-200 hover:shadow-sm transition group
                  {{ !$filters->status && !$filters->isArchived ? 'border-indigo-300 ring-1 ring-indigo-200' : '' }}">
            <div class="flex-shrink-0 w-9 h-9 bg-indigo-50 rounded-lg flex items-center justify-center
                        group-hover:bg-indigo-100 transition">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">الكل</p>
                <p class="text-lg font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
            </div>
        </a>

        {{-- نشط --}}
        <a href="{{ route('clients.index', ['status' => 'active']) }}"
           class="flex items-center gap-3 p-3 bg-white rounded-xl border border-gray-100
                  hover:border-teal-200 hover:shadow-sm transition group
                  {{ $filters->status?->value === 'active' ? 'border-teal-300 ring-1 ring-teal-200' : '' }}">
            <div class="flex-shrink-0 w-9 h-9 bg-teal-50 rounded-lg flex items-center justify-center
                        group-hover:bg-teal-100 transition">
                <span class="w-2.5 h-2.5 bg-teal-500 rounded-full"></span>
            </div>
            <div>
                <p class="text-xs text-gray-500">نشط</p>
                <p class="text-lg font-bold text-gray-900">{{ number_format($stats['active']) }}</p>
            </div>
        </a>

        {{-- محتمل --}}
        <a href="{{ route('clients.index', ['status' => 'prospect']) }}"
           class="flex items-center gap-3 p-3 bg-white rounded-xl border border-gray-100
                  hover:border-blue-200 hover:shadow-sm transition group
                  {{ $filters->status?->value === 'prospect' ? 'border-blue-300 ring-1 ring-blue-200' : '' }}">
            <div class="flex-shrink-0 w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center
                        group-hover:bg-blue-100 transition">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">محتمل</p>
                <p class="text-lg font-bold text-gray-900">{{ number_format($stats['prospects']) }}</p>
            </div>
        </a>

        {{-- متابعات --}}
        <a href="{{ route('clients.index', ['has_follow_up' => '1']) }}"
           class="flex items-center gap-3 p-3 bg-white rounded-xl border border-gray-100
                  hover:border-amber-200 hover:shadow-sm transition group
                  {{ $filters->hasPendingFollowUp ? 'border-amber-300 ring-1 ring-amber-200' : '' }}">
            <div class="flex-shrink-0 w-9 h-9 bg-amber-50 rounded-lg flex items-center justify-center
                        group-hover:bg-amber-100 transition">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">متابعات</p>
                <p class="text-lg font-bold text-gray-900">{{ number_format($stats['with_follow_up']) }}</p>
            </div>
        </a>

        {{-- مؤرشف --}}
        <a href="{{ route('clients.index', ['is_archived' => '1']) }}"
           class="flex items-center gap-3 p-3 bg-white rounded-xl border border-gray-100
                  hover:border-gray-300 hover:shadow-sm transition group
                  {{ $filters->isArchived ? 'border-gray-300 ring-1 ring-gray-200' : '' }}">
            <div class="flex-shrink-0 w-9 h-9 bg-gray-50 rounded-lg flex items-center justify-center
                        group-hover:bg-gray-100 transition">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">مؤرشف</p>
                <p class="text-lg font-bold text-gray-900">{{ number_format($stats['archived']) }}</p>
            </div>
        </a>
    </div>

    {{-- ==================== Filters ==================== --}}
    <div class="bg-white rounded-xl border border-gray-100 p-4">
        <form method="GET" action="{{ route('clients.index') }}" class="flex flex-wrap gap-3 items-end">

            {{-- حقل البحث --}}
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">بحث</label>
                <div class="relative">
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" id="liveSearch" value="{{ $filters->search }}"
                           placeholder="الاسم، الشركة، البريد، الهاتف…"
                           x-on:input.debounce.350ms="liveSearch($event.target.value)"
                           x-on:keydown.enter.prevent="liveSearch($event.target.value)"
                           class="w-full pr-9 pl-4 py-2 text-sm border border-gray-200 rounded-lg
                                  focus:ring-2 focus:ring-indigo-500 focus:border-indigo-400 outline-none">
                </div>
            </div>

            {{-- فلتر الحالة --}}
            <div class="min-w-36">
                <label class="block text-xs font-medium text-gray-600 mb-1">الحالة</label>
                <select name="status"
                        class="w-full py-2 px-3 text-sm border border-gray-200 rounded-lg
                               focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                    <option value="">الكل</option>
                    <option value="active"   {{ $filters->status?->value === 'active'   ? 'selected' : '' }}>نشط</option>
                    <option value="prospect" {{ $filters->status?->value === 'prospect' ? 'selected' : '' }}>محتمل</option>
                    <option value="inactive" {{ $filters->status?->value === 'inactive' ? 'selected' : '' }}>غير نشط</option>
                    <option value="archived" {{ $filters->status?->value === 'archived' ? 'selected' : '' }}>مؤرشف</option>
                </select>
            </div>

            {{-- فلتر الوسوم --}}
            @if($tags->isNotEmpty())
            <div class="min-w-36">
                <label class="block text-xs font-medium text-gray-600 mb-1">الوسم</label>
                <select name="tag_ids[]"
                        class="w-full py-2 px-3 text-sm border border-gray-200 rounded-lg
                               focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                    <option value="">كل الوسوم</option>
                    @foreach($tags as $tag)
                    <option value="{{ $tag->id }}"
                            {{ in_array($tag->id, $filters->tagIds) ? 'selected' : '' }}>
                        {{ $tag->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- ترتيب --}}
            <div class="min-w-40">
                <label class="block text-xs font-medium text-gray-600 mb-1">ترتيب حسب</label>
                <select name="sort_by"
                        class="w-full py-2 px-3 text-sm border border-gray-200 rounded-lg
                               focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                    <option value="created_at"    {{ $filters->sortBy === 'created_at'    ? 'selected' : '' }}>تاريخ الإضافة</option>
                    <option value="name"          {{ $filters->sortBy === 'name'          ? 'selected' : '' }}>الاسم</option>
                    <option value="health_score"  {{ $filters->sortBy === 'health_score'  ? 'selected' : '' }}>نقاط الصحة</option>
                    <option value="total_revenue" {{ $filters->sortBy === 'total_revenue' ? 'selected' : '' }}>الإيراد</option>
                    <option value="last_contact_at" {{ $filters->sortBy === 'last_contact_at' ? 'selected' : '' }}>آخر تواصل</option>
                </select>
            </div>

            {{-- اتجاه الترتيب --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">الاتجاه</label>
                <select name="sort_dir"
                        class="py-2 px-3 text-sm border border-gray-200 rounded-lg
                               focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                    <option value="desc" {{ $filters->sortDir === 'desc' ? 'selected' : '' }}>↓ تنازلي</option>
                    <option value="asc"  {{ $filters->sortDir === 'asc'  ? 'selected' : '' }}>↑ تصاعدي</option>
                </select>
            </div>

            {{-- أزرار --}}
            <div class="flex gap-2">
                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm
                               font-medium rounded-lg transition">
                    بحث
                </button>
                @if($filters->hasFilters())
                <a href="{{ route('clients.index') }}"
                   class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm rounded-lg transition">
                    مسح
                </a>
                @endif
            </div>

        </form>
    </div>

    {{-- ==================== Bulk Toolbar ==================== --}}
    <div x-show="selectedIds.length > 0" x-cloak x-transition
         class="bg-indigo-600 text-white rounded-xl px-4 py-3 flex items-center gap-3 flex-wrap">
        <span class="text-sm font-medium" x-text="selectedIds.length + ' عملاء محددين'"></span>
        <div class="flex items-center gap-2 mr-auto">
            <button x-on:click="bulkAction('archive')"
                    class="px-3 py-1.5 text-xs font-medium bg-white/20 hover:bg-white/30 rounded-lg transition">
                أرشفة
            </button>
            <div class="relative" x-data="{ openTag: false }">
                <button x-on:click="openTag = !openTag"
                        class="px-3 py-1.5 text-xs font-medium bg-white/20 hover:bg-white/30 rounded-lg transition flex items-center gap-1">
                    تعيين وسم
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="openTag" x-on:click.away="openTag=false"
                     class="absolute left-0 top-full mt-1 bg-white rounded-xl shadow-xl border border-gray-100 py-1 z-50 min-w-44"
                     style="top: calc(100% + 4px)">
                    @foreach($tags as $tag)
                    <button x-on:click="bulkAction('tag', {{ $tag->id }}); openTag=false"
                            class="w-full text-right px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full inline-block" style="background:{{ $tag->color ?? '#6366f1' }}"></span>
                        {{ $tag->name }}
                    </button>
                    @endforeach
                </div>
            </div>
            <button x-on:click="selectedIds = []"
                    class="px-3 py-1.5 text-xs font-medium bg-white/10 hover:bg-white/20 rounded-lg transition">
                إلغاء التحديد
            </button>
        </div>
    </div>

    {{-- ==================== جدول العملاء ==================== --}}
    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">

        @if($clients->isEmpty())
        {{-- حالة فارغة --}}
        <div class="py-16 text-center">
            <svg class="w-14 h-14 mx-auto text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            @if($filters->hasFilters())
            <p class="text-gray-500 font-medium">لا توجد نتائج تطابق الفلاتر المحددة</p>
            <p class="text-sm text-gray-400 mt-1">جرّب تغيير معايير البحث</p>
            <a href="{{ route('clients.index') }}"
               class="inline-block mt-4 text-sm text-indigo-600 hover:underline">مسح الفلاتر</a>
            @else
            <p class="text-gray-500 font-medium">لا يوجد عملاء بعد</p>
            <p class="text-sm text-gray-400 mt-1">ابدأ بإضافة أول عميل لك</p>
            @can('create', App\Models\Client::class)
            <a href="{{ route('clients.create') }}"
               class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-indigo-600 text-white
                      text-sm font-medium rounded-xl hover:bg-indigo-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                إضافة عميل
            </a>
            @endcan
            @endif
        </div>

        @else
        {{-- الجدول --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="py-3 px-3 w-10">
                            <input type="checkbox"
                                   x-on:change="toggleAll($event.target.checked)"
                                   :checked="selectedIds.length > 0 && selectedIds.length === visibleCount"
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        </th>
                        <th class="text-right py-3 px-4 font-medium text-gray-500 whitespace-nowrap">العميل</th>
                        <th class="text-right py-3 px-4 font-medium text-gray-500 whitespace-nowrap hidden md:table-cell">الحالة</th>
                        <th class="text-right py-3 px-4 font-medium text-gray-500 whitespace-nowrap hidden lg:table-cell">الوسوم</th>
                        <th class="text-right py-3 px-4 font-medium text-gray-500 whitespace-nowrap hidden xl:table-cell">الصحة</th>
                        <th class="text-right py-3 px-4 font-medium text-gray-500 whitespace-nowrap hidden xl:table-cell">آخر تواصل</th>
                        <th class="py-3 px-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($clients as $client)
                    <tr class="hover:bg-gray-50 transition group"
                        :class="selectedIds.includes({{ $client->id }}) ? 'bg-indigo-50/50' : ''">
                        <td class="py-3 px-3">
                            <input type="checkbox"
                                   value="{{ $client->id }}"
                                   x-on:change="toggleClient({{ $client->id }}, $event.target.checked)"
                                   :checked="selectedIds.includes({{ $client->id }})"
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        </td>

                        {{-- معلومات العميل --}}
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                {{-- الأفاتار --}}
                                <div class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold
                                            {{ $client->is_archived ? 'bg-gray-100 text-gray-400' : 'bg-indigo-100 text-indigo-700' }}">
                                    {{ mb_substr($client->name, 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <a href="{{ route('clients.show', $client->public_id) }}"
                                       class="font-medium text-gray-900 hover:text-indigo-600 transition truncate block">
                                        {{ $client->name }}
                                    </a>
                                    @if($client->company)
                                    <p class="text-xs text-gray-400 truncate">{{ $client->company }}</p>
                                    @elseif($client->email)
                                    <p class="text-xs text-gray-400 truncate">{{ $client->email }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- الحالة — $client->status مُحوَّل تلقائياً إلى Enum بالـ cast --}}
                        <td class="py-3 px-4 hidden md:table-cell">
                            @if($client->status)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $client->status->badgeClass() }}">
                                {{ $client->status->label() }}
                            </span>
                            @endif
                        </td>

                        {{-- الوسوم --}}
                        <td class="py-3 px-4 hidden lg:table-cell">
                            <div class="flex flex-wrap gap-1">
                                @forelse($client->tags->take(3) as $tag)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium text-white"
                                      style="background-color: {{ $tag->color ?? '#6366f1' }}">
                                    @if($tag->icon){{ $tag->icon }} @endif{{ $tag->name }}
                                </span>
                                @empty
                                <span class="text-xs text-gray-300">—</span>
                                @endforelse
                                @if($client->tags->count() > 3)
                                <span class="text-xs text-gray-400">+{{ $client->tags->count() - 3 }}</span>
                                @endif
                            </div>
                        </td>

                        {{-- نقاط الصحة --}}
                        <td class="py-3 px-4 hidden xl:table-cell">
                            @if($client->health_score !== null)
                            @php
                                $score = $client->health_score;
                                $color = $score >= 75 ? 'text-teal-600' : ($score >= 50 ? 'text-amber-600' : 'text-red-500');
                                $bg    = $score >= 75 ? 'bg-teal-50' : ($score >= 50 ? 'bg-amber-50' : 'bg-red-50');
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-bold
                                         {{ $color }} {{ $bg }}">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                {{ $score }}
                            </span>
                            @else
                            <span class="text-xs text-gray-300">—</span>
                            @endif
                        </td>

                        {{-- آخر تواصل --}}
                        <td class="py-3 px-4 hidden xl:table-cell">
                            @if($client->last_contact_at)
                            <span class="text-xs text-gray-500"
                                  title="{{ $client->last_contact_at->format('Y-m-d') }}">
                                {{ $client->last_contact_at->diffForHumans() }}
                            </span>
                            @else
                            <span class="text-xs text-gray-300">لا يوجد</span>
                            @endif
                        </td>

                        {{-- إجراءات --}}
                        <td class="py-3 px-4">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition">
                                <a href="{{ route('clients.show', $client->public_id) }}"
                                   class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition"
                                   title="عرض">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                @can('update', $client)
                                <a href="{{ route('clients.edit', $client->public_id) }}"
                                   class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition"
                                   title="تعديل">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @endcan
                                @if($client->is_archived)
                                @can('restore', $client)
                                <form method="POST" action="{{ route('clients.restore', $client->public_id) }}">
                                    @csrf
                                    <button type="submit"
                                            class="p-1.5 text-teal-500 hover:text-teal-700 hover:bg-teal-50 rounded-lg transition"
                                            title="إلغاء الأرشفة">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                        </svg>
                                    </button>
                                </form>
                                @endcan
                                @else
                                @can('archive', $client)
                                <form method="POST" action="{{ route('clients.archive', $client->public_id) }}"
                                      onsubmit="return confirm('هل تريد أرشفة هذا العميل؟')">
                                    @csrf
                                    <button type="submit"
                                            class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-lg transition"
                                            title="أرشفة">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                        </svg>
                                    </button>
                                </form>
                                @endcan
                                @endif
                            </div>
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Footer: Count + Infinite Scroll --}}
        <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
            <p class="text-sm text-gray-500">
                عرض <span x-text="visibleCount"></span> عميل
                <span x-show="loadingMore" class="mr-2 text-indigo-500 text-xs">جاري التحميل…</span>
            </p>
            <p x-show="!hasMore && visibleCount > 0" class="text-xs text-gray-400">وصلت للنهاية ✓</p>
        </div>
        <div id="scroll-sentinel" class="h-1"></div>
        @endif

    </div>

</div>

{{-- ==================== Import Modal ==================== --}}
@can('importClients', App\Models\Client::class)
<div x-data="importManager()"
     x-show="open"
     x-cloak
     @open-import-modal.window="openModal()"
     class="fixed inset-0 z-50 flex items-center justify-center p-4">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeModal()"></div>

    {{-- Panel --}}
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-auto"
         @click.stop>

        {{-- Header --}}
        <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <div>
                <h3 class="text-base font-bold text-gray-900">استيراد العملاء</h3>
                <p class="text-xs text-gray-500 mt-0.5">ارفع ملف Excel وسيتم إضافة العملاء تلقائياً</p>
            </div>
            <button @click="closeModal()" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="p-5 space-y-4">

            {{-- Step 1: رفع الملف --}}
            <template x-if="step === 'upload'">
                <div class="space-y-4">

                    {{-- تنزيل القالب --}}
                    <div class="flex items-center justify-between p-3 bg-indigo-50 rounded-xl border border-indigo-100">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="text-sm text-indigo-700 font-medium">نموذج الاستيراد</span>
                        </div>
                        <a href="{{ route('clients.import.template') }}"
                           class="text-xs text-indigo-600 hover:text-indigo-800 font-medium underline">
                            تحميل القالب (.xlsx)
                        </a>
                    </div>

                    {{-- منطقة رفع الملف --}}
                    <div class="relative">
                        <label
                            class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed rounded-xl cursor-pointer transition"
                            :class="dragover
                                ? 'border-indigo-400 bg-indigo-50'
                                : (file ? 'border-green-400 bg-green-50' : 'border-gray-200 bg-gray-50 hover:border-indigo-300 hover:bg-indigo-50')"
                            @dragover.prevent="dragover = true"
                            @dragleave.prevent="dragover = false"
                            @drop.prevent="handleDrop($event)">

                            <template x-if="!file">
                                <div class="text-center">
                                    <svg class="mx-auto w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <p class="text-sm text-gray-600">اسحب الملف هنا أو <span class="text-indigo-600 font-medium">اضغط للاختيار</span></p>
                                    <p class="text-xs text-gray-400 mt-1">xlsx أو xls أو csv — حتى 10 ميجابايت</p>
                                </div>
                            </template>

                            <template x-if="file">
                                <div class="text-center">
                                    <svg class="mx-auto w-8 h-8 text-green-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <p class="text-sm font-medium text-green-700" x-text="file.name"></p>
                                    <p class="text-xs text-green-500 mt-0.5" x-text="formatSize(file.size)"></p>
                                </div>
                            </template>

                            <input type="file" class="hidden" accept=".xlsx,.xls,.csv"
                                   @change="handleFileChange($event)">
                        </label>

                        {{-- زر إزالة الملف --}}
                        <template x-if="file">
                            <button @click.prevent="file = null"
                                    class="absolute top-2 left-2 p-1 bg-white rounded-full shadow text-gray-400 hover:text-red-500 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </template>
                    </div>

                    {{-- خيارات --}}
                    <div class="space-y-2">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" x-model="skipDuplicates"
                                   class="w-4 h-4 rounded text-indigo-600 border-gray-300 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">تخطي العملاء المكررين (نفس البريد الإلكتروني)</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" x-model="updateExisting"
                                   class="w-4 h-4 rounded text-indigo-600 border-gray-300 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">تحديث العملاء الموجودين (إذا كان البريد مطابقاً)</span>
                        </label>
                    </div>

                    {{-- رسالة خطأ --}}
                    <template x-if="errorMsg">
                        <div class="flex items-center gap-2 p-3 bg-red-50 rounded-xl border border-red-100">
                            <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm text-red-700" x-text="errorMsg"></p>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Step 2: جارٍ المعالجة --}}
            <template x-if="step === 'processing'">
                <div class="space-y-4 py-2">
                    <div class="flex flex-col items-center text-center gap-3">
                        <div class="w-14 h-14 bg-indigo-50 rounded-full flex items-center justify-center">
                            <svg class="w-7 h-7 text-indigo-500 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800" x-text="statusLabel || 'جارٍ رفع الملف...'"></p>
                            <p class="text-xs text-gray-500 mt-0.5">لا تغلق هذه النافذة</p>
                        </div>
                    </div>

                    {{-- Progress bar --}}
                    <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                        <div class="h-2 bg-indigo-500 rounded-full transition-all duration-500"
                             :style="'width:' + progress + '%'"></div>
                    </div>
                    <p class="text-center text-xs text-gray-400" x-text="progress + '%'"></p>
                </div>
            </template>

            {{-- Step 3: النتيجة --}}
            <template x-if="step === 'done'">
                <div class="space-y-4 py-2">
                    <div class="flex flex-col items-center text-center gap-3">
                        <div class="w-14 h-14 rounded-full flex items-center justify-center"
                             :class="result.error_count > 0 ? 'bg-amber-50' : 'bg-green-50'">
                            <template x-if="result.error_count === 0">
                                <svg class="w-7 h-7 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </template>
                            <template x-if="result.error_count > 0">
                                <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </template>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800"
                               x-text="result.error_count === 0 ? 'تم الاستيراد بنجاح!' : 'اكتمل مع تحذيرات'"></p>
                            <p class="text-xs text-gray-500 mt-0.5" x-text="result.summary || ''"></p>
                        </div>
                    </div>

                    {{-- إحصاءات --}}
                    <div class="grid grid-cols-3 gap-3">
                        <div class="text-center p-3 bg-green-50 rounded-xl">
                            <p class="text-xl font-bold text-green-600" x-text="result.success_count ?? 0"></p>
                            <p class="text-xs text-green-700">مضاف</p>
                        </div>
                        <div class="text-center p-3 bg-amber-50 rounded-xl">
                            <p class="text-xl font-bold text-amber-600" x-text="result.skipped_count ?? 0"></p>
                            <p class="text-xs text-amber-700">متخطَّى</p>
                        </div>
                        <div class="text-center p-3 bg-red-50 rounded-xl">
                            <p class="text-xl font-bold text-red-600" x-text="result.error_count ?? 0"></p>
                            <p class="text-xs text-red-700">أخطاء</p>
                        </div>
                    </div>

                    {{-- أخطاء تفصيلية --}}
                    <template x-if="result.errors && result.errors.length > 0">
                        <div class="max-h-32 overflow-y-auto space-y-1.5 p-3 bg-red-50 rounded-xl border border-red-100">
                            <p class="text-xs font-medium text-red-700 mb-2">تفاصيل الأخطاء:</p>
                            <template x-for="err in result.errors.slice(0, 10)" :key="err.row">
                                <div class="text-xs text-red-600">
                                    <span class="font-medium">سطر <span x-text="err.row"></span>:</span>
                                    <span x-text="Array.isArray(err.errors) ? err.errors.join('، ') : err.errors"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </template>

        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between p-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
            <button @click="closeModal()"
                    class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition">
                <span x-text="step === 'done' ? 'إغلاق' : 'إلغاء'"></span>
            </button>

            <template x-if="step === 'upload'">
                <button @click="submitImport()"
                        :disabled="!file || uploading"
                        class="inline-flex items-center gap-2 px-5 py-2 text-sm font-medium text-white
                               bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed
                               rounded-xl transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l4-4m0 0l4 4m-4-4v12"/>
                    </svg>
                    بدء الاستيراد
                </button>
            </template>

            <template x-if="step === 'done' && result.success_count > 0">
                <a href="{{ route('clients.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-2 text-sm font-medium text-white
                          bg-green-600 hover:bg-green-700 rounded-xl transition">
                    عرض العملاء
                </a>
            </template>
        </div>
    </div>
</div>
@endcan

@push('scripts')
<script>
window.clientList = function() {
    return {
        // ── State ────────────────────────────────────────────────────
        selectedIds:  [],
        visibleCount: {{ $clients->count() }},
        hasMore:      {{ $clients->hasMorePages() ? 'true' : 'false' }},
        nextCursor:   '{{ $clients->nextCursor()?->encode() ?? '' }}',
        loadingMore:  false,
        searching:    false,
        currentSearch: '{{ $filters->search ?? '' }}',

        // ── Init ─────────────────────────────────────────────────────
        init() {
            this.$nextTick(() => this.setupInfiniteScroll());
        },

        // ── Infinite Scroll ──────────────────────────────────────────
        setupInfiniteScroll() {
            const sentinel = document.getElementById('scroll-sentinel');
            if (!sentinel) return;
            const observer = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting && this.hasMore && !this.loadingMore) {
                    this.loadMore();
                }
            }, { rootMargin: '200px' });
            observer.observe(sentinel);
        },

        async loadMore() {
            if (!this.hasMore || this.loadingMore || !this.nextCursor) return;
            this.loadingMore = true;
            try {
                const params = new URLSearchParams(window.location.search);
                params.set('cursor', this.nextCursor);
                const res  = await fetch(`{{ route('clients.index') }}?${params}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const json = await res.json();
                this.renderClients(json.data, true);
                this.hasMore    = json.has_more;
                this.nextCursor = json.next_cursor ?? '';
                this.visibleCount += json.data.length;
            } catch(e) { console.error(e); }
            finally { this.loadingMore = false; }
        },

        // ── Live Search ───────────────────────────────────────────────
        async liveSearch(term) {
            this.currentSearch = term;
            this.searching     = true;
            this.selectedIds   = [];
            try {
                const params = new URLSearchParams(window.location.search);
                params.set('search', term);
                params.delete('cursor');
                // تحديث URL بدون reload
                history.replaceState({}, '', `?${params}`);
                const res  = await fetch(`{{ route('clients.index') }}?${params}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const json = await res.json();
                this.renderClients(json.data, false);
                this.hasMore      = json.has_more;
                this.nextCursor   = json.next_cursor ?? '';
                this.visibleCount = json.data.length;
            } catch(e) { console.error(e); }
            finally { this.searching = false; }
        },

        // ── Render Clients ────────────────────────────────────────────
        renderClients(clients, append) {
            const tbody = document.querySelector('table tbody');
            if (!tbody) return;
            if (!append) tbody.innerHTML = '';

            if (!append && clients.length === 0) {
                tbody.innerHTML = `
                    <tr><td colspan="7" class="py-12 text-center text-gray-400 text-sm">
                        لا توجد نتائج تطابق البحث
                    </td></tr>`;
                return;
            }

            clients.forEach(c => {
                const tags = (c.tags || []).slice(0, 3).map(t =>
                    `<span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium text-white"
                           style="background-color:${t.color}">${t.icon || ''}${t.name}</span>`
                ).join('');

                const health = c.health_score != null
                    ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-bold
                        ${c.health_score >= 75 ? 'text-teal-600 bg-teal-50' : c.health_score >= 50 ? 'text-amber-600 bg-amber-50' : 'text-red-500 bg-red-50'}">
                        ★ ${c.health_score}</span>`
                    : '<span class="text-xs text-gray-300">—</span>';

                const status = c.status_label
                    ? `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${c.status_badge}">${c.status_label}</span>`
                    : '';

                const initials = c.name ? c.name.charAt(0) : '?';
                const avatarCls = c.is_archived ? 'bg-gray-100 text-gray-400' : 'bg-indigo-100 text-indigo-700';

                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50 transition group';
                tr.dataset.clientId = c.id;
                tr.innerHTML = `
                    <td class="py-3 px-3">
                        <input type="checkbox" value="${c.id}"
                               onchange="document.querySelector('[x-data]').__x.$data.toggleClient(${c.id}, this.checked)"
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold ${avatarCls}">
                                ${initials}
                            </div>
                            <div class="min-w-0">
                                <a href="${c.show_url}" class="font-medium text-gray-900 hover:text-indigo-600 transition truncate block">
                                    ${c.name}
                                </a>
                                ${c.company ? `<p class="text-xs text-gray-400 truncate">${c.company}</p>` : (c.email ? `<p class="text-xs text-gray-400 truncate">${c.email}</p>` : '')}
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-4 hidden md:table-cell">${status}</td>
                    <td class="py-3 px-4 hidden lg:table-cell"><div class="flex flex-wrap gap-1">${tags}</div></td>
                    <td class="py-3 px-4 hidden xl:table-cell">${health}</td>
                    <td class="py-3 px-4 hidden xl:table-cell">
                        <span class="text-xs text-gray-500">${c.last_contact || '<span class="text-gray-300">لا يوجد</span>'}</span>
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition">
                            <a href="${c.show_url}" class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="عرض">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="${c.edit_url}" class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition" title="تعديل">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                        </div>
                    </td>`;
                tbody.appendChild(tr);
            });
        },

        // ── Bulk Actions ─────────────────────────────────────────────
        toggleClient(id, checked) {
            if (checked) {
                if (!this.selectedIds.includes(id)) this.selectedIds.push(id);
            } else {
                this.selectedIds = this.selectedIds.filter(i => i !== id);
            }
        },

        toggleAll(checked) {
            const checkboxes = document.querySelectorAll('table tbody input[type=checkbox]');
            checkboxes.forEach(cb => {
                cb.checked = checked;
                this.toggleClient(parseInt(cb.value), checked);
            });
            if (!checked) this.selectedIds = [];
        },

        async bulkAction(action, tagId = null) {
            if (this.selectedIds.length === 0) return;
            const label = action === 'archive' ? 'أرشفة' : 'تعيين وسم';
            if (!confirm(`هل تريد ${label} ${this.selectedIds.length} عملاء؟`)) return;

            try {
                const body = { action, client_ids: this.selectedIds };
                if (tagId) body.tag_id = tagId;
                const res  = await fetch('{{ route('clients.bulk-action') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(body),
                });
                const json = await res.json();
                if (res.ok) {
                    // إزالة الصفوف من الجدول
                    if (action === 'archive') {
                        this.selectedIds.forEach(id => {
                            document.querySelector(`tr[data-client-id="${id}"]`)?.remove();
                        });
                        this.visibleCount -= this.selectedIds.length;
                    }
                    this.selectedIds = [];
                    // Toast بسيط
                    const t = document.createElement('div');
                    t.className = 'fixed top-5 left-1/2 -translate-x-1/2 z-50 bg-emerald-600 text-white text-sm px-5 py-3 rounded-xl shadow-lg';
                    t.textContent = json.message;
                    document.body.appendChild(t);
                    setTimeout(() => t.remove(), 3000);
                    if (action === 'tag') window.location.reload();
                }
            } catch(e) { console.error(e); }
        },
    }
}

window.importManager = function() {
    return {
        open:          false,
        step:          'upload',   // upload | processing | done
        file:          null,
        dragover:      false,
        uploading:     false,
        skipDuplicates: true,
        updateExisting: false,
        progress:      0,
        statusLabel:   '',
        errorMsg:      '',
        result:        {},
        pollTimer:     null,
        logId:         null,

        openModal() {
            this.reset();
            this.open = true;
        },

        closeModal() {
            if (this.pollTimer) clearInterval(this.pollTimer);
            this.open = false;
        },

        reset() {
            this.step          = 'upload';
            this.file          = null;
            this.uploading     = false;
            this.progress      = 0;
            this.statusLabel   = '';
            this.errorMsg      = '';
            this.result        = {};
            this.logId         = null;
            if (this.pollTimer) clearInterval(this.pollTimer);
        },

        handleFileChange(event) {
            const f = event.target.files[0];
            if (f) this.validateAndSetFile(f);
        },

        handleDrop(event) {
            this.dragover = false;
            const f = event.dataTransfer.files[0];
            if (f) this.validateAndSetFile(f);
        },

        validateAndSetFile(f) {
            const allowed = ['xlsx', 'xls', 'csv'];
            const ext = f.name.split('.').pop().toLowerCase();
            if (!allowed.includes(ext)) {
                this.errorMsg = 'صيغة الملف غير مدعومة — xlsx أو xls أو csv فقط.';
                return;
            }
            if (f.size > 10 * 1024 * 1024) {
                this.errorMsg = 'حجم الملف يتجاوز 10 ميجابايت.';
                return;
            }
            this.errorMsg = '';
            this.file = f;
        },

        formatSize(bytes) {
            if (bytes < 1024)       return bytes + ' B';
            if (bytes < 1024*1024)  return (bytes/1024).toFixed(1) + ' KB';
            return (bytes/1024/1024).toFixed(1) + ' MB';
        },

        async submitImport() {
            if (!this.file || this.uploading) return;

            this.uploading   = true;
            this.step        = 'processing';
            this.progress    = 10;
            this.statusLabel = 'جارٍ رفع الملف...';
            this.errorMsg    = '';

            const formData = new FormData();
            formData.append('file',             this.file);
            formData.append('skip_duplicates',  this.skipDuplicates ? '1' : '0');
            formData.append('update_existing',  this.updateExisting ? '1' : '0');
            formData.append('_token',           document.querySelector('meta[name=csrf-token]').content);

            try {
                const resp = await fetch('{{ route('clients.import.store') }}', {
                    method: 'POST',
                    body:   formData,
                });

                const json = await resp.json();

                if (!resp.ok) {
                    const msgs = json.errors
                        ? Object.values(json.errors).flat().join(' | ')
                        : (json.message || 'فشل الرفع');
                    this.step     = 'upload';
                    this.errorMsg = msgs;
                    this.uploading = false;
                    return;
                }

                this.logId       = json.data?.id;
                this.progress    = 30;
                this.statusLabel = 'جارٍ معالجة الملف...';

                // إذا اكتمل فوراً (sync queue)
                if (['completed','partial','failed'].includes(json.data?.status)) {
                    this.finishWithResult(json.data);
                    return;
                }

                // بدء polling كل 2 ثانية
                this.pollTimer = setInterval(() => this.pollStatus(), 2000);

            } catch (err) {
                this.step      = 'upload';
                this.errorMsg  = 'حدث خطأ غير متوقع، حاول مجدداً.';
                this.uploading = false;
            }
        },

        async pollStatus() {
            if (!this.logId) return;

            try {
                const resp = await fetch(`{{ url('clients/import') }}/${this.logId}`, {
                    headers: { 'Accept': 'application/json' },
                });
                const json = await resp.json();
                const data = json.data;

                // تحديث الـ progress
                if (data.total_rows > 0) {
                    const done = (data.success_count + data.error_count + data.skipped_count);
                    this.progress = Math.min(90, 30 + Math.round((done / data.total_rows) * 60));
                } else {
                    this.progress = Math.min(this.progress + 5, 85);
                }

                this.statusLabel = data.status_label || 'جارٍ المعالجة...';

                if (json.is_finished) {
                    clearInterval(this.pollTimer);
                    this.finishWithResult(data);
                }
            } catch (e) {
                // تجاهل أخطاء الـ polling المؤقتة
            }
        },

        finishWithResult(data) {
            clearInterval(this.pollTimer);
            this.progress  = 100;
            this.result    = data;
            this.step      = 'done';
            this.uploading = false;
        },
    }
}
</script>
@endpush
@endsection

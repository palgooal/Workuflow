@extends('layouts.app')

@section('title', 'المشاريع')

@section('content')
<div class="space-y-6" x-data="{
    view: localStorage.getItem('projects_view') || 'card',
    setView(v) { this.view = v; localStorage.setItem('projects_view', v); }
}">

    {{-- Header --}}
    <x-page-header title="المشاريع" subtitle="إدارة مشاريعك وتتبع أرباحها الصافية">
        <x-slot name="actions">
            {{-- View Toggle --}}
            <div class="flex items-center gap-1 p-1 bg-slate-100 rounded-xl">
                <button @click="setView('card')"
                        :class="view === 'card' ? 'bg-white shadow-sm text-ink' : 'text-muted hover:text-ink'"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                </button>
                <button @click="setView('table')"
                        :class="view === 'table' ? 'bg-white shadow-sm text-ink' : 'text-muted hover:text-ink'"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 10h18M3 14h18M10 3v18"/>
                    </svg>
                </button>
            </div>

            @can('create', App\Models\Project::class)
                <a href="{{ route('projects.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand hover:bg-brand-600
                          text-white text-sm font-semibold rounded-btn transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    مشروع جديد
                </a>
            @else
                <div class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 text-muted
                            text-sm font-medium rounded-btn cursor-not-allowed"
                     title="وصلت للحد الأقصى من المشاريع في خطتك الحالية">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    الحد الأقصى مكتمل
                </div>
            @endcan
        </x-slot>
    </x-page-header>

    {{-- Portfolio Summary --}}
    @if($portfolio['projects_count'] > 0)
    @php $byCur = $portfolio['by_currency'] ?? []; $multi = $portfolio['multi_currency'] ?? false; @endphp

    @if($multi)
    <x-card-section padding="p-0">
        <x-data-table>
            <x-slot name="head">
                <x-table-th>العملة</x-table-th>
                <x-table-th align="center">الدخل</x-table-th>
                <x-table-th align="center">المصروفات</x-table-th>
                <x-table-th align="center">الصافي</x-table-th>
            </x-slot>
            @foreach($byCur as $cur => $vals)
            <tr class="dash-row">
                <td class="dash-td font-semibold text-ink">{{ $cur }}</td>
                <td class="dash-td text-center font-bold text-success-700 nums">+{{ number_format($vals['income'], 2) }}</td>
                <td class="dash-td text-center font-bold text-red-600 nums">-{{ number_format($vals['expenses'], 2) }}</td>
                <td class="dash-td text-center font-bold nums {{ $vals['net'] >= 0 ? 'text-brand-600' : 'text-red-600' }}">
                    {{ $vals['net'] >= 0 ? '+' : '' }}{{ number_format($vals['net'], 2) }}
                </td>
            </tr>
            @endforeach
        </x-data-table>
    </x-card-section>
    <div class="flex items-center justify-between gap-3 text-sm text-slate-600 px-1">
        <span class="inline-flex items-center gap-2">
            <svg class="w-4 h-4 text-brand/70" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
            المشاريع النشطة:
            <span class="font-semibold text-brand-600 nums">{{ $portfolio['active_count'] }} / {{ $portfolio['projects_count'] }}</span>
        </span>
    </div>
    <div class="flex items-center gap-2 text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded-xl px-4 py-2.5">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        معاملات بعملات متعددة — المبالغ معروضة منفصلة لكل عملة بدون دمج.
    </div>

    @else
    @php $vals = array_values($byCur)[0] ?? ['income'=>0,'expenses'=>0,'net'=>0]; $cur = array_key_first($byCur) ?? ''; @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stats-card title="إجمالي الدخل" :value="number_format($vals['income'], 2) . ' ' . $cur" color="green" prefix="">
            <x-slot name="icon"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg></x-slot>
        </x-stats-card>
        <x-stats-card title="إجمالي المصروفات" :value="number_format($vals['expenses'], 2) . ' ' . $cur" color="red">
            <x-slot name="icon"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg></x-slot>
        </x-stats-card>
        <x-stats-card title="صافي الربح" :value="number_format(abs($vals['net']), 2) . ' ' . $cur" :color="$vals['net'] >= 0 ? 'green' : 'red'" :prefix="$vals['net'] >= 0 ? '+' : '-'">
            <x-slot name="icon"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></x-slot>
        </x-stats-card>
        <x-stats-card title="المشاريع النشطة" :value="$portfolio['active_count'] . ' / ' . $portfolio['projects_count']" color="indigo">
            <x-slot name="icon"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg></x-slot>
        </x-stats-card>
    </div>
    @endif
    @endif

    {{-- ======= CARD VIEW ======= --}}
    <div x-show="view === 'card'" x-transition:enter="transition-opacity duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">

        {{-- Business Projects --}}
        @if(isset($projects['business']) && $projects['business']->isNotEmpty())
        <div class="mb-6">
            <div class="flex items-center gap-2 mb-3">
                <svg class="w-4 h-4 text-brand/70" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                </svg>
                <h2 class="text-sm font-semibold text-ink">المشاريع التجارية</h2>
                <x-badge color="blue">{{ $projects['business']->count() }}</x-badge>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($projects['business'] as $project)
                    @include('projects._card', ['project' => $project])
                @endforeach
            </div>
        </div>
        @endif

        {{-- Personal Projects --}}
        @if(isset($projects['personal']) && $projects['personal']->isNotEmpty())
        <div>
            <div class="flex items-center gap-2 mb-3">
                <svg class="w-4 h-4 text-brand/70" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <h2 class="text-sm font-semibold text-ink">المشاريع الشخصية</h2>
                <x-badge color="purple">{{ $projects['personal']->count() }}</x-badge>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($projects['personal'] as $project)
                    @include('projects._card', ['project' => $project])
                @endforeach
            </div>
        </div>
        @endif

    </div>

    {{-- ======= TABLE VIEW ======= --}}
    <div x-show="view === 'table'" x-transition:enter="transition-opacity duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">

        @php
            $allProjects = collect($projects['business'] ?? [])->merge($projects['personal'] ?? []);
        @endphp

        @if($allProjects->isNotEmpty())
        <div class="dash-card overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-subtle bg-slate-50/60">
                        <th class="dash-th text-right px-4 py-3">المشروع</th>
                        <th class="dash-th text-center px-4 py-3">النوع</th>
                        <th class="dash-th text-center px-4 py-3">الحالة</th>
                        <th class="dash-th text-center px-4 py-3">العملة</th>
                        <th class="dash-th text-center px-4 py-3">الدخل</th>
                        <th class="dash-th text-center px-4 py-3">المصروف</th>
                        <th class="dash-th text-center px-4 py-3">الصافي</th>
                        <th class="dash-th text-center px-4 py-3">المعاملات</th>
                        <th class="dash-th px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-subtle">
                    @foreach($allProjects as $project)
                    @php
                        $income   = $project->totalIncome();
                        $expenses = $project->totalExpenses();
                        $net      = $project->netProfit();
                    @endphp
                    <tr class="dash-row">
                        <td class="dash-td">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg shrink-0 flex items-center justify-center text-base"
                                     style="background-color: {{ $project->color }}1A; border: 1.5px solid {{ $project->color }}40">
                                    {{ $project->type->icon() }}
                                </div>
                                <div>
                                    <p class="font-semibold text-ink">{{ $project->name }}</p>
                                    @if($project->description)
                                        <p class="text-xs text-muted truncate max-w-[200px]">{{ $project->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="dash-td text-center">
                            <span class="text-xs text-muted">{{ $project->type->label() }}</span>
                        </td>
                        <td class="dash-td text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold {{ $project->status->tailwindBadge() }}">
                                {{ $project->status->icon() }} {{ $project->status->label() }}
                            </span>
                        </td>
                        <td class="dash-td text-center">
                            <span class="text-xs font-medium text-muted">{{ $project->currency }}</span>
                        </td>
                        <td class="dash-td text-center font-bold text-success-700 nums">
                            +{{ number_format($income, 0) }}
                        </td>
                        <td class="dash-td text-center font-bold text-red-600 nums">
                            {{ number_format($expenses, 0) }}
                        </td>
                        <td class="dash-td text-center font-bold nums {{ $net >= 0 ? 'text-brand-600' : 'text-red-600' }}">
                            {{ $net >= 0 ? '+' : '' }}{{ number_format($net, 0) }}
                        </td>
                        <td class="dash-td text-center">
                            <span class="text-xs text-muted nums">{{ $project->transactions_count ?? 0 }}</span>
                        </td>
                        <td class="dash-td">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('projects.show', $project) }}"
                                   class="inline-flex items-center gap-1 text-xs text-brand hover:text-brand-700 font-semibold transition-colors px-2 py-1 rounded-lg hover:bg-brand-50">
                                    عرض
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                </a>
                                <div class="relative">
                                    <button onclick="toggleProjectMenu(event, 'pmenu-{{ $project->id }}')"
                                            class="p-1.5 rounded-lg text-muted hover:text-ink hover:bg-slate-100 transition-colors">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                        </svg>
                                    </button>
                                </div>

                                {{-- Dropdown مثبّت بـ body --}}
                                <div id="pmenu-{{ $project->id }}"
                                     class="hidden fixed w-44 bg-surface rounded-xl shadow-pop border border-subtle py-1"
                                     style="z-index: 1000;">
                                    <a href="{{ route('projects.show', $project) }}"
                                       class="flex items-center gap-2.5 px-3 py-2 text-sm text-ink hover:bg-slate-50 transition-colors">
                                        <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        عرض التفاصيل
                                    </a>
                                    <a href="{{ route('projects.edit', $project) }}"
                                       class="flex items-center gap-2.5 px-3 py-2 text-sm text-ink hover:bg-slate-50 transition-colors">
                                        <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        تعديل
                                    </a>
                                    <div class="h-px bg-subtle my-1"></div>
                                    <form method="POST" action="{{ route('projects.destroy', $project) }}"
                                          onsubmit="return confirm('هل أنت متأكد من حذف مشروع {{ addslashes($project->name) }}؟')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="w-full flex items-center gap-2.5 px-3 py-2 text-sm text-red-600 hover:bg-error-soft transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            حذف
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>

    {{-- Empty State --}}
    @if($portfolio['projects_count'] === 0)

        <x-empty-state
            title="لا توجد مشاريع بعد"
            description="أنشئ مشروعك الأول لتبدأ بتتبع دخلك ومصاريفك وصافي ربحك"
            :action="route('projects.create')"
            actionLabel="إنشاء أول مشروع"
        />
    @endif

</div>

@push('scripts')
<script>
(function() {
    let _activeMenu = null;

    function closeAll() {
        if (_activeMenu) {
            _activeMenu.classList.add('hidden');
            _activeMenu = null;
        }
    }

    window.toggleProjectMenu = function(e, id) {
        e.stopPropagation();
        const menu = document.getElementById(id);
        if (!menu) return;

        if (_activeMenu && _activeMenu !== menu) closeAll();

        if (menu.classList.contains('hidden')) {
            const btn = e.currentTarget;
            const r   = btn.getBoundingClientRect();
            const mw  = 176;

            // top: تحت الزر مباشرة
            menu.style.top   = (r.bottom + window.scrollY + 4) + 'px';
            menu.style.right = 'auto';

            // left: نحاول نبدأ من يمين الزر ونمتد لليسار
            // إذا خرج من الشاشة نربطه باليمين
            let left = r.left + window.scrollX - (mw - r.width);
            if (left < 4) left = 4;
            if (left + mw > window.innerWidth - 4) left = window.innerWidth - mw - 4;
            menu.style.left = left + 'px';

            menu.classList.remove('hidden');
            _activeMenu = menu;
        } else {
            closeAll();
        }
    };

    document.addEventListener('click', closeAll);
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeAll(); });
})();
</script>
@endpush
@endsection

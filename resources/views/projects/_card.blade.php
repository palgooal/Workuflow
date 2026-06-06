<div class="bg-white rounded-2xl border border-gray-100 hover:shadow-md transition-shadow
            {{ $project->status->isActive() ? '' : 'opacity-70' }}"
     x-data="{ menuOpen: false, statusOpen: false }">

    {{-- Color bar --}}
    <div class="h-1.5 w-full rounded-t-2xl" style="background-color: {{ $project->color }}"></div>

    <div class="p-5">
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                {{-- Color circle --}}
                <div class="w-10 h-10 rounded-xl shrink-0 flex items-center justify-center"
                     style="background-color: {{ $project->color }}1A; border: 2px solid {{ $project->color }}40">
                    <span class="text-lg">{{ $project->type->icon() }}</span>
                </div>
                <div class="min-w-0">
                    <h3 class="font-semibold text-gray-900 truncate">{{ $project->name }}</h3>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $project->currency }}</p>
                </div>
            </div>

            {{-- Dropdown menu --}}
            <div class="relative shrink-0" @click.outside="menuOpen = false; statusOpen = false">
                <button @click="menuOpen = !menuOpen; statusOpen = false"
                        class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                    </svg>
                </button>

                <div x-show="menuOpen" x-transition
                     class="absolute left-0 bottom-full mb-1 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-20">

                    <a href="{{ route('projects.show', $project) }}"
                       class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        عرض التفاصيل
                    </a>

                    <a href="{{ route('projects.edit', $project) }}"
                       class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        تعديل
                    </a>

                    <hr class="my-1 border-gray-100">

                    {{-- تغيير الحالة --}}
                    <button type="button" @click="statusOpen = !statusOpen"
                            class="w-full flex items-center justify-between gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <span class="flex items-center gap-2">
                            <span>{{ $project->status->icon() }}</span>
                            <span>{{ $project->status->label() }}</span>
                        </span>
                        <svg class="w-3.5 h-3.5 text-gray-400 transition-transform"
                             :class="statusOpen ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    {{-- خيارات الحالة --}}
                    <div x-show="statusOpen" x-transition class="bg-gray-50 border-t border-b border-gray-100 py-1">
                        @php
                            $allStatuses = \App\Support\Enums\ProjectStatus::cases();
                        @endphp
                        @foreach($allStatuses as $s)
                            @if($s !== $project->status)
                            <form method="POST" action="{{ route('projects.update-status', $project) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="{{ $s->value }}">
                                <button type="submit"
                                        class="w-full flex items-center gap-2.5 px-4 py-1.5 text-xs text-gray-600 hover:bg-white hover:text-gray-900 transition">
                                    <span>{{ $s->icon() }}</span>
                                    <span>{{ $s->label() }}</span>
                                </button>
                            </form>
                            @endif
                        @endforeach
                    </div>

                    <hr class="my-1 border-gray-100">

                    <form method="POST" action="{{ route('projects.destroy', $project) }}"
                          onsubmit="return confirm('هل أنت متأكد من حذف مشروع {{ addslashes($project->name) }}؟')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            حذف
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Status Badge — دائماً ظاهر --}}
        <div class="mt-2">
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold {{ $project->status->tailwindBadge() }}">
                {{ $project->status->icon() }} {{ $project->status->label() }}
            </span>
        </div>

        {{-- Description --}}
        @if($project->description)
            <p class="mt-3 text-sm text-gray-500 line-clamp-2">{{ $project->description }}</p>
        @endif

        {{-- Financial Mini Summary --}}
        @php
            $income   = $project->totalIncome();
            $expenses = $project->totalExpenses();
            $net      = $project->netProfit();
        @endphp

        <div class="mt-4 grid grid-cols-3 gap-2 text-center">
            <div class="bg-green-50 rounded-xl p-2">
                <p class="text-xs text-gray-400 mb-0.5">دخل</p>
                <p class="text-xs font-bold text-green-700">{{ number_format($income, 0) }}</p>
            </div>
            <div class="bg-red-50 rounded-xl p-2">
                <p class="text-xs text-gray-400 mb-0.5">مصروف</p>
                <p class="text-xs font-bold text-red-700">{{ number_format($expenses, 0) }}</p>
            </div>
            <div class="{{ $net >= 0 ? 'bg-indigo-50' : 'bg-red-50' }} rounded-xl p-2">
                <p class="text-xs text-gray-400 mb-0.5">صافي</p>
                <p class="text-xs font-bold {{ $net >= 0 ? 'text-indigo-700' : 'text-red-700' }}">
                    {{ $net >= 0 ? '+' : '' }}{{ number_format($net, 0) }}
                </p>
            </div>
        </div>

        {{-- Footer --}}
        <div class="mt-3 flex items-center justify-between text-xs text-gray-400">
            <span>{{ $project->transactions_count ?? 0 }} معاملة</span>
            <a href="{{ route('projects.show', $project) }}"
               class="text-indigo-600 hover:text-indigo-700 font-medium">
                عرض التفاصيل ←
            </a>
        </div>
    </div>
</div>

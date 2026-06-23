<div class="px-5 py-4 dash-row group">

    <div class="flex items-start justify-between gap-4">

        {{-- Icon + Info --}}
        <div class="flex items-start gap-3 flex-1 min-w-0">

            {{-- Status Icon --}}
            <div class="shrink-0 mt-0.5">
                @if($debt->isPaid())
                    <div class="w-9 h-9 rounded-xl bg-green-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                @elseif($debt->isOverdue())
                    <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                @else
                    <div class="w-9 h-9 rounded-xl bg-brand-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-brand/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                @endif
            </div>

            {{-- Details --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <p class="text-sm font-semibold text-ink">{{ $debt->party_name }}</p>

                    {{-- Status Badge --}}
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-xs font-medium
                        @if($debt->status === \App\Support\Enums\DebtStatus::Paid)
                            bg-green-100 text-green-700
                        @elseif($debt->status === \App\Support\Enums\DebtStatus::PartiallyPaid)
                            bg-yellow-100 text-yellow-700
                        @else
                            bg-red-100 text-red-700
                        @endif">
                        {{ $debt->status->label() }}
                    </span>

                    @if($debt->isOverdue())
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-xs font-medium bg-red-100 text-red-700">
                            ⚠️ متأخر
                        </span>
                    @endif
                </div>

                {{-- Amount Info --}}
                <div class="mt-1 flex items-center gap-3 text-xs text-muted">
                    <span>الإجمالي: <strong class="text-slate-700 nums">{{ number_format($debt->amount, 2) }} {{ $debt->currency }}</strong></span>
                    @if(!$debt->isPaid())
                        <span class="text-muted/40">|</span>
                        <span>المتبقي: <strong class="text-red-600 nums">{{ number_format($debt->remaining_amount, 2) }} {{ $debt->currency }}</strong></span>
                    @endif
                    @if($debt->due_date)
                        <span class="text-muted/40">|</span>
                        <span class="nums {{ $debt->isOverdue() ? 'text-red-600 font-medium' : '' }}">
                            استحقاق: {{ $debt->due_date->format('d/m/Y') }}
                        </span>
                    @endif
                </div>

                {{-- Progress Bar --}}
                @if(!$debt->isPaid())
                    @php $pct = $debt->paidPercentage(); @endphp
                    <div class="mt-2.5">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-xs text-muted">تقدم السداد</span>
                            <span class="text-xs font-medium text-slate-600">{{ $pct }}%</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full transition-all
                                @if($pct >= 100) bg-green-500
                                @elseif($pct >= 50) bg-yellow-400
                                @else bg-red-400
                                @endif"
                                 style="width: {{ min($pct, 100) }}%">
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Notes --}}
                @if($debt->notes)
                    <p class="mt-1.5 text-xs text-muted truncate">📝 {{ $debt->notes }}</p>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-1.5 shrink-0">
            @if(!$debt->isPaid())
                {{-- Record Payment Button --}}
                <button type="button"
                        @click="openPayModal(
                            '{{ $debt->id }}',
                            '{{ addslashes($debt->party_name) }}',
                            {{ $debt->remaining_amount }},
                            '{{ $debt->currency }}'
                        )"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5
                               bg-brand-50 hover:bg-brand-100 text-brand-600
                               text-xs font-medium rounded-lg transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                    </svg>
                    دفعة
                </button>

                {{-- Mark as Paid --}}
                <form method="POST" action="{{ route('debts.mark-paid', $debt) }}"
                      onsubmit="return confirm('تحديد هذا الدين كمدفوع بالكامل؟')">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5
                                   bg-green-50 hover:bg-green-100 text-green-700
                                   text-xs font-medium rounded-lg transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        مسدَّد
                    </button>
                </form>
            @else
                <span class="text-xs text-green-600 font-medium px-2 py-1">
                    ✅ مسدَّد بالكامل
                </span>
            @endif

            {{-- Delete --}}
            <form method="POST" action="{{ route('debts.destroy', $debt) }}"
                  onsubmit="return confirm('حذف هذا الدين نهائياً؟')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="p-1.5 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition opacity-0 group-hover:opacity-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </form>
        </div>

    </div>
</div>

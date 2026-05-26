{{--
    x-crm-follow-up-card
    Props:
        $followUp  — ClientFollowUp model (with client relation)
        $color     — 'red' | 'amber' | 'blue'  (column color theme)
--}}
@props(['followUp', 'color' => 'amber'])

@php
    $daysLeft   = $followUp->daysUntilDue();
    $isOverdue  = $followUp->isOverdue();
    $status     = $followUp->actualStatus();

    $typeIcons = [
        'call'    => '📞',
        'email'   => '📧',
        'meeting' => '🤝',
        'task'    => '✅',
        'other'   => '📌',
    ];
    $typeIcon = $typeIcons[$followUp->type ?? 'other'] ?? '📌';

    $priorityConfig = [
        1 => ['label' => 'عالية',    'class' => 'bg-red-100 text-red-700'],
        2 => ['label' => 'متوسطة',   'class' => 'bg-amber-100 text-amber-700'],
        3 => ['label' => 'منخفضة',   'class' => 'bg-green-100 text-green-700'],
    ];
    $priority = $priorityConfig[$followUp->priority ?? 2] ?? $priorityConfig[2];

    $borderColors = [
        'red'   => 'border-red-300',
        'amber' => 'border-amber-300',
        'blue'  => 'border-blue-300',
    ];
    $stripColors = [
        'red'   => 'bg-red-500',
        'amber' => 'bg-amber-500',
        'blue'  => 'bg-blue-500',
    ];
    $border = $borderColors[$color] ?? 'border-gray-200';
    $strip  = $stripColors[$color] ?? 'bg-gray-400';

    $clientPublicId = $followUp->client->public_id ?? null;
    $clientName     = $followUp->client->name ?? '—';
@endphp

<div x-data="followUpCard('{{ $followUp->id }}', '{{ $clientPublicId }}')"
     class="bg-white rounded-xl border {{ $border }} shadow-sm overflow-hidden group hover:shadow-md transition">

    {{-- شريط اللون الجانبي --}}
    <div class="flex">
        <div class="w-1 {{ $strip }} shrink-0"></div>

        <div class="flex-1 p-4">

            {{-- Header: العميل + الأيقونة --}}
            <div class="flex items-start justify-between gap-2 mb-2">
                <div class="flex-1 min-w-0">
                    @if($clientPublicId)
                        <a href="{{ route('clients.show', $clientPublicId) }}"
                           class="text-sm font-semibold text-indigo-700 hover:text-indigo-900 truncate block">
                            {{ $clientName }}
                        </a>
                    @else
                        <span class="text-sm font-semibold text-gray-800 truncate block">{{ $clientName }}</span>
                    @endif
                </div>
                <span class="text-base shrink-0">{{ $typeIcon }}</span>
            </div>

            {{-- العنوان --}}
            <p class="text-sm text-gray-800 font-medium mb-3 leading-snug">{{ $followUp->title }}</p>

            {{-- Meta: الأولوية + التاريخ --}}
            <div class="flex flex-wrap items-center gap-2 mb-3">
                <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $priority['class'] }}">
                    {{ $priority['label'] }}
                </span>
                <span class="text-xs text-gray-500">
                    {{ $followUp->due_at->translatedFormat('j M H:i') }}
                </span>
                @if($isOverdue)
                    <span class="text-xs text-red-600 font-semibold">
                        (منذ {{ abs($daysLeft) }} {{ abs($daysLeft) == 1 ? 'يوم' : 'أيام' }})
                    </span>
                @elseif($daysLeft === 0)
                    <span class="text-xs text-amber-600 font-semibold">اليوم</span>
                @else
                    <span class="text-xs text-blue-600">
                        بعد {{ $daysLeft }} {{ $daysLeft == 1 ? 'يوم' : 'أيام' }}
                    </span>
                @endif
            </div>

            @if($followUp->notes)
                <p class="text-xs text-gray-500 mb-3 line-clamp-2">{{ $followUp->notes }}</p>
            @endif

            {{-- الأزرار --}}
            <div class="flex items-center gap-2" x-show="!done">

                {{-- إتمام --}}
                <button @click="complete()"
                        :disabled="loading"
                        class="flex-1 flex items-center justify-center gap-1 px-3 py-1.5 text-xs font-medium bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 transition">
                    <span x-show="!loading">✅ إتمام</span>
                    <span x-show="loading">…</span>
                </button>

                {{-- تأجيل — يفتح Modal إضافة متابعة جديدة بنفس العميل --}}
                <button @click="reschedule()"
                        :disabled="loading"
                        class="px-3 py-1.5 text-xs font-medium border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 disabled:opacity-50 transition">
                    📅 تأجيل
                </button>

                {{-- إلغاء --}}
                <button @click="cancel()"
                        :disabled="loading"
                        class="px-3 py-1.5 text-xs font-medium text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg disabled:opacity-50 transition">
                    ✕
                </button>
            </div>

            {{-- حالة بعد الإتمام/الإلغاء --}}
            <div x-show="done" x-cloak class="text-center py-1">
                <span class="text-sm" x-text="doneMsg"></span>
            </div>

        </div>
    </div>
</div>

<script>
function followUpCard(followUpId, clientPublicId) {
    return {
        followUpId,
        clientPublicId,
        loading: false,
        done: false,
        doneMsg: '',

        async complete() {
            if (!confirm('هل أتممت هذه المتابعة؟')) return;
            this.loading = true;
            try {
                const res = await fetch(
                    `/clients/${this.clientPublicId}/follow-ups/${this.followUpId}/complete`,
                    {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    }
                );
                if (!res.ok) throw new Error('خطأ في الإتمام');
                this.done    = true;
                this.doneMsg = '✅ تم إتمامها';
                window.dispatchEvent(new CustomEvent('show-toast', { detail: { msg: '✅ تم إتمام المتابعة.' } }));
            } catch (e) {
                window.dispatchEvent(new CustomEvent('show-toast', { detail: { msg: '❌ ' + e.message, error: true } }));
            } finally {
                this.loading = false;
            }
        },

        async cancel() {
            if (!confirm('إلغاء هذه المتابعة؟')) return;
            this.loading = true;
            try {
                const res = await fetch(
                    `/clients/${this.clientPublicId}/follow-ups/${this.followUpId}/cancel`,
                    {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    }
                );
                if (!res.ok) throw new Error('خطأ في الإلغاء');
                this.done    = true;
                this.doneMsg = '❌ ملغاة';
                window.dispatchEvent(new CustomEvent('show-toast', { detail: { msg: '🗑️ تم إلغاء المتابعة.' } }));
            } catch (e) {
                window.dispatchEvent(new CustomEvent('show-toast', { detail: { msg: '❌ ' + e.message, error: true } }));
            } finally {
                this.loading = false;
            }
        },

        reschedule() {
            // يفتح Modal الإضافة السريعة مع تحديد العميل تلقائياً
            window.dispatchEvent(new CustomEvent('open-add-modal'));
        },
    };
}
</script>

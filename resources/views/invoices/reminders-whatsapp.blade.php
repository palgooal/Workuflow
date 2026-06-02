@extends('layouts.app')

@section('title', 'تذكيرات واتساب المعلّقة')

@section('content')
<div class="max-w-4xl mx-auto space-y-4">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">تذكيرات واتساب المعلّقة</h1>
            <p class="text-sm text-gray-500 mt-0.5">فواتير تحتاج إرسال تذكير عبر واتساب</p>
        </div>
        <a href="{{ route('invoices.index') }}"
           class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            الفواتير
        </a>
    </div>

    {{-- Info Banner --}}
    <div class="bg-green-50 border border-green-200 rounded-xl p-4 flex items-start gap-3 text-sm text-green-800">
        <svg class="w-5 h-5 text-green-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </svg>
        <div>
            <p class="font-semibold">كيف يعمل؟</p>
            <p class="text-xs mt-0.5 text-green-700">النظام يُسجّل التذكيرات تلقائياً كل صباح. اضغط "إرسال" لفتح واتساب برسالة جاهزة، ثم "تم الإرسال" لإزالتها من القائمة.</p>
        </div>
    </div>

    {{-- القائمة --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-800">التذكيرات المعلّقة</h3>
            <span class="text-xs text-gray-400">{{ $pendingReminders->total() }} تذكير</span>
        </div>

        @if($pendingReminders->isEmpty())
            <div class="py-16 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-medium">لا توجد تذكيرات معلّقة</p>
                <p class="text-xs mt-1">ستظهر هنا التذكيرات التي يولّدها النظام تلقائياً كل صباح</p>
            </div>
        @else
            <div class="divide-y divide-gray-50">
                @foreach($pendingReminders as $reminder)
                @php
                    $typeLabel = $reminder->type === 'before_due' ? 'قبل الاستحقاق' : 'متأخرة';
                    $typeBadge = $reminder->type === 'before_due' ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700';
                    $phone = preg_replace('/[^0-9]/', '', $reminder->client_phone ?? '');
                    $message = $reminder->type === 'before_due'
                        ? "مرحباً {$reminder->client_name}،\n\nتذكير بأن الفاتورة رقم *{$reminder->number}* بمبلغ *" . number_format($reminder->total, 2) . " {$reminder->currency}* تستحق بتاريخ " . \Carbon\Carbon::parse($reminder->due_date)->format('Y/m/d') . ".\n\nنرجو السداد في الوقت المحدد 🙏"
                        : "مرحباً {$reminder->client_name}،\n\nنودّ تنبيهك بأن الفاتورة رقم *{$reminder->number}* بمبلغ *" . number_format($reminder->total, 2) . " {$reminder->currency}* تجاوزت تاريخ الاستحقاق.\n\nنرجو التواصل لتسوية المبلغ في أقرب وقت 🙏";
                    $waUrl = 'https://wa.me/' . $phone . '?text=' . rawurlencode($message);
                @endphp
                <div class="flex items-center gap-4 px-5 py-4" id="reminder-{{ $reminder->id }}">

                    {{-- Badge النوع --}}
                    <span class="shrink-0 text-xs px-2.5 py-1 rounded-full font-medium {{ $typeBadge }}">
                        {{ $typeLabel }}
                    </span>

                    {{-- معلومات --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800">{{ $reminder->client_name }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $reminder->number }} —
                            {{ number_format($reminder->total, 2) }} {{ $reminder->currency }}
                            @if($reminder->due_date)
                                · استحقاق {{ \Carbon\Carbon::parse($reminder->due_date)->format('Y/m/d') }}
                            @endif
                        </p>
                    </div>

                    {{-- الأزرار --}}
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ $waUrl }}" target="_blank"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs text-green-700 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                            إرسال
                        </a>

                        <button onclick="markSent({{ $reminder->id }}, this)"
                                class="text-xs px-3 py-1.5 text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                            تم الإرسال ✓
                        </button>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="px-5 py-4 border-t border-gray-100">
                {{ $pendingReminders->links() }}
            </div>
        @endif
    </div>

</div>

<script>
async function markSent(logId, btn) {
    btn.disabled = true;
    btn.textContent = '...';
    try {
        const res = await fetch(`{{ url('/invoices/reminders') }}/${logId}/mark-sent`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
            },
        });
        if (res.ok) {
            document.getElementById('reminder-' + logId).remove();
        }
    } catch (e) {
        btn.disabled = false;
        btn.textContent = 'تم الإرسال ✓';
    }
}
</script>

@endsection

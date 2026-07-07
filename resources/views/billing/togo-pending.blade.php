@extends('layouts.app')

@section('title', 'تأكيد الدفع')

@section('content')
<div class="max-w-md mx-auto space-y-6">

    {{-- Header --}}
    <div class="text-center space-y-3">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-brand-100 dark:bg-brand-900/40 rounded-2xl mb-2">
            <svg class="w-8 h-8 text-brand dark:text-brand/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
        <h1 class="text-xl font-bold text-ink">الدفع الآمن عبر Togo.ps</h1>
        <p class="text-sm text-muted max-w-sm mx-auto">
            ستُفتح نافذة دفع آمنة صغيرة وتبقى هذه الصفحة مفتوحة — وتنتقل تلقائياً بعد اكتمال الدفع أو إلغائه.
        </p>
    </div>

    {{-- Order Summary --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 space-y-4">
        <h2 class="font-semibold text-slate-900 dark:text-white text-sm border-b border-slate-100 dark:border-slate-800 pb-3">
            ملخص الطلب
        </h2>

        <div class="space-y-3">
            <div class="flex items-center justify-between text-sm">
                <span class="text-muted">الخطة</span>
                <span class="font-semibold text-slate-900 dark:text-white">{{ $planLabel }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-muted">دورة الفوترة</span>
                <span class="font-medium text-slate-700 dark:text-slate-300">{{ $cycleLabel }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-muted">رقم الطلب</span>
                <span class="font-mono text-xs text-slate-400 dark:text-slate-500">{{ substr($order->id, 0, 12) }}…</span>
            </div>
            <div class="border-t border-slate-100 dark:border-slate-800 pt-3 flex items-center justify-between">
                <span class="font-semibold text-slate-900 dark:text-white text-sm">المبلغ الإجمالي</span>
                <span class="font-bold text-2xl text-brand">
                    ${{ number_format((float)$order->amount, 0) }}
                    <span class="text-sm font-medium text-muted">{{ $order->currency }}</span>
                </span>
            </div>
        </div>
    </div>

    {{-- Trust indicators --}}
    <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl border border-emerald-200 dark:border-emerald-800 px-5 py-4">
        <div class="flex items-center gap-2 mb-2">
            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <span class="text-xs font-semibold text-emerald-800 dark:text-emerald-200">الدفع مؤمَّن وموثوق</span>
        </div>
        <div class="flex items-center gap-3 text-xs text-emerald-700 dark:text-emerald-300 flex-wrap">
            <span class="flex items-center gap-1">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                Visa &amp; Mastercard
            </span>
            <span class="text-emerald-400">·</span>
            <span class="flex items-center gap-1">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                تشفير SSL 256-bit
            </span>
            <span class="text-emerald-400">·</span>
            <span class="flex items-center gap-1">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                بوابة Togo.ps المرخّصة
            </span>
        </div>
    </div>

    {{-- Actions --}}
    <div class="space-y-3">
        {{-- Primary: proceed to Togo checkout — نافذة منبثقة بدل تحويل كامل --}}
        <button type="button" id="togo-pay-btn" data-checkout-url="{{ $checkoutUrl }}"
                class="flex items-center justify-center gap-2 w-full py-3.5 bg-brand text-white font-semibold rounded-xl hover:bg-brand-600 transition text-sm disabled:opacity-60 disabled:cursor-not-allowed">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
            <span id="togo-pay-label">متابعة إلى الدفع</span>
        </button>

        {{-- Secondary: back to plan selection --}}
        <a href="{{ route('billing.upgrade') }}"
           class="block text-center text-sm text-muted hover:text-slate-600 dark:hover:text-slate-300 transition py-2">
            ← العودة لاختيار الخطة
        </a>
    </div>

    {{-- Redirect notice --}}
    <p class="text-center text-xs text-slate-400 dark:text-slate-500 leading-relaxed">
        بالضغط على "متابعة إلى الدفع"، تُفتح نافذة دفع آمنة صغيرة من Togo.ps.
        بعد اكتمال الدفع أو إلغائه، تنتقل هذه الصفحة تلقائياً للنتيجة.
    </p>

</div>

@push('scripts')
<script>
(function () {
    var btn   = document.getElementById('togo-pay-btn');
    var label = document.getElementById('togo-pay-label');
    if (!btn) return;

    var POPUP_NAME = 'darahem_togo_payment';
    var popup = null;
    var pollTimer = null;

    function popupFeatures() {
        var w = 480, h = 720;
        var left = (window.screenX || 0) + Math.max(0, (window.outerWidth  - w) / 2);
        var top  = (window.screenY || 0) + Math.max(0, (window.outerHeight - h) / 2);
        return 'width=' + w + ',height=' + h + ',left=' + left + ',top=' + top + ',resizable=yes,scrollbars=yes';
    }

    btn.addEventListener('click', function () {
        var url = btn.dataset.checkoutUrl;

        // الرابط جاهز من الخادم مسبقاً (لا حاجة لـ fetch) — window.open()
        // يُستدعى هنا مباشرة ومتزامناً مع الضغطة نفسها، وإلا يفقد "بصمة
        // المستخدم" ويُفتح كتبويب متصفح كامل بدل نافذة منبثقة مضغوطة.
        popup = window.open(url, POPUP_NAME, popupFeatures());

        if (!popup) {
            // popup blocker — تراجع للتحويل الكامل بنفس الصفحة كحل أخير.
            window.location.href = url;
            return;
        }

        popup.focus();
        btn.disabled = true;
        label.textContent = 'جارِ إتمام الدفع في النافذة المنبثقة...';

        // لا يوجد postMessage من Togo — نتابع دورياً هل أُغلقت النافذة
        // (تلقائياً بعد النجاح/الفشل عبر partials/togo-popup-return، أو
        // يدوياً من المستخدم) لنُحدِّث هذه الصفحة وتعكس الحالة الفعلية.
        if (pollTimer) clearInterval(pollTimer);
        pollTimer = setInterval(function () {
            if (popup.closed) {
                clearInterval(pollTimer);
                window.location.reload();
            }
        }, 700);
    });
})();
</script>
@endpush
@endsection

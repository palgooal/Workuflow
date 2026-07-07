{{--
    يُضمَّن في صفحات نتيجة الدفع (success / failed / upgrade بعد الإلغاء) —
    نفس أسلوب resources/views/invoices/pay.blade.php لكن مع فارق مهم: هنا
    صفحة "قبل الدفع" (togo-pending) مختلفة عن صفحة "بعد الدفع" (هذه الصفحة)،
    فبدل إغلاق النافذة فقط، ننقل الصفحة الأصلية (opener) لنفس رابط النتيجة
    أولاً حتى يشوف المستخدم رسالة النجاح/الفشل الصحيحة في تبويبه الأصلي،
    ثم نُغلق النافذة المنبثقة.

    window.name === 'darahem_togo_payment' يُميّز نافذة الدفع تحديداً عن أي
    تبويب/نافذة أخرى مفتوحة بشكل طبيعي على نفس الرابط.
--}}
@push('scripts')
<script>
(function () {
    if (window.opener && !window.opener.closed && window.name === 'darahem_togo_payment') {
        window.opener.location.href = window.location.href;
        window.close();
    }
})();
</script>
@endpush

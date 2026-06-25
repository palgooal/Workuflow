<div class="space-y-4 p-1">

    {{-- Row helper --}}
    @php
        $row = fn(string $label, $value, string $class = '') =>
            "<div class='flex justify-between text-sm py-1 border-b border-gray-100 dark:border-gray-700'>
                <span class='text-gray-500 dark:text-gray-400 font-medium'>{$label}</span>
                <span class='font-mono text-gray-900 dark:text-white {$class}'>" . e($value ?? '—') . "</span>
            </div>";
    @endphp

    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 space-y-1">
        {!! $row('ULID', $order->id) !!}
        {!! $row('المستخدم', ($order->user?->name ?? '—') . ' <' . ($order->user?->email ?? '') . '>') !!}
        {!! $row('الخطة', $order->plan) !!}
        {!! $row('دورة الفوترة', match($order->cycle) {
            'monthly' => 'شهري',
            'annual'  => 'سنوي (12 شهراً)',
            default   => $order->cycle ?? '—',
        }) !!}
        {!! $row('المبلغ', number_format((float)$order->amount, 2) . ' ' . $order->currency) !!}
        {!! $row('مزود الدفع', $order->provider ?? '—') !!}
        {!! $row('رقم الطلب الخارجي', $order->provider_order_id ?? '—') !!}
        {!! $row('Hashed ID', $order->provider_hashed_id ?? '—') !!}
        {!! $row('الحالة', match($order->status) {
            'pending'   => 'قيد الانتظار',
            'paid'      => 'مدفوع ✅',
            'failed'    => 'فشل ❌',
            'cancelled' => 'ملغى',
            default     => $order->status,
        }) !!}
        {!! $row('تاريخ الإنشاء', $order->created_at?->format('Y/m/d H:i:s') ?? '—') !!}
        {!! $row('تاريخ الدفع', $order->paid_at?->format('Y/m/d H:i:s') ?? '—') !!}
        {!! $row('تاريخ الفشل', $order->failed_at?->format('Y/m/d H:i:s') ?? '—') !!}
    </div>

    @if(!empty($order->metadata['checkout_url']))
    <div class="text-sm">
        <span class="text-gray-500 dark:text-gray-400 font-medium">رابط الدفع:</span>
        <a href="{{ $order->metadata['checkout_url'] }}"
           target="_blank"
           class="text-blue-600 dark:text-blue-400 hover:underline break-all ml-2">
            {{ $order->metadata['checkout_url'] }}
        </a>
    </div>
    @endif

</div>

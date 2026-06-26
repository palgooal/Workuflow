@php
    $eventLabels = [
        'order.created'              => ['label' => 'إنشاء الطلب',               'icon' => '🟢', 'tab' => 'internal'],
        'user.redirected_to_gateway' => ['label' => 'توجيه المستخدم للبوابة',     'icon' => '↗️',  'tab' => 'gateway'],
        'callback.received'          => ['label' => 'استقبال callback من البوابة', 'icon' => '📥', 'tab' => 'gateway'],
        'payment.marked_paid'        => ['label' => 'تأكيد الدفع',               'icon' => '✅', 'tab' => 'internal'],
        'payment.failed'             => ['label' => 'فشل الدفع',                 'icon' => '❌', 'tab' => 'internal'],
        'subscription.activated'     => ['label' => 'تفعيل الاشتراك',            'icon' => '🚀', 'tab' => 'subscription'],
        'notification.sent'          => ['label' => 'إرسال الإشعار',              'icon' => '📧', 'tab' => 'internal'],
    ];

    $timeline = $order->getTimelineEvents();
    $gatewayData = collect($order->metadata ?? [])->except(['timeline_events', 'checkout_url', 'billing_cycle', 'charged_months', 'displayed_monthly_price', 'plan'])->toArray();
@endphp

<div x-data="{ tab: 'timeline' }" class="space-y-4 p-1">

    {{-- Tabs --}}
    <div class="flex gap-1 border-b border-gray-200 dark:border-gray-700 text-sm">
        @foreach([
            ['id' => 'timeline',     'label' => 'Timeline'],
            ['id' => 'gateway',      'label' => 'استجابة البوابة'],
            ['id' => 'order',        'label' => 'تفاصيل الطلب'],
            ['id' => 'subscription', 'label' => 'الاشتراك'],
        ] as $t)
        <button
            @click="tab = '{{ $t['id'] }}'"
            :class="tab === '{{ $t['id'] }}' ? 'border-b-2 border-primary-500 text-primary-600 font-semibold' : 'text-gray-500 hover:text-gray-700'"
            class="px-3 py-2 transition">
            {{ $t['label'] }}
        </button>
        @endforeach
    </div>

    {{-- Timeline Tab --}}
    <div x-show="tab === 'timeline'" class="space-y-1">
        @if(empty($timeline))
            <p class="text-gray-500 text-sm text-center py-4">لا توجد أحداث مسجّلة بعد.</p>
        @else
        <ol class="relative border-r border-gray-200 dark:border-gray-700 me-3">
            @foreach($timeline as $event)
            @php
                $meta  = $eventLabels[$event['event']] ?? ['label' => $event['event'], 'icon' => '•'];
                $extra = collect($event)->except(['event', 'at'])->toArray();
            @endphp
            <li class="mb-4 ms-4">
                <span class="absolute -start-1.5 flex h-3 w-3 items-center justify-center rounded-full ring-4 ring-white dark:ring-gray-900 bg-primary-100"></span>
                <div class="flex items-start gap-2">
                    <span class="text-base leading-none">{{ $meta['icon'] }}</span>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-gray-100 text-sm">{{ $meta['label'] }}</p>
                        <time class="text-xs text-gray-400 font-mono">{{ \Carbon\Carbon::parse($event['at'])->format('Y-m-d H:i:s') }}</time>
                        @if($extra)
                        <div class="mt-1 text-xs text-gray-500 space-y-0.5">
                            @foreach($extra as $k => $v)
                            <span class="inline-block bg-gray-100 dark:bg-gray-800 rounded px-1.5 py-0.5 me-1">{{ $k }}: <strong>{{ is_scalar($v) ? $v : json_encode($v) }}</strong></span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </li>
            @endforeach
        </ol>
        @endif
    </div>

    {{-- Gateway Response Tab --}}
    <div x-show="tab === 'gateway'">
        @if(empty($gatewayData))
            <p class="text-gray-500 text-sm text-center py-4">لا توجد بيانات بوابة مخزّنة.</p>
        @else
        <pre class="bg-gray-900 text-green-400 rounded-lg p-4 text-xs overflow-auto max-h-72 font-mono leading-relaxed">{{ json_encode($gatewayData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
        @endif
    </div>

    {{-- Order Details Tab --}}
    <div x-show="tab === 'order'" class="grid grid-cols-2 gap-3 text-sm">
        @foreach([
            ['label' => 'ULID',            'value' => $order->id],
            ['label' => 'المستخدم',        'value' => ($order->user?->name ?? '—') . ' (' . ($order->user?->email ?? '') . ')'],
            ['label' => 'الخطة',           'value' => ucfirst($order->plan ?? '—')],
            ['label' => 'دورة الفوترة',    'value' => match($order->cycle ?? '') { 'monthly' => 'شهري', 'annual' => 'سنوي', default => $order->cycle }],
            ['label' => 'المبلغ',          'value' => number_format($order->amount, 2) . ' ' . ($order->currency ?? '')],
            ['label' => 'مزود الدفع',      'value' => ucfirst($order->provider ?? '—')],
            ['label' => 'رقم الطلب Togo',  'value' => $order->provider_order_id ?? '—'],
            ['label' => 'الحالة',          'value' => $order->status],
            ['label' => 'تاريخ الإنشاء',   'value' => $order->created_at?->format('Y-m-d H:i:s') ?? '—'],
            ['label' => 'تاريخ الدفع',     'value' => $order->paid_at?->format('Y-m-d H:i:s') ?? '—'],
            ['label' => 'تاريخ الفشل',     'value' => $order->failed_at?->format('Y-m-d H:i:s') ?? '—'],
        ] as $row)
        <div>
            <span class="text-gray-500 font-semibold text-xs block">{{ $row['label'] }}</span>
            <span class="text-gray-900 dark:text-gray-100 font-mono break-all">{{ $row['value'] }}</span>
        </div>
        @endforeach
        @if(!empty($order->metadata['checkout_url']))
        <div class="col-span-2">
            <span class="text-gray-500 font-semibold text-xs block">رابط الدفع</span>
            <a href="{{ $order->metadata['checkout_url'] }}" target="_blank"
               class="text-primary-500 hover:underline text-xs break-all font-mono">{{ $order->metadata['checkout_url'] }}</a>
        </div>
        @endif
    </div>

    {{-- Subscription Activation Tab --}}
    <div x-show="tab === 'subscription'" class="text-sm space-y-3">
        @php
            $sub = \App\Models\Subscription::where('user_id', $order->user_id)
                ->where('provider_subscription_id', $order->provider_order_id)
                ->latest()
                ->first();
        @endphp
        @if($sub)
        <div class="grid grid-cols-2 gap-3">
            @foreach([
                ['label' => 'حالة الاشتراك',   'value' => $sub->status],
                ['label' => 'الخطة',           'value' => ucfirst($sub->plan ?? '—')],
                ['label' => 'دورة الفوترة',    'value' => match($sub->cycle ?? '') { 'monthly' => 'شهري', 'annual' => 'سنوي', default => $sub->cycle ?? '—' }],
                ['label' => 'يبدأ',            'value' => $sub->starts_at?->format('Y-m-d') ?? '—'],
                ['label' => 'ينتهي',           'value' => $sub->ends_at?->format('Y-m-d') ?? '—'],
                ['label' => 'مفعَّل في',       'value' => $sub->created_at?->format('Y-m-d H:i:s') ?? '—'],
            ] as $row)
            <div>
                <span class="text-gray-500 font-semibold text-xs block">{{ $row['label'] }}</span>
                <span class="text-gray-900 dark:text-gray-100">{{ $row['value'] }}</span>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-gray-500 text-sm text-center py-4">
            @if($order->status === 'paid')
                لم يُعثر على سجل اشتراك مرتبط بهذا الطلب.
            @else
                الاشتراك لم يُفعَّل بعد (الطلب {{ $order->status }}).
            @endif
        </p>
        @endif
    </div>

</div>

<div class="space-y-4 p-1">
    <div class="grid grid-cols-2 gap-3 text-sm">
        <div>
            <span class="font-semibold text-gray-500">المزود</span>
            <p class="font-mono text-gray-900 dark:text-gray-100">{{ $callback->provider }}</p>
        </div>
        <div>
            <span class="font-semibold text-gray-500">Order ID</span>
            <p class="font-mono text-gray-900 dark:text-gray-100">{{ $callback->order_id ?? '—' }}</p>
        </div>
        <div>
            <span class="font-semibold text-gray-500">محاولات</span>
            <p class="text-gray-900 dark:text-gray-100">{{ $callback->retries }}</p>
        </div>
        <div>
            <span class="font-semibold text-gray-500">الحالة</span>
            <p class="text-gray-900 dark:text-gray-100">{{ $callback->resolved ? '✅ محلول' : '❌ غير محلول' }}</p>
        </div>
    </div>

    @if($callback->payload)
    <div>
        <span class="font-semibold text-gray-500 text-sm">Payload البيانات</span>
        <pre class="mt-1 bg-gray-900 text-green-400 rounded-lg p-4 text-xs overflow-auto max-h-48 font-mono leading-relaxed">{{ json_encode($callback->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
    </div>
    @endif

    @if($callback->exception)
    <div>
        <span class="font-semibold text-gray-500 text-sm">تفاصيل الخطأ</span>
        <pre class="mt-1 bg-red-950 text-red-300 rounded-lg p-4 text-xs overflow-auto max-h-48 font-mono leading-relaxed">{{ $callback->exception }}</pre>
    </div>
    @endif
</div>

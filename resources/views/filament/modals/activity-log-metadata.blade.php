<div class="space-y-3 p-1">
    <div class="grid grid-cols-2 gap-3 text-sm">
        <div>
            <span class="font-semibold text-gray-500">الحدث</span>
            <p class="font-mono text-gray-900 dark:text-gray-100">{{ $log->event_type }}</p>
        </div>
        <div>
            <span class="font-semibold text-gray-500">الوقت</span>
            <p class="text-gray-900 dark:text-gray-100">{{ $log->created_at->format('Y-m-d H:i:s') }}</p>
        </div>
        @if($log->ip_address)
        <div>
            <span class="font-semibold text-gray-500">IP</span>
            <p class="font-mono text-gray-900 dark:text-gray-100">{{ $log->ip_address }}</p>
        </div>
        @endif
        @if($log->entity_type)
        <div>
            <span class="font-semibold text-gray-500">الكيان</span>
            <p class="font-mono text-gray-900 dark:text-gray-100">{{ class_basename($log->entity_type) }} #{{ $log->entity_id }}</p>
        </div>
        @endif
    </div>

    @if($log->metadata)
    <div>
        <span class="font-semibold text-gray-500 text-sm">البيانات الإضافية</span>
        <pre class="mt-1 bg-gray-900 text-green-400 rounded-lg p-4 text-xs overflow-auto max-h-64 font-mono leading-relaxed">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
    </div>
    @endif

    @if($log->user_agent)
    <div>
        <span class="font-semibold text-gray-500 text-sm">User Agent</span>
        <p class="text-xs text-gray-600 dark:text-gray-400 break-all mt-1">{{ $log->user_agent }}</p>
    </div>
    @endif
</div>

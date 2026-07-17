@php($status = $this->scheduleStatus())

<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">حالة الجدولة</x-slot>

        <dl class="grid grid-cols-1 gap-x-4 gap-y-4 text-sm sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">آخر نسخة مجدولة</dt>
                <dd class="text-gray-950 dark:text-white">
                    {{ $status['last_scheduled_backup']?->name ?? 'لا توجد بعد' }}
                </dd>
            </div>

            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">آخر وقت تشغيل</dt>
                <dd class="text-gray-950 dark:text-white">
                    {{ $status['last_run_at']?->format('Y-m-d H:i:s') ?? '—' }}
                </dd>
            </div>

            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">آخر نتيجة</dt>
                <dd>
                    @if ($status['last_result'])
                        <x-filament::badge :color="$status['last_result']->color()">
                            {{ $status['last_result']->label() }}
                        </x-filament::badge>
                    @else
                        <span class="text-gray-950 dark:text-white">—</span>
                    @endif
                </dd>
            </div>

            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">موعد التشغيل القادم</dt>
                <dd class="text-gray-950 dark:text-white space-y-1">
                    <div>قاعدة بيانات: {{ $status['next_database_run']?->format('Y-m-d H:i') ?? '—' }}</div>
                    <div>كاملة: {{ $status['next_full_run']?->format('Y-m-d H:i') ?? '—' }}</div>
                </dd>
            </div>
        </dl>
    </x-filament::section>

    <x-filament::section>
        <form wire:submit="save">
            {{ $this->form }}

            <div class="mt-6">
                <x-filament::button type="submit" color="success" icon="heroicon-o-check">
                    حفظ الإعدادات
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</x-filament-panels::page>

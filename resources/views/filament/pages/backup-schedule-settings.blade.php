{{--
    $scheduleStatus وَ$systemInfo خصائص Livewire عامة مُحسَّبة مرة واحدة فقط في
    BackupScheduleSettings::mount()/save() (وليس هنا في الـBlade) — لا يُعاد
    استعلامها مع كل Render يسبّبه تفاعل المستخدم مع النموذج. راجع docblock
    الصفحة (المرحلة التاسعة: Backup Policies & Settings UI).
--}}
<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">حالة الجدولة</x-slot>

        <dl class="grid grid-cols-1 gap-x-4 gap-y-4 text-sm sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">آخر نسخة مجدولة</dt>
                <dd class="text-gray-950 dark:text-white">
                    {{ $scheduleStatus['last_scheduled_backup']?->name ?? 'لا توجد بعد' }}
                </dd>
            </div>

            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">آخر وقت تشغيل</dt>
                <dd class="text-gray-950 dark:text-white">
                    {{ $scheduleStatus['last_run_at']?->format('Y-m-d H:i:s') ?? '—' }}
                </dd>
            </div>

            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">آخر نتيجة</dt>
                <dd>
                    @if ($scheduleStatus['last_result'])
                        <x-filament::badge :color="$scheduleStatus['last_result']->color()">
                            {{ $scheduleStatus['last_result']->label() }}
                        </x-filament::badge>
                    @else
                        <span class="text-gray-950 dark:text-white">—</span>
                    @endif
                </dd>
            </div>

            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">موعد التشغيل القادم</dt>
                <dd class="text-gray-950 dark:text-white space-y-1">
                    <div>قاعدة بيانات: {{ $scheduleStatus['next_database_run']?->format('Y-m-d H:i') ?? '—' }}</div>
                    <div>كاملة: {{ $scheduleStatus['next_full_run']?->format('Y-m-d H:i') ?? '—' }}</div>
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

    {{-- الأمان — عرض فقط. لا يوجد أي حقل لتعديل مفتاح التشفير من هنا. --}}
    <x-filament::section>
        <x-slot name="heading">الأمان</x-slot>
        <x-slot name="description">حالة تشفير أرشيفات النسخ الاحتياطية — المفتاح نفسه لا يُعرَض ولا يُعدَّل من هذه الواجهة أبداً.</x-slot>

        <dl class="text-sm">
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">التشفير (Encryption)</dt>
                <dd class="mt-1" aria-label="حالة تشفير النسخ الاحتياطية: {{ $systemInfo['encryption_enabled'] ? 'مفعّل' : 'غير مفعّل' }}">
                    <x-filament::badge :color="$systemInfo['encryption_enabled'] ? 'success' : 'danger'" :icon="$systemInfo['encryption_enabled'] ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open'">
                        {{ $systemInfo['encryption_enabled'] ? 'مفعّل' : 'غير مفعّل' }}
                    </x-filament::badge>
                </dd>
            </div>
        </dl>
    </x-filament::section>

    {{-- معلومات النظام — عرض فقط، Aggregates محسوبة مرة واحدة (BackupMonitoringService). --}}
    <x-filament::section>
        <x-slot name="heading">معلومات النظام</x-slot>

        <dl class="grid grid-cols-1 gap-x-4 gap-y-4 text-sm sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">إصدار النظام (Laravel)</dt>
                <dd class="text-gray-950 dark:text-white">
                    <bdi>{{ $systemInfo['laravel_version'] }}</bdi>
                </dd>
            </div>

            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">آخر نسخة ناجحة</dt>
                <dd class="text-gray-950 dark:text-white">
                    {{ $systemInfo['last_successful_backup']?->name ?? 'لا توجد بعد' }}
                </dd>
            </div>

            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">آخر استعادة (Restore)</dt>
                <dd class="text-gray-950 dark:text-white">
                    {{ $systemInfo['last_restore']?->created_at?->format('Y-m-d H:i:s') ?? 'لم تُجرَ استعادة بعد' }}
                </dd>
            </div>

            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">عدد النسخ</dt>
                <dd class="text-gray-950 dark:text-white">
                    <bdi>{{ $systemInfo['total_backups'] }}</bdi>
                </dd>
            </div>

            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">إجمالي الحجم</dt>
                <dd class="text-gray-950 dark:text-white">
                    <bdi>{{ $systemInfo['total_size_human'] }}</bdi>
                </dd>
            </div>
        </dl>
    </x-filament::section>
</x-filament-panels::page>

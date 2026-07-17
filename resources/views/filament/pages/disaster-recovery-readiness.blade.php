{{--
    قراءة فقط بالكامل — لا form هنا، لا Action مؤثِّرة. كل البيانات محسوبة
    مرة واحدة في DisasterRecoveryReadiness::mount() (systemStatus/
    integrityChecks/readinessChecks/overallStatus)، وليس هنا في الـBlade.
--}}
@php
    $statusMeta = [
        'healthy'  => ['label' => 'Healthy',  'icon' => 'heroicon-o-check-circle',       'color' => 'success'],
        'warning'  => ['label' => 'Warning',   'icon' => 'heroicon-o-exclamation-triangle', 'color' => 'warning'],
        'critical' => ['label' => 'Critical',  'icon' => 'heroicon-o-x-circle',           'color' => 'danger'],
    ];

    $overallMeta = [
        'READY'    => ['icon' => 'heroicon-o-check-circle',       'color' => 'success', 'description' => 'كل عناصر الجاهزية الأساسية سليمة حالياً.'],
        'WARNING'  => ['icon' => 'heroicon-o-exclamation-triangle', 'color' => 'warning', 'description' => 'يوجد عنصر واحد على الأقل يحتاج مراجعة قبل الاعتماد الكامل على التعافي من كارثة.'],
        'CRITICAL' => ['icon' => 'heroicon-o-x-circle',           'color' => 'danger',  'description' => 'يوجد عنصر أساسي غير متوفر — النظام غير جاهز للتعافي من كارثة حالياً.'],
    ][$overallStatus];

    $readinessValueMeta = [
        'yes'     => ['icon' => 'heroicon-o-check-circle', 'class' => 'text-success-600 dark:text-success-400', 'label' => 'نعم'],
        'no'      => ['icon' => 'heroicon-o-x-circle',     'class' => 'text-danger-600 dark:text-danger-400',  'label' => 'لا'],
        'unknown' => ['icon' => 'heroicon-o-question-mark-circle', 'class' => 'text-gray-500 dark:text-gray-400', 'label' => 'غير معروف'],
    ];
@endphp

<x-filament-panels::page>
    {{-- ملخّص الجاهزية — Badge واحدة، معروضة أولاً لأنها أهم عنصر في الصفحة. --}}
    <x-filament::section>
        <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">ملخّص الجاهزية</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $overallMeta['description'] }}</p>
            </div>

            <div aria-label="حالة الجاهزية الإجمالية: {{ $overallStatus }}">
                <x-filament::badge :color="$overallMeta['color']" :icon="$overallMeta['icon']" size="lg">
                    {{ $overallStatus }}
                </x-filament::badge>
            </div>
        </div>
    </x-filament::section>

    {{-- القسم الأول: حالة النظام --}}
    <x-filament::section>
        <x-slot name="heading">حالة النظام</x-slot>

        <dl class="grid grid-cols-1 gap-x-4 gap-y-4 text-sm sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">آخر نسخة ناجحة</dt>
                <dd class="text-gray-950 dark:text-white">
                    {{ $systemStatus['last_successful']?->name ?? 'لا توجد بعد' }}
                </dd>
            </div>

            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">آخر نسخة فاشلة</dt>
                <dd class="text-gray-950 dark:text-white">
                    {{ $systemStatus['last_failed']?->name ?? 'لا توجد' }}
                </dd>
            </div>

            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">آخر استعادة (Restore)</dt>
                <dd class="text-gray-950 dark:text-white">
                    {{ $systemStatus['last_restore']?->created_at?->format('Y-m-d H:i:s') ?? 'لم تُجرَ استعادة بعد' }}
                </dd>
            </div>

            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">عدد النسخ</dt>
                <dd class="text-gray-950 dark:text-white">
                    <bdi>{{ $systemStatus['total_backups'] }}</bdi>
                </dd>
            </div>

            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">الحجم الكلي</dt>
                <dd class="text-gray-950 dark:text-white">
                    <bdi>{{ $systemStatus['total_size_human'] }}</bdi>
                </dd>
            </div>
        </dl>
    </x-filament::section>

    {{-- القسم الثاني: سلامة النظام --}}
    <x-filament::section>
        <x-slot name="heading">سلامة النظام</x-slot>
        <x-slot name="description">كل عنصر يُقيَّم اعتماداً على البيانات الفعلية الموجودة فقط — لا تخمين.</x-slot>

        <dl class="grid grid-cols-1 gap-x-4 gap-y-4 text-sm sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($integrityChecks as $check)
                @php($meta = $statusMeta[$check['status']])
                <div class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                    <dt class="font-medium text-gray-700 dark:text-gray-300">{{ $check['label'] }}</dt>
                    <dd aria-label="{{ $check['label'] }}: {{ $meta['label'] }}">
                        <x-filament::badge :color="$meta['color']" :icon="$meta['icon']">
                            {{ $meta['label'] }}
                        </x-filament::badge>
                    </dd>
                </div>
            @endforeach
        </dl>
    </x-filament::section>

    {{-- القسم الثالث: التحقق من الجاهزية --}}
    <x-filament::section>
        <x-slot name="heading">التحقق من الجاهزية</x-slot>
        <x-slot name="description">"غير معروف" تُعرَض صراحة عندما لا تتوفر بيانات حقيقية كافية لتحديد الإجابة — بدل التخمين.</x-slot>

        <ul class="divide-y divide-gray-200 text-sm dark:divide-gray-700">
            @foreach ($readinessChecks as $item)
                @php($valueMeta = $readinessValueMeta[$item['value']])
                <li class="flex items-center justify-between gap-3 py-2.5">
                    <span class="text-gray-700 dark:text-gray-300">{{ $item['label'] }}</span>
                    <span class="inline-flex items-center gap-1.5 font-medium {{ $valueMeta['class'] }}" aria-label="{{ $item['label'] }}: {{ $valueMeta['label'] }}">
                        @svg($valueMeta['icon'], 'h-4 w-4')
                        {{ $valueMeta['label'] }}
                    </span>
                </li>
            @endforeach
        </ul>
    </x-filament::section>
</x-filament-panels::page>

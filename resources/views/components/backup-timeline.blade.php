{{--
    backup-timeline — عرض وتدقيق فقط (المرحلة الثامنة: Backup History &
    Audit Timeline). يُبنى بالكامل من $steps المحسوبة في
    App\View\Components\BackupTimeline — بدون أي استعلام أو منطق إضافي هنا.

    عربي بالكامل، RTL بالاتجاه المنطقي (flex + gap يتبعان اتجاه dir=rtl
    تلقائياً)، بدون JavaScript/Alpine/Polling — الـ"نبض" على خطوة "جارٍ
    التنفيذ" هو animate-pulse من Tailwind (CSS بحت، ليس Alpine ولا JS).
--}}
@php
    $dotClasses = [
        'gray'    => 'bg-gray-100 text-gray-500 dark:bg-gray-500/10 dark:text-gray-400',
        'info'    => 'bg-info-100 text-info-600 dark:bg-info-500/10 dark:text-info-400',
        'success' => 'bg-success-100 text-success-600 dark:bg-success-500/10 dark:text-success-400',
        'danger'  => 'bg-danger-100 text-danger-600 dark:bg-danger-500/10 dark:text-danger-400',
    ];

    $badgeClasses = [
        'gray' => 'bg-gray-50 text-gray-600 ring-gray-500/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20',
        'info' => 'bg-info-50 text-info-700 ring-info-600/10 dark:bg-info-400/10 dark:text-info-400 dark:ring-info-400/20',
    ];
@endphp

<div class="space-y-4">
    {{-- شارة المصدر: يدوي / مجدول --}}
    <div>
        <span
            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $badgeClasses[$triggerColor()] ?? $badgeClasses['gray'] }}"
            aria-label="مصدر النسخة: {{ $triggerLabel() }}"
        >
            @svg($triggerIcon(), 'h-3.5 w-3.5')
            <bdi>{{ $triggerLabel() }}</bdi>
        </span>
    </div>

    {{-- السجل الزمني --}}
    <ol class="list-none" aria-label="السجل الزمني لدورة حياة النسخة الاحتياطية">
        @foreach ($steps as $step)
            <li
                class="relative flex gap-4 {{ $loop->last ? '' : 'pb-8' }}"
                aria-label="{{ $step['ariaLabel'] }}"
            >
                {{-- العمود: الأيقونة + خط الوصل --}}
                <div class="flex flex-col items-center">
                    <span
                        @class([
                            'flex h-8 w-8 shrink-0 items-center justify-center rounded-full',
                            $dotClasses[$step['color']] ?? $dotClasses['gray'],
                            'animate-pulse' => ! empty($step['pulse']),
                        ])
                    >
                        @svg($step['icon'], 'h-4 w-4')
                    </span>

                    @unless ($loop->last)
                        <span class="mt-1 w-0.5 flex-1 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                    @endunless
                </div>

                {{-- المحتوى --}}
                <div class="flex-1 {{ $loop->last ? '' : 'pb-2' }}">
                    <p class="text-sm font-semibold text-gray-950 dark:text-white">
                        {{ $step['title'] }}
                    </p>

                    @if ($step['time'])
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            <bdi>{{ $step['time']->diffForHumans() }}</bdi>
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500">
                            <bdi>{{ $step['time']->translatedFormat('j F Y') }}</bdi>
                            <span class="mx-1">·</span>
                            <bdi>{{ $step['time']->format('H:i') }}</bdi>
                        </p>
                    @endif

                    @if ($step['description'])
                        <p
                            @class([
                                'mt-1.5 whitespace-pre-line text-sm',
                                'text-danger-700 dark:text-danger-400' => $step['color'] === 'danger',
                                'text-gray-700 dark:text-gray-300' => $step['color'] !== 'danger',
                            ])
                        >
                            {{ $step['description'] }}
                        </p>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
</div>

@php
    // ⚠️ لا "use" استيراد هنا عمداً — ملف Blade مُصرَّف ومُضمَّن (include) داخل
    // نطاق دالة عند العرض، وليس ملف PHP مستقل يبدأ بـ"use" في أول سطر فعلي؛
    // الاعتماد على ذلك هش عبر إصدارات Blade/Livewire المختلفة. نستخدم بدلاً
    // من ذلك الاسم الكامل \App\Support\Enums\BackupType مباشرة أينما احتجناه.

    // ⚠️ UI Polish فقط — لا تغيير في البيانات أو المنطق. نفس استدعاء
    // BackupMonitoringService::snapshot() تماماً، بنفس المفاتيح الداخلية
    // (health_status تبقى 'healthy'/'warning'/'critical' داخلياً — التعريب
    // هنا للعرض فقط، لا لقيمة الـEnum/الـarray).
    $snapshot = $this->getSnapshot();

    $healthMeta = [
        'healthy' => [
            'label'       => 'سليم',
            'color'       => 'success',
            'icon'        => 'heroicon-o-check-circle',
            'description' => 'كل النسخ الاحتياطية تعمل بشكل طبيعي ولا توجد نسخ عالقة.',
        ],
        'warning' => [
            'label'       => 'تنبيه',
            'color'       => 'warning',
            'icon'        => 'heroicon-o-exclamation-triangle',
            'description' => 'آخر نسخة ناجحة قديمة، أو حدث فشل مؤخراً — يستحسن المراجعة.',
        ],
        'critical' => [
            'label'       => 'حرج',
            'color'       => 'danger',
            'icon'        => 'heroicon-o-x-circle',
            'description' => 'لا توجد نسخة ناجحة، أو توجد عملية عالقة — يتطلب انتباهاً فورياً.',
        ],
    ];
    $health = $healthMeta[$snapshot['health_status']] ?? [
        'label' => $snapshot['health_status'], 'color' => 'gray', 'icon' => 'heroicon-o-question-mark-circle', 'description' => null,
    ];

    $healthBannerClasses = match ($health['color']) {
        'success' => 'border-success-200 bg-success-50 dark:border-success-800 dark:bg-success-950',
        'warning' => 'border-warning-200 bg-warning-50 dark:border-warning-800 dark:bg-warning-950',
        'danger'  => 'border-danger-200 bg-danger-50 dark:border-danger-800 dark:bg-danger-950',
        default   => 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800',
    };
    $healthIconClasses = match ($health['color']) {
        'success' => 'text-success-600 dark:text-success-400',
        'warning' => 'text-warning-600 dark:text-warning-400',
        'danger'  => 'text-danger-600 dark:text-danger-400',
        default   => 'text-gray-500',
    };

    // "لا توجد أي نسخة احتياطية إطلاقاً" — كل backup له type واحد دائماً، فمجموع
    // عدَّادي database+full يساوي إجمالي عدد السجلات فعلياً (بصرف النظر عن status).
    $hasNoBackupsAtAll = ($snapshot['counts']['database'] + $snapshot['counts']['full']) === 0;

    $typeLabel = fn (\App\Support\Enums\BackupType $type) => $type === \App\Support\Enums\BackupType::Database ? 'قاعدة البيانات' : 'نسخة كاملة';

    $integrityMeta = function (?bool $verified) {
        return match ($verified) {
            true    => ['label' => 'تم التحقق', 'color' => 'text-success-600 dark:text-success-400', 'icon' => 'heroicon-o-shield-check'],
            false   => ['label' => 'فشل التحقق', 'color' => 'text-danger-600 dark:text-danger-400', 'icon' => 'heroicon-o-shield-exclamation'],
            default => ['label' => 'لم يُفحص بعد', 'color' => 'text-gray-500 dark:text-gray-400', 'icon' => 'heroicon-o-minus-circle'],
        };
    };

    $absoluteDate = fn ($date) => $date ? $date->format('H:i') . ' — ' . $date->translatedFormat('j F Y') : null;

    // ── بطاقات الحدث الأخير — بنية موحَّدة واحدة للأربعة، حتى في حال الفراغ ──
    $cards = [];

    // آخر نسخة ناجحة
    $s = $snapshot['last_successful'];
    $cards[] = [
        'title'       => 'آخر نسخة ناجحة',
        'icon'        => 'heroicon-o-check-circle',
        'badgeLabel'  => $s ? $typeLabel($s->type) : 'لا توجد',
        'badgeColor'  => 'gray',
        'mainValue'   => $s ? optional($s->completed_at)->diffForHumans() : 'لا توجد نسخة بعد',
        'meta'        => null,
        'description' => $s ? $absoluteDate($s->completed_at) : 'لم يتم إنشاء أي نسخة بعد.',
    ];

    // آخر نسخة فاشلة
    $f = $snapshot['last_failed'];
    $cards[] = [
        'title'       => 'آخر نسخة فاشلة',
        'icon'        => 'heroicon-o-x-circle',
        'badgeLabel'  => $f ? $typeLabel($f->type) : 'لا توجد',
        'badgeColor'  => $f ? 'danger' : 'gray',
        'mainValue'   => $f ? optional($f->completed_at)->diffForHumans() : 'لا توجد نسخة فاشلة',
        'meta'        => null,
        'description' => $f ? ($f->error_message ?? 'فشلت العملية') : 'الحالة ممتازة ✓',
    ];

    // آخر نسخة قاعدة بيانات
    $db = $snapshot['last_database_backup'];
    $dbIntegrity = $db ? $integrityMeta($db->integrity_verified) : null;
    $cards[] = [
        'title'       => 'نسخة قاعدة البيانات',
        'icon'        => 'heroicon-o-circle-stack',
        'badgeLabel'  => 'قاعدة البيانات',
        'badgeColor'  => 'gray',
        'mainValue'   => $db ? optional($db->completed_at)->diffForHumans() : 'لا توجد نسخة بعد',
        'meta'        => $db ? ['text' => $db->humanSize() ?? '—', 'integrity' => $dbIntegrity] : null,
        'description' => $db ? $absoluteDate($db->completed_at) : 'لم تُنشأ أي نسخة قاعدة بيانات بعد.',
    ];

    // آخر نسخة كاملة
    $full = $snapshot['last_full_backup'];
    $fullIntegrity = $full ? $integrityMeta($full->integrity_verified) : null;
    $cards[] = [
        'title'       => 'النسخة الكاملة',
        'icon'        => 'heroicon-o-archive-box',
        'badgeLabel'  => 'نسخة كاملة',
        'badgeColor'  => 'gray',
        'mainValue'   => $full ? optional($full->completed_at)->diffForHumans() : 'لا توجد نسخة بعد',
        'meta'        => $full ? ['text' => $full->humanSize() ?? '—', 'integrity' => $fullIntegrity] : null,
        'description' => $full ? $absoluteDate($full->completed_at) : 'لم تُنشأ أي نسخة كاملة بعد.',
    ];

    // ── الجدولة القادمة — الوقت هو العنصر الأبرز ──
    $relativeDay = function ($date) {
        if (! $date) {
            return null;
        }

        return match (true) {
            $date->isToday()    => 'اليوم',
            $date->isTomorrow() => 'غداً',
            default             => $date->translatedFormat('l، j F'),
        };
    };

    $schedule = [
        [
            'label' => 'قاعدة البيانات',
            'day'   => $relativeDay($snapshot['next_database_run']),
            'time'  => $snapshot['next_database_run']?->format('H:i'),
        ],
        [
            'label' => 'النسخة الكاملة',
            'day'   => $relativeDay($snapshot['next_full_run']),
            'time'  => $snapshot['next_full_run']?->format('H:i'),
        ],
    ];

    // ── الإحصائيات — Mini Stats بفواصل، بلا Cards كبيرة ──
    $stats = [
        ['label' => 'مكتملة',        'value' => $snapshot['counts']['completed'], 'color' => 'text-success-600 dark:text-success-400'],
        ['label' => 'قيد التشغيل',   'value' => $snapshot['counts']['running'],   'color' => 'text-info-600 dark:text-info-400'],
        ['label' => 'فاشلة',         'value' => $snapshot['counts']['failed'],    'color' => 'text-danger-600 dark:text-danger-400'],
        ['label' => 'قاعدة البيانات', 'value' => $snapshot['counts']['database'],  'color' => 'text-gray-950 dark:text-white'],
        ['label' => 'نسخة كاملة',    'value' => $snapshot['counts']['full'],      'color' => 'text-gray-950 dark:text-white'],
    ];
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">مراقبة النسخ الاحتياطية</x-slot>
        <x-slot name="description">ملخص حالة النسخ، الجدولة، وسلامة النظام.</x-slot>

        {{-- حالة النظام — شريط علوي بارز --}}
        <div
            class="mb-7 flex items-center gap-4 rounded-lg border p-5 {{ $healthBannerClasses }}"
            role="status"
            aria-label="حالة نظام النسخ الاحتياطي: {{ $health['label'] }}"
        >
            @svg($health['icon'], 'h-9 w-9 flex-shrink-0 '.$healthIconClasses)

            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-base font-semibold leading-relaxed text-gray-950 dark:text-white">{{ $health['label'] }}</span>
                    <x-filament::badge :color="$health['color']">
                        {{ $health['label'] }}
                    </x-filament::badge>
                </div>
                @if ($health['description'])
                    <p class="mt-1 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $health['description'] }}</p>
                @endif
            </div>
        </div>

        @if ($hasNoBackupsAtAll)
            {{-- Empty State عام — بدل تكرار نفس رسالة الفراغ في 4 بطاقات منفصلة --}}
            <div class="mb-7 flex flex-col items-center justify-center gap-3 rounded-lg border border-dashed border-gray-300 p-10 text-center dark:border-gray-700">
                @svg('heroicon-o-circle-stack', 'h-10 w-10 text-gray-400 dark:text-gray-600')
                <p class="text-sm font-medium leading-relaxed text-gray-700 dark:text-gray-300">لا توجد أي نسخ احتياطية بعد.</p>
                <p class="text-xs leading-relaxed text-gray-500 dark:text-gray-400">ستظهر هنا تفاصيل آخر النسخ فور إنشاء أول نسخة احتياطية.</p>
            </div>
        @else
            {{-- آخر نسخة ناجحة / فاشلة / قاعدة بيانات / كاملة — بنية موحَّدة واحدة --}}
            <div class="mb-7 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($cards as $card)
                    <div class="flex flex-col gap-3 rounded-lg border border-gray-200 p-5 dark:border-gray-700">
                        <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                            @svg($card['icon'], 'h-4 w-4 flex-shrink-0')
                            <h3 class="text-xs font-medium uppercase tracking-wide">{{ $card['title'] }}</h3>
                        </div>

                        <x-filament::badge :color="$card['badgeColor']" class="w-fit">
                            {{ $card['badgeLabel'] }}
                        </x-filament::badge>

                        <p class="text-xl font-bold leading-snug text-gray-950 dark:text-white">
                            <bdi>{{ $card['mainValue'] }}</bdi>
                        </p>

                        @if ($card['meta'])
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm">
                                <span class="text-gray-700 dark:text-gray-300"><bdi>{{ $card['meta']['text'] }}</bdi></span>
                                <span class="flex items-center gap-1 {{ $card['meta']['integrity']['color'] }}">
                                    @svg($card['meta']['integrity']['icon'], 'h-3.5 w-3.5 flex-shrink-0')
                                    {{ $card['meta']['integrity']['label'] }}
                                </span>
                            </div>
                        @endif

                        <p class="break-words text-xs leading-relaxed text-gray-500 dark:text-gray-400">
                            <bdi>{{ $card['description'] }}</bdi>
                        </p>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- النسخة المجدولة القادمة / الإحصائيات / إجمالي المساحة --}}
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            {{-- النسخة المجدولة القادمة — الوقت هو العنصر الأبرز --}}
            <div class="rounded-lg border border-gray-200 p-5 dark:border-gray-700">
                <div class="mb-4 flex items-center gap-2 text-gray-500 dark:text-gray-400">
                    @svg('heroicon-o-calendar-days', 'h-4 w-4 flex-shrink-0')
                    <h3 class="text-xs font-medium uppercase tracking-wide">النسخة المجدولة القادمة</h3>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    @foreach ($schedule as $item)
                        <div>
                            <p class="text-xs leading-relaxed text-gray-500 dark:text-gray-400">{{ $item['label'] }}</p>
                            @if ($item['time'])
                                <p class="mt-1 text-2xl font-bold leading-tight text-gray-950 dark:text-white">
                                    <bdi>{{ $item['time'] }}</bdi>
                                </p>
                                <p class="text-xs leading-relaxed text-gray-500 dark:text-gray-400">{{ $item['day'] }}</p>
                            @else
                                <p class="mt-1 text-sm font-medium leading-relaxed text-gray-500 dark:text-gray-400">معطّلة</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- الإحصائيات — Mini Stats بفواصل --}}
            <div class="rounded-lg border border-gray-200 p-5 dark:border-gray-700">
                <div class="mb-4 flex items-center gap-2 text-gray-500 dark:text-gray-400">
                    @svg('heroicon-o-list-bullet', 'h-4 w-4 flex-shrink-0')
                    <h3 class="text-xs font-medium uppercase tracking-wide">الإحصائيات</h3>
                </div>

                <div class="flex flex-wrap divide-x divide-gray-200 rtl:divide-x-reverse dark:divide-gray-700">
                    @foreach ($stats as $stat)
                        <div class="flex min-w-[5.5rem] flex-col items-center gap-1 px-3 py-1 text-center first:ps-0 last:pe-0">
                            <span class="text-xl font-bold leading-tight {{ $stat['color'] }}"><bdi>{{ $stat['value'] }}</bdi></span>
                            <span class="text-xs leading-relaxed text-gray-500 dark:text-gray-400">{{ $stat['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- إجمالي المساحة --}}
            <div class="rounded-lg border border-gray-200 p-5 dark:border-gray-700">
                <div class="mb-4 flex items-center gap-2 text-gray-500 dark:text-gray-400">
                    @svg('heroicon-o-server-stack', 'h-4 w-4 flex-shrink-0')
                    <h3 class="text-xs font-medium uppercase tracking-wide">إجمالي المساحة المستخدَمة</h3>
                </div>
                <p class="text-2xl font-bold leading-tight text-gray-950 dark:text-white">
                    <bdi>{{ $snapshot['total_size_human'] }}</bdi>
                </p>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

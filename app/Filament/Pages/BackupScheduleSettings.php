<?php

namespace App\Filament\Pages;

use App\Models\ActivityLog;
use App\Models\Backup;
use App\Models\Setting;
use App\Services\Backup\BackupEncryptor;
use App\Services\Backup\BackupMonitoringService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

/**
 * BackupScheduleSettings — "النظام → إعدادات النسخ الاحتياطي" (المرحلة
 * الخامسة، وُسِّعت في المرحلة التاسعة: Backup Policies & Settings UI).
 *
 * طبقة إعدادات فقط فوق النظام الحالي: لا تنشئ أي نسخة احتياطية بنفسها ولا
 * تُعدَّل شيئاً في محرك النسخ/الاستعادة/الجدولة/الاحتفاظ/المراقبة/الإشعارات/
 * الـTimeline (BackupService, RestoreService, ScheduledBackupRunner,
 * BackupRetention, BackupMonitoringService, BackupObserver, BackupTimeline) —
 * تحفظ فقط في جدول settings (مجموعة backup_schedule الموجودة أصلاً) عبر
 * Setting::setGroup()، وتقرأ عرضاً فقط من BackupMonitoringService/ActivityLog/
 * BackupEncryptor الموجودة أصلاً، بنفس نمط MailSettings/PaymentSettings.
 *
 * القيم المحفوظة هنا تُقرَأ فعلياً عبر:
 *  - routes/console.php (وقت/تفعيل الجدولة الديناميكية)
 *  - App\Services\Backup\ScheduledBackupRunner::isEnabled()
 *  - AppServiceProvider::applyBackupScheduleSettings() (الاحتفاظ + مهلة
 *    التنفيذ — تربط بإعدادات config('backups.system_backup.*') الموجودة
 *    أصلاً، لا تكرّرها)
 *
 * ⚠️ حقول لم تُضَف عمداً (قرارات صريحة، راجع التقرير النهائي):
 *  - "أيام التنفيذ" لكل نوع: المحرك الحالي (routes/console.php +
 *    ScheduledBackupRunner) يدعم فقط "يومياً" لقاعدة البيانات و"كل جمعة"
 *    للنسخة الكاملة، بشكل مبرمج غير قابل للتخصيص حالياً — إضافة حقل لا
 *    يقرأه أي كود فعلي سيكون مضلِّلاً لمستخدم الواجهة، وتعديل المحرك ممنوع
 *    هذه المرحلة.
 *  - "Yearly" في سياسة الاحتفاظ: BackupRetention الحالية (GFS) تدعم فقط
 *    daily/weekly/monthly — لا مستوى "سنوي" في المنطق الفعلي، وتعديلها ممنوع
 *    هذه المرحلة. لنفس السبب أعلاه لم يُضَف حقل غير مفعَّل بلا قيمة حقيقية.
 */
class BackupScheduleSettings extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'إعدادات النسخ الاحتياطي';
    protected static ?string $navigationGroup = 'النظام';
    protected static ?int    $navigationSort  = 21;
    protected static ?string $title           = 'إعدادات النسخ الاحتياطي';
    protected static string  $view            = 'filament.pages.backup-schedule-settings';

    public array $data = [];

    /** @var array{last_scheduled_backup: ?Backup, last_run_at: ?\Illuminate\Support\Carbon, last_result: ?\App\Support\Enums\BackupStatus, next_database_run: ?\Illuminate\Support\Carbon, next_full_run: ?\Illuminate\Support\Carbon} */
    public array $scheduleStatus = [];

    /** @var array{laravel_version: string, last_successful_backup: ?Backup, last_restore: ?ActivityLog, total_backups: int, total_size_human: string, encryption_enabled: bool} */
    public array $systemInfo = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public function mount(): void
    {
        $saved = Setting::group('backup_schedule');

        $this->data = [
            'database_backup_enabled' => filter_var($saved['database_backup_enabled'] ?? '1', FILTER_VALIDATE_BOOLEAN),
            'full_backup_enabled'     => filter_var($saved['full_backup_enabled'] ?? '1', FILTER_VALIDATE_BOOLEAN),
            'backup_time'             => $saved['backup_time'] ?? '02:00',
            'backup_timezone'         => $saved['backup_timezone'] ?? config('app.timezone'),
            'retention_daily'         => $saved['retention_daily']   ?? (string) config('backups.system_backup.retention.daily', 7),
            'retention_weekly'        => $saved['retention_weekly']  ?? (string) config('backups.system_backup.retention.weekly', 4),
            'retention_monthly'       => $saved['retention_monthly'] ?? (string) config('backups.system_backup.retention.monthly', 3),
            'running_timeout'         => $saved['running_timeout']   ?? (string) config('backups.system_backup.job_timeout', 1800),
        ];

        // تُحسَب مرة واحدة فقط هنا (mount) — وليس داخل الـBlade View — حتى لا
        // تُعاد نفس الاستعلامات في كل Render لاحق يسبّبه تفاعل المستخدم مع
        // حقول النموذج (متطلَّب صريح: "لا تضف Queries داخل كل Render").
        $this->scheduleStatus = $this->computeScheduleStatus();
        $this->systemInfo     = $this->computeSystemInfo();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('تفعيل الجدولة التلقائية')
                    ->icon('heroicon-o-power')
                    ->description('عند التعطيل، لن يُنشئ Laravel Scheduler أي نسخة من هذا النوع تلقائياً — الإنشاء اليدوي من صفحة "النسخ الاحتياطية" يبقى متاحاً دائماً بغض النظر عن هذا الإعداد.')
                    ->schema([
                        Forms\Components\Toggle::make('database_backup_enabled')
                            ->label('نسخة قاعدة البيانات التلقائية (يومياً)')
                            ->helperText('عند التفعيل، يُنشئ Scheduler نسخة قاعدة بيانات جديدة كل يوم في الوقت المحدَّد أدناه.')
                            ->default(true),

                        Forms\Components\Toggle::make('full_backup_enabled')
                            ->label('النسخة الكاملة التلقائية (أسبوعياً — كل يوم جمعة)')
                            ->helperText('عند التفعيل، يُنشئ Scheduler نسخة كاملة (قاعدة بيانات + ملفات) كل يوم جمعة في نفس الوقت.')
                            ->default(true),
                    ]),

                Forms\Components\Section::make('موعد التنفيذ')
                    ->icon('heroicon-o-clock')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TimePicker::make('backup_time')
                            ->label('وقت التنفيذ')
                            ->seconds(false)
                            ->default('02:00')
                            ->required()
                            ->helperText('نسخة قاعدة البيانات: كل يوم في هذا الوقت. النسخة الكاملة: كل يوم جمعة في نفس الوقت.')
                            ->validationMessages([
                                'required' => 'حقل وقت التنفيذ مطلوب.',
                            ]),

                        Forms\Components\Select::make('backup_timezone')
                            ->label('المنطقة الزمنية')
                            ->options(array_combine(
                                \DateTimeZone::listIdentifiers(),
                                \DateTimeZone::listIdentifiers()
                            ))
                            ->searchable()
                            ->default(config('app.timezone'))
                            ->required()
                            ->helperText('تُطبَّق على وقت التنفيذ أعلاه لكلا نوعَي النسخ.'),
                    ]),

                Forms\Components\Section::make('سياسة الاحتفاظ (Retention) ومهلة التنفيذ')
                    ->icon('heroicon-o-archive-box')
                    ->description('عدد النسخ التي تُحفَظ قبل الحذف التلقائي عبر backup:apply-retention، والمهلة الزمنية القصوى قبل اعتبار نسخة "عالقة" وتصنيفها كفاشلة تلقائياً — راجع docs/BACKUP-SYSTEM.md لتفاصيل سياسة الاحتفاظ الكاملة.')
                    ->columns(4)
                    ->schema([
                        Forms\Components\TextInput::make('retention_daily')
                            ->label('عدد النسخ اليومية')
                            ->helperText('لنسخ قاعدة البيانات اليومية.')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->required()
                            ->validationMessages([
                                'min' => 'يجب أن يكون عدد النسخ اليومية 1 على الأقل.',
                                'required' => 'حقل عدد النسخ اليومية مطلوب.',
                            ]),

                        Forms\Components\TextInput::make('retention_weekly')
                            ->label('عدد النسخ الأسبوعية')
                            ->helperText('لنُسخ نهاية الأسبوع (الكاملة) — سياسة GFS.')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->required()
                            ->validationMessages([
                                'min' => 'يجب أن يكون عدد النسخ الأسبوعية 1 على الأقل.',
                                'required' => 'حقل عدد النسخ الأسبوعية مطلوب.',
                            ]),

                        Forms\Components\TextInput::make('retention_monthly')
                            ->label('عدد النسخ الشهرية')
                            ->helperText('لنسخة أول جمعة من كل شهر — سياسة GFS.')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->required()
                            ->validationMessages([
                                'min' => 'يجب أن يكون عدد النسخ الشهرية 1 على الأقل.',
                                'required' => 'حقل عدد النسخ الشهرية مطلوب.',
                            ]),

                        Forms\Components\TextInput::make('running_timeout')
                            ->label('مهلة العملية الجارية (ثانية)')
                            ->helperText('إذا تجاوزت نسخة "قيد التنفيذ" ضعف هذه المهلة، تُصنَّف تلقائياً كعالقة/فاشلة عند تشغيل backup:apply-retention.')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->required()
                            ->validationMessages([
                                'min' => 'يجب أن تكون مهلة العملية الجارية 1 ثانية على الأقل.',
                                'required' => 'حقل مهلة العملية الجارية مطلوب.',
                            ]),
                    ]),

                Forms\Components\Section::make('التنبيهات')
                    ->icon('heroicon-o-bell-alert')
                    ->description('حالة إشعارات لوحة الإدارة الداخلية عند فشل/نجاح النسخ المجدولة — عرض فقط، ليست إعدادات قابلة للحفظ من هذه الصفحة حالياً.')
                    ->schema([
                        Forms\Components\Toggle::make('failure_notifications_enabled')
                            ->label('إشعارات الفشل')
                            ->helperText('مفعّلة دائماً حالياً: يصل إشعار داخلي لكل Super Admin عند فشل أي نسخة مجدولة (المرحلة السابعة). تعطيلها من هذه الواجهة يتطلّب تعديل BackupObserver، وهو ممنوع في هذه المرحلة.')
                            ->default(true)
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Toggle::make('success_notifications_enabled')
                            ->label('إشعارات النجاح')
                            ->helperText('غير مدعومة حالياً.')
                            ->default(false)
                            ->disabled()
                            ->dehydrated(false),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $before = Setting::group('backup_schedule');

        $after = [
            'database_backup_enabled' => ! empty($data['database_backup_enabled']) ? '1' : '0',
            'full_backup_enabled'     => ! empty($data['full_backup_enabled']) ? '1' : '0',
            'backup_time'             => $data['backup_time'],
            'backup_timezone'         => $data['backup_timezone'],
            'retention_daily'         => (string) $data['retention_daily'],
            'retention_weekly'        => (string) $data['retention_weekly'],
            'retention_monthly'       => (string) $data['retention_monthly'],
            'running_timeout'         => (string) $data['running_timeout'],
        ];

        // Atomic: كل مفاتيح المجموعة تُحفَظ داخل معاملة واحدة — إن فشل أي جزء
        // (مثلاً خطأ اتصال بقاعدة البيانات في منتصف الحفظ)، تُلغى كل الكتابات
        // الجزئية معاً ولا يبقى النظام بحالة نصف-محفوظة. لا تعديل على
        // Setting::setGroup() نفسها (تبقى كما هي، تُستدعى فقط داخل معاملة).
        DB::transaction(function () use ($after): void {
            Setting::setGroup('backup_schedule', $after);
        });

        // Audit — يعتمد على ActivityLog الموجود أصلاً بالمشروع (لا نظام جديد):
        // يسجّل المستخدم (auth()->id() عبر recordFor) والوقت (created_at
        // تلقائياً) وما الذي تغيّر فعلياً فقط (مقارنة قبل/بعد).
        $changed = array_diff_assoc($after, $before);

        if ($changed !== []) {
            ActivityLog::recordFor(
                eventType: 'backup_settings.updated',
                entityType: Setting::class,
                entityId: 'backup_schedule',
                metadata: [
                    'changed' => collect($changed)->mapWithKeys(
                        fn ($newValue, $key) => [$key => ['from' => $before[$key] ?? null, 'to' => $newValue]]
                    )->all(),
                ],
            );
        }

        // إعادة حساب حالة الجدولة المعروضة (تعتمد على $this->data الجديدة) —
        // استعلام واحد فقط، وليس تكراراً لكل حقل يتغيّر أثناء الكتابة.
        $this->scheduleStatus = $this->computeScheduleStatus();

        Notification::make()
            ->title('✅ تم حفظ إعدادات النسخ الاحتياطي')
            ->success()
            ->send();
    }

    /**
     * بيانات حالة الجدولة المعروضة في الصفحة (عرض فقط). استعلام واحد على
     * جدول backups (Backup::query()->scheduledOnes()->latest()->first())،
     * يُستدعى فقط من mount()/save() أعلاه — وليس من الـBlade View مباشرة —
     * حتى لا يتكرر مع كل Render.
     *
     * @return array{last_scheduled_backup: ?Backup, last_run_at: ?\Illuminate\Support\Carbon, last_result: ?\App\Support\Enums\BackupStatus, next_database_run: ?\Illuminate\Support\Carbon, next_full_run: ?\Illuminate\Support\Carbon}
     */
    private function computeScheduleStatus(): array
    {
        $lastScheduled = Backup::query()->scheduledOnes()->latest('created_at')->first();

        $timezone = $this->data['backup_timezone'] ?? config('app.timezone');
        $time     = $this->data['backup_time'] ?? '02:00';

        $nextDatabaseRun = null;
        $nextFullRun     = null;

        try {
            [$hour, $minute] = array_pad(array_map('intval', explode(':', $time)), 2, 0);

            $nextDatabaseRun = now($timezone)->setTime($hour, $minute, 0);
            if ($nextDatabaseRun->isPast()) {
                $nextDatabaseRun->addDay();
            }

            $nextFullRun = now($timezone)->setTime($hour, $minute, 0);
            $guard = 0;
            while ((! $nextFullRun->isFriday() || $nextFullRun->isPast()) && $guard < 8) {
                $nextFullRun->addDay();
                $guard++;
            }
        } catch (\Throwable) {
            // تنسيق وقت/منطقة زمنية غير صالح مؤقتاً أثناء التعديل — لا نكسر الصفحة
        }

        return [
            'last_scheduled_backup' => $lastScheduled,
            'last_run_at'           => $lastScheduled?->created_at,
            'last_result'           => $lastScheduled?->status,
            'next_database_run'     => $nextDatabaseRun,
            'next_full_run'         => $nextFullRun,
        ];
    }

    /**
     * "الأمان" و"معلومات النظام" (عرض فقط، قراءة فقط — لا تعديل على مفتاح
     * التشفير من هنا إطلاقاً). تُعيد استخدام BackupMonitoringService::snapshot()
     * الموجودة أصلاً (المرحلة السادسة) للحجم الإجمالي وآخر نسخة ناجحة، بدل
     * تكرار تلك الحسابات. عدد النسخ الإجمالي **لا** يُشتَق من counts['completed'
     * + running + failed] في snapshot() لأنها لا تتضمن الحالة Pending أصلاً
     * (كانت ستُنقِص العدد الحقيقي) — استُخدِم بدلاً منها Backup::query()->count()
     * (COUNT(*) وحدها، بلا تحميل أي صف)، وهو Aggregate جديد ضروري فعلاً هنا
     * («استخدم Aggregates فقط عند الحاجة»). "آخر Restore" أيضاً استعلام جديد
     * ضروري لعدم توفره في أي خدمة حالية (سجل واحد عبر ActivityLog الموجود
     * أصلاً، وليس نظاماً جديداً).
     *
     * @return array{laravel_version: string, last_successful_backup: ?Backup, last_restore: ?ActivityLog, total_backups: int, total_size_human: string, encryption_enabled: bool}
     */
    private function computeSystemInfo(): array
    {
        $snapshot = app(BackupMonitoringService::class)->snapshot();

        $lastRestore = ActivityLog::query()
            ->where('event_type', 'backup.restored')
            ->latest('created_at')
            ->first();

        return [
            'laravel_version'         => app()->version(),
            'last_successful_backup'  => $snapshot['last_successful'],
            'last_restore'            => $lastRestore,
            'total_backups'           => Backup::query()->count(),
            'total_size_human'        => $snapshot['total_size_human'],
            'encryption_enabled'      => app(BackupEncryptor::class)->hasKey(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('حفظ الإعدادات')
                ->icon('heroicon-o-check')
                ->color('success')
                ->action('save'),
        ];
    }
}

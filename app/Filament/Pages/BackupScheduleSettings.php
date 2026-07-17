<?php

namespace App\Filament\Pages;

use App\Models\Backup;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * BackupScheduleSettings — "الإعدادات → النسخ الاحتياطي" (المرحلة الخامسة).
 *
 * طبقة إعدادات فقط فوق النظام الحالي: لا تنشئ أي نسخة احتياطية بنفسها ولا
 * تُعدَّل شيئاً في محرك النسخ/الاستعادة — تحفظ فقط في جدول settings (المجموعة
 * الموجودة أصلاً في النظام) عبر Setting::setGroup()، تماماً بنفس نمط
 * MailSettings/PaymentSettings. تُقرأ هذه القيم من:
 *  - routes/console.php (وقت/تفعيل الجدولة الديناميكية)
 *  - App\Services\Backup\ScheduledBackupRunner::isEnabled()
 *  - AppServiceProvider::applyBackupScheduleSettings() (الاحتفاظ — يربط بإعداد
 *    config('backups.system_backup.retention.*') الموجود أصلاً، لا يكرره)
 */
class BackupScheduleSettings extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'النسخ الاحتياطي';
    protected static ?string $navigationGroup = 'الإعدادات';
    protected static ?int    $navigationSort  = 25;
    protected static string  $view            = 'filament.pages.backup-schedule-settings';

    public array $data = [];

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
        ];
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
                            ->default(true),

                        Forms\Components\Toggle::make('full_backup_enabled')
                            ->label('النسخة الكاملة التلقائية (أسبوعياً — كل يوم جمعة)')
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
                            ->helperText('نسخة قاعدة البيانات: كل يوم في هذا الوقت. النسخة الكاملة: كل يوم جمعة في نفس الوقت.'),

                        Forms\Components\Select::make('backup_timezone')
                            ->label('المنطقة الزمنية')
                            ->options(array_combine(
                                \DateTimeZone::listIdentifiers(),
                                \DateTimeZone::listIdentifiers()
                            ))
                            ->searchable()
                            ->default(config('app.timezone'))
                            ->required(),
                    ]),

                Forms\Components\Section::make('الاحتفاظ (Retention)')
                    ->icon('heroicon-o-archive-box')
                    ->description('عدد النسخ التي تُحفَظ قبل الحذف التلقائي عبر backup:apply-retention — راجع docs/BACKUP-SYSTEM.md لتفاصيل سياسة الاحتفاظ الكاملة.')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('retention_daily')
                            ->label('عدد النسخ اليومية')
                            ->numeric()
                            ->minValue(1)
                            ->required(),

                        Forms\Components\TextInput::make('retention_weekly')
                            ->label('عدد النسخ الأسبوعية')
                            ->numeric()
                            ->minValue(1)
                            ->required(),

                        Forms\Components\TextInput::make('retention_monthly')
                            ->label('عدد النسخ الشهرية')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::setGroup('backup_schedule', [
            'database_backup_enabled' => ! empty($data['database_backup_enabled']) ? '1' : '0',
            'full_backup_enabled'     => ! empty($data['full_backup_enabled']) ? '1' : '0',
            'backup_time'             => $data['backup_time'],
            'backup_timezone'         => $data['backup_timezone'],
            'retention_daily'         => (string) $data['retention_daily'],
            'retention_weekly'        => (string) $data['retention_weekly'],
            'retention_monthly'       => (string) $data['retention_monthly'],
        ]);

        Notification::make()
            ->title('✅ تم حفظ إعدادات جدولة النسخ الاحتياطي')
            ->success()
            ->send();
    }

    /**
     * بيانات حالة الجدولة المعروضة في الصفحة (عرض فقط — ليست جزءاً من
     * form()/data، فلا علاقة لها بأي مشكلة تهيئة Container). تُستدعى مباشرة
     * من الـBlade View.
     *
     * @return array{last_scheduled_backup: Backup|null, last_run_at: \Illuminate\Support\Carbon|null, last_result: \App\Support\Enums\BackupStatus|null, next_database_run: \Illuminate\Support\Carbon|null, next_full_run: \Illuminate\Support\Carbon|null}
     */
    public function scheduleStatus(): array
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

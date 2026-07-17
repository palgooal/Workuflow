<?php

namespace App\Filament\Pages;

use App\Models\ActivityLog;
use App\Models\Backup;
use App\Models\User;
use App\Services\Backup\BackupEncryptor;
use App\Services\Backup\BackupMonitoringService;
use App\Services\Backup\ScheduledBackupRunner;
use App\Support\Enums\BackupType;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

/**
 * DisasterRecoveryReadiness — "النظام → جاهزية التعافي" (المرحلة العاشرة:
 * Disaster Recovery Readiness).
 *
 * صفحة قراءة فقط بالكامل: لا تنشئ أي نسخة، لا تُشغِّل أي استعادة، لا تحفظ أي
 * إعداد — لا form()، لا save()، لا Action مؤثِّرة في getHeaderActions().
 * تقرأ فقط من خدمات القراءة الموجودة أصلاً:
 *  - BackupMonitoringService::snapshot() (المرحلة السادسة) — آخر نسخة ناجحة/
 *    فاشلة، الحجم الإجمالي، health_status.
 *  - ScheduledBackupRunner::isEnabled() (المرحلة الخامسة) — حالة الجدولة
 *    الفعلية، بدل قراءة Setting::group() مباشرة (لا تكرار منطق).
 *  - BackupEncryptor::hasKey() — حالة التشفير.
 *  - ActivityLog (آخر Restore) وBackup::query()->count() — نفس الاستعلامات
 *    الدقيقة المستخدَمة في BackupScheduleSettings::computeSystemInfo() (المرحلة
 *    التاسعة)، غير موجودة في أي خدمة مشتركة لذا كُرِّرت هنا بسطر واحد بدل
 *    استحداث خدمة جديدة (ممنوع صراحة: Health/Monitoring/Disaster/Readiness
 *    Service/Calculator).
 *  - User::whereHas('roles', ...) — نفس استعلام "Super Admins" الآمن (بلا
 *    Spatie role() الذي يرمي RoleDoesNotExist) المستخدَم أصلاً في
 *    BackupObserver::getSuperAdmins() — لا تعديل على BackupObserver، فقط
 *    قراءة موازية بنفس الأسلوب الآمن للتحقق من وجود مستلمين للإشعارات.
 *
 * لا تُعدَّل بسبب هذه الصفحة: BackupService, RestoreService,
 * BackupMonitoringService, BackupRetention(Service), ScheduledBackupRunner,
 * BackupTimeline, BackupScheduleSettings, BackupObserver — قراءة فقط من كل
 * ما سبق، دون أي تعديل.
 *
 * حساب Badge "الجاهزية" (READY/WARNING/CRITICAL) وحالات "سلامة النظام" Healthy/
 * Warning/Critical: اختزال ترتيبي بسيط (أسوأ حالة بين health_status من
 * BackupMonitoringService وعناصر سلامة النظام) داخل computeOverallStatus()
 * أدناه فقط — Helper صغير محلي للصفحة، وليس Engine/Service جديداً.
 */
class DisasterRecoveryReadiness extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-shield-exclamation';
    protected static ?string $navigationLabel = 'جاهزية التعافي';
    protected static ?string $navigationGroup = 'النظام';
    protected static ?int    $navigationSort  = 22;
    protected static ?string $title           = 'جاهزية التعافي من الكوارث';
    protected static string  $view            = 'filament.pages.disaster-recovery-readiness';

    /** @var array{last_successful: ?Backup, last_failed: ?Backup, last_restore: ?ActivityLog, total_backups: int, total_size_human: string} */
    public array $systemStatus = [];

    /** @var array<string, array{label: string, status: string}> healthy|warning|critical لكل عنصر */
    public array $integrityChecks = [];

    /** @var array<int, array{label: string, value: string}> yes|no|unknown لكل عنصر */
    public array $readinessChecks = [];

    public string $overallStatus = 'CRITICAL';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    /**
     * كل شيء يُحسَب مرة واحدة هنا فقط (وليس في الـBlade View) — لا استعلامات
     * متكررة مع أي Render لاحق. الصفحة لا تحتوي أي حقل تفاعلي أصلاً (لا
     * form()) فلا يوجد سبب لإعادة الحساب بعد mount() إطلاقاً.
     */
    public function mount(): void
    {
        $snapshot  = app(BackupMonitoringService::class)->snapshot();
        $scheduler = app(ScheduledBackupRunner::class);
        $encryptor = app(BackupEncryptor::class);

        $lastSuccessful = $snapshot['last_successful'];

        $encryptionEnabled = $encryptor->hasKey();
        $integrityState    = $this->integrityState($lastSuccessful);
        $restoreAvailable  = $lastSuccessful !== null && $lastSuccessful->integrity_verified === true;

        $databaseScheduled = $scheduler->isEnabled(BackupType::Database);
        $fullScheduled     = $scheduler->isEnabled(BackupType::Full);

        $retentionValues = collect([
            (int) config('backups.system_backup.retention.daily', 0),
            (int) config('backups.system_backup.retention.weekly', 0),
            (int) config('backups.system_backup.retention.monthly', 0),
        ]);
        $retentionAllPositive = $retentionValues->every(fn (int $v) => $v >= 1);
        $retentionAnyPositive = $retentionValues->contains(fn (int $v) => $v >= 1);

        // نفس استعلام "Super Admins" الآمن في BackupObserver::getSuperAdmins()
        // (بدون Spatie role()، التي ترمي RoleDoesNotExist إن لم يكن الدور
        // مزروعاً بعد) — قراءة موازية فقط، لا تعديل على BackupObserver.
        $hasNotificationRecipient = User::query()
            ->whereHas('roles', function ($query): void {
                $query->where('name', 'super_admin')->where('guard_name', 'web');
            })
            ->exists();

        $lastRestore = ActivityLog::query()
            ->where('event_type', 'backup.restored')
            ->latest('created_at')
            ->first();

        $totalBackups = Backup::query()->count();

        $this->systemStatus = [
            'last_successful'   => $lastSuccessful,
            'last_failed'       => $snapshot['last_failed'],
            'last_restore'      => $lastRestore,
            'total_backups'     => $totalBackups,
            'total_size_human'  => $snapshot['total_size_human'],
        ];

        $this->integrityChecks = [
            'encryption' => [
                'label'  => 'Encryption',
                'status' => $encryptionEnabled ? 'healthy' : 'critical',
            ],
            'integrity_verification' => [
                'label'  => 'Integrity Verification',
                'status' => match ($integrityState) {
                    'verified'          => 'healthy',
                    'unverified'        => 'warning',
                    'failed', 'none'    => 'critical',
                },
            ],
            'restore_available' => [
                'label'  => 'Restore Available',
                'status' => $restoreAvailable ? 'healthy' : 'critical',
            ],
            'scheduler' => [
                'label'  => 'Scheduler',
                'status' => match (true) {
                    $databaseScheduled && $fullScheduled => 'healthy',
                    $databaseScheduled || $fullScheduled => 'warning',
                    default                               => 'critical',
                },
            ],
            'retention' => [
                'label'  => 'Retention',
                'status' => match (true) {
                    $retentionAllPositive => 'healthy',
                    $retentionAnyPositive => 'warning',
                    default                => 'critical',
                },
            ],
            'notifications' => [
                'label'  => 'Notifications',
                'status' => $hasNotificationRecipient ? 'healthy' : 'warning',
            ],
        ];

        $this->readinessChecks = [
            ['label' => 'آخر Backup موجود', 'value' => $totalBackups > 0 ? 'yes' : 'no'],
            ['label' => 'آخر Backup تم التحقق منه', 'value' => match ($integrityState) {
                'verified'   => 'yes',
                'failed'     => 'no',
                'none'       => 'no',
                'unverified' => 'unknown',
            }],
            ['label' => 'يوجد Restore صالح', 'value' => $restoreAvailable ? 'yes' : 'no'],
            ['label' => 'التشفير مفعّل', 'value' => $encryptionEnabled ? 'yes' : 'no'],
            ['label' => 'الجدولة مفعّلة', 'value' => ($databaseScheduled || $fullScheduled) ? 'yes' : 'no'],
            ['label' => 'سياسة الاحتفاظ مفعّلة', 'value' => $retentionAnyPositive ? 'yes' : 'no'],
        ];

        $this->overallStatus = $this->computeOverallStatus($snapshot['health_status']);
    }

    /**
     * verified: آخر نسخة ناجحة integrity_verified=true.
     * failed: آخر نسخة ناجحة integrity_verified=false (فُحصت ورسبت).
     * unverified: آخر نسخة ناجحة موجودة لكن integrity_verified=null (لم تُفحَص
     * بعد) — هذه فقط تُعرَض كـ"Unknown" في قسم التحقق من الجاهزية، وفق الطلب
     * الصريح بعدم التخمين عند غياب بيانات حقيقية.
     * none: لا توجد أي نسخة ناجحة إطلاقاً.
     */
    private function integrityState(?Backup $lastSuccessful): string
    {
        if ($lastSuccessful === null) {
            return 'none';
        }

        return match ($lastSuccessful->integrity_verified) {
            true  => 'verified',
            false => 'failed',
            null  => 'unverified',
        };
    }

    /**
     * Badge واحدة فقط: أسوأ حالة بين health_status من BackupMonitoringService
     * (كما طُلِب صراحة) وكل عنصر في "سلامة النظام" أعلاه. اختزال ترتيبي بسيط
     * (0=healthy, 1=warning, 2=critical) — لا Engine/Service جديد، Helper محلي فقط.
     */
    private function computeOverallStatus(string $monitoringHealthStatus): string
    {
        $severity = ['healthy' => 0, 'warning' => 1, 'critical' => 2];

        $worst = $severity[$monitoringHealthStatus] ?? 2;

        /** @var Collection $checks */
        $checks = collect($this->integrityChecks);

        $worst = $checks->reduce(
            fn (int $carry, array $check) => max($carry, $severity[$check['status']] ?? 2),
            $worst
        );

        return match ($worst) {
            0       => 'READY',
            1       => 'WARNING',
            default => 'CRITICAL',
        };
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}

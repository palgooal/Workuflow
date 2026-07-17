<?php

namespace App\Services\Backup;

use App\Jobs\Backup\RunSystemBackupJob;
use App\Models\ActivityLog;
use App\Models\Backup;
use App\Models\Setting;
use App\Support\Enums\BackupStatus;
use App\Support\Enums\BackupTrigger;
use App\Support\Enums\BackupType;
use Illuminate\Support\Facades\Log;

/**
 * ScheduledBackupRunner — طبقة جدولة فقط فوق نظام النسخ الاحتياطي الحالي
 * (المرحلة الخامسة). لا يحتوي أي منطق نسخ فعلي — يُنشئ سجل Backup
 * (triggered_by=scheduled) بالضبط كما يفعل زر "إنشاء نسخة يدوية" في
 * Filament، ثم يُطلِق RunSystemBackupJob الحالي دون أي تعديل عليه.
 *
 * ⚠️ لا تعديل على SystemBackupService/RunSystemBackupJob/BackupRetentionService
 * هنا أو بسبب هذا الملف — كل التنفيذ الفعلي يبقى كما هو.
 *
 * يُستدعى من routes/console.php (Schedule::call) — راجع docs/BACKUP-SYSTEM.md.
 */
class ScheduledBackupRunner
{
    /**
     * ينفَّذ من الجدولة. يتحقق من التفعيل ومن عدم وجود نسخة أخرى قيد
     * التنفيذ حالياً قبل إنشاء أي شيء، ثم يُسجّل الأحداث المطلوبة.
     */
    public function run(BackupType $type): void
    {
        if (! $this->isEnabled($type)) {
            // لا تسجيل هنا عمداً: ->when() في routes/console.php يمنع حتى
            // استدعاء run() أصلاً عند التعطيل — هذا الفحص دفاع إضافي فقط
            // (مثلاً استدعاء مباشر يدوي لهذه الدالة لأغراض الاختبار).
            return;
        }

        if ($this->isAnotherBackupRunning()) {
            Log::warning('Scheduled backup skipped (another backup is already running)', [
                'type' => $type->value,
            ]);

            ActivityLog::record(
                eventType: 'backup.scheduled_skipped',
                metadata: ['type' => $type->value, 'reason' => 'another_backup_running'],
            );

            return;
        }

        Log::info('Scheduled backup started', ['type' => $type->value]);

        $backup = Backup::create([
            'name'         => 'scheduled-'.$type->value.'-'.now()->format('Ymd-His'),
            'type'         => $type,
            'status'       => BackupStatus::Pending,
            'triggered_by' => BackupTrigger::Scheduled,
        ]);

        ActivityLog::record(
            eventType: 'backup.scheduled_started',
            entityType: Backup::class,
            entityId: $backup->id,
            metadata: ['type' => $type->value],
        );

        RunSystemBackupJob::dispatch($backup->id);
    }

    /**
     * هل نوع النسخة هذا مفعَّل من إعدادات الإدارة (الإعدادات → النسخ
     * الاحتياطي)؟ الافتراضي عند عدم وجود إعداد محفوظ = مفعَّل (true)، حفاظاً
     * على السلوك الحالي في الإنتاج (الجدولة كانت تعمل دائماً قبل هذه المرحلة).
     */
    public function isEnabled(BackupType $type): bool
    {
        $settings = Setting::group('backup_schedule');

        $key = $type === BackupType::Database
            ? 'database_backup_enabled'
            : 'full_backup_enabled';

        return ($settings[$key] ?? '1') === '1';
    }

    /**
     * راجع "ثالثاً" في طلب المرحلة الخامسة: فحص صريح لعدم وجود Backup آخر
     * بحالة running قبل بدء أي نسخة مجدولة جديدة — منفصل عن withoutOverlapping()
     * في routes/console.php (التي تمنع فقط تداخل نفس الحدث المجدوَل مع نفسه).
     */
    private function isAnotherBackupRunning(): bool
    {
        return Backup::query()->runningOnes()->exists();
    }
}

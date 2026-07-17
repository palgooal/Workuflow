<?php

namespace App\Observers;

use App\Filament\Resources\BackupResource;
use App\Models\Backup;
use App\Models\User;
use App\Support\Enums\BackupStatus;
use App\Support\Enums\BackupTrigger;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * BackupObserver — يُسجّل "Scheduled backup completed"/"Scheduled backup
 * failed" عندما ينتقل سجل Backup (triggered_by=scheduled) فعلياً إلى
 * completed/failed، بمراقبة تغيّر status فقط — بدون أي تعديل على
 * SystemBackupService أو RunSystemBackupJob (اللذين يستدعيان
 * markCompleted()/markFailed() الحاليتين دون تغيير).
 *
 * المرحلة السابعة (Backup Failure Notifications — Phase 1): عند فشل نسخة
 * *مجدولة* فقط، يُرسَل أيضاً إشعار Filament Database Notification لكل
 * مستخدمي super_admin — داخل لوحة الإدارة فقط، بلا Email/Slack/Telegram/
 * Webhooks/أي قناة خارجية. يعتمد على نفس فحص wasChanged('status') أدناه
 * فقط لمنع التكرار — لا منطق انتقال حالة إضافي أو مكرَّر.
 *
 * مسجَّل في AppServiceProvider::boot() عبر Backup::observe(BackupObserver::class).
 */
class BackupObserver
{
    public function updated(Backup $backup): void
    {
        if ($backup->triggered_by !== BackupTrigger::Scheduled) {
            return;
        }

        if (! $backup->wasChanged('status')) {
            return;
        }

        if ($backup->status === BackupStatus::Completed) {
            Log::info('Scheduled backup completed', [
                'backup_id' => $backup->id,
                'type'      => $backup->type->value,
            ]);
        } elseif ($backup->status === BackupStatus::Failed) {
            Log::error('Scheduled backup failed', [
                'backup_id' => $backup->id,
                'type'      => $backup->type->value,
                'error'     => $backup->error_message,
            ]);

            $this->notifySuperAdminsOfScheduledFailure($backup);
        }
    }

    /**
     * إشعار داخلي فقط (Filament Database Notification، يظهر في جرس الإشعارات
     * داخل /admin) — لا يخرج خارج التطبيق بأي شكل. يُستدعى فقط من الفرع أعلاه
     * (triggered_by=scheduled AND status انتقلت فعلياً إلى failed)، لذا لا
     * تكرار: أي Update لاحق لا يغيّر status (اسم/حجم/checksum/إلخ) لا يستدعي
     * هذه الدالة إطلاقاً بسبب wasChanged('status') في updated() أعلاه.
     */
    private function notifySuperAdminsOfScheduledFailure(Backup $backup): void
    {
        $superAdmins = $this->getSuperAdmins();

        if ($superAdmins->isEmpty()) {
            return;
        }

        $reason = $backup->error_message ?: 'سبب غير معروف.';
        $time   = optional($backup->completed_at)->format('Y-m-d H:i') ?? now()->format('Y-m-d H:i');

        // ⚠️ try/catch ضيّق حول إرسال الإشعار فقط (وليس كل منطق الـObserver):
        // markFailed() تكون قد حفظت status=failed في قاعدة البيانات فعلياً
        // قبل وصول التنفيذ إلى هنا (Eloquent يُطلِق updated() بعد الحفظ). أي
        // استثناء من طبقة الإشعارات (مثلاً مشكلة في توليد الرابط أو في قناة
        // sendToDatabase) يجب ألا يُفشِل استدعاء markFailed() نفسه من منظور
        // المُستدعي — يُسجَّل الخطأ ويُكمل التنفيذ بصورة طبيعية.
        try {
            Notification::make()
                ->title('فشل إنشاء النسخة الاحتياطية')
                ->body(
                    "فشلت النسخة المجدولة.\n\n".
                    "النوع:\n{$backup->type->label()}\n\n".
                    "السبب:\n{$reason}\n\n".
                    "الوقت:\n{$time}"
                )
                ->danger()
                ->actions([
                    Action::make('view')
                        ->label('عرض لوحة النسخ الاحتياطية')
                        // ⚠️ panel صريح — راجع getBackupResourceUrl(): هذا الـObserver
                        // قد يعمل من Scheduler/CLI/Queue/خارج أي طلب HTTP داخل /admin،
                        // حيث Filament::getCurrentPanel() تكون null. تمرير panel:'admin'
                        // صراحة يجعل توليد الرابط مستقلاً تماماً عن "اللوحة الحالية".
                        ->url($this->getBackupResourceUrl($backup))
                        ->markAsRead(),
                ])
                ->sendToDatabase($superAdmins);
        } catch (\Throwable $e) {
            Log::error('Failed to send scheduled backup failure notification', [
                'backup_id' => $backup->id,
                'error'     => $e->getMessage(),
            ]);
        }
    }

    /**
     * يولّد رابط سجل النسخة داخل BackupResource مع تحديد لوحة الإدارة صراحةً
     * (panel: 'admin' — معرّف اللوحة الفعلي في AdminPanelProvider::panel()->id('admin'))
     * بدل الاعتماد ضمنياً على Filament::getCurrentPanel()، التي تبقى null في
     * أي سياق خارج طلب HTTP فعلي داخل /admin (Scheduler، أوامر Artisan،
     * Queue Worker، الاختبارات). راجع BackupResource::getPages() — توجد صفحة
     * 'view' مخصَّصة لعرض سجل واحد، فتُستخدَم مباشرة بدل صفحة index العامة.
     */
    private function getBackupResourceUrl(Backup $backup): string
    {
        return BackupResource::getUrl('view', ['record' => $backup], panel: 'admin');
    }

    /**
     * يجلب كل مستخدمي super_admin عبر علاقة الأدوار مباشرة (استعلام واحد)،
     * دون استخدام Spatie scope role() — التي ترمي RoleDoesNotExist إذا لم
     * يكن الدور موجوداً بعد (Seeded) أصلاً، وهو سيناريو ممكن تماماً في هذا
     * الـObserver التشغيلي العام (مثلاً بيئة جديدة لم تُشغَّل فيها بذور
     * الأدوار بعد). عدم وجود الدور/أي super_admin هنا ليس خطأً يستوجب
     * استثناءً — ببساطة لا يوجد أحد يُخطَر، وتستمر markFailed() بنجاح كما هي.
     */
    private function getSuperAdmins(): \Illuminate\Support\Collection
    {
        return User::query()
            ->whereHas('roles', function ($query): void {
                $query
                    ->where('name', 'super_admin')
                    ->where('guard_name', 'web');
            })
            ->get();
    }
}

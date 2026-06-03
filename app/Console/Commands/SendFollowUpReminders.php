<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Modules\CRM\Services\FollowUpService;
use App\Notifications\FollowUpReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * SendFollowUpReminders — إرسال تذكيرات المتابعات المستحقة
 *
 * يُشغَّل كل 30 دقيقة عبر Scheduler.
 * يُرسل إشعاراً داخلياً (database) لكل متابعة لها reminder_at <= now().
 *
 * نافذة الفحص: الساعة الأخيرة فقط (مُعرَّفة في FollowUpService::dueForReminder())
 * لتجنب الإرسال المتكرر عبر دورات Scheduler المتعاقبة.
 */
class SendFollowUpReminders extends Command
{
    protected $signature   = 'crm:send-follow-up-reminders {--dry-run : اعرض فقط بدون إرسال}';
    protected $description = 'إرسال تذكيرات المتابعات التي حان وقتها';

    public function __construct(private readonly FollowUpService $followUpService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun   = (bool) $this->option('dry-run');
        $followUps = $this->followUpService->dueForReminder();

        if ($followUps->isEmpty()) {
            $this->info('لا توجد تذكيرات مستحقة الآن.');
            return self::SUCCESS;
        }

        $this->info("📋 تذكيرات مستحقة: {$followUps->count()}");

        $sent   = 0;
        $failed = 0;

        foreach ($followUps as $followUp) {
            try {
                // استخراج المستخدم عبر العلاقة client.user
                $user = $followUp->client?->user ?? User::find($followUp->user_id);

                if (!$user) {
                    $this->warn("  ⚠ متابعة {$followUp->id}: مستخدم غير موجود — تخطّي.");
                    continue;
                }

                if ($dryRun) {
                    $this->line("  [dry-run] → {$user->email}: {$followUp->title}");
                    $sent++;
                    continue;
                }

                $user->notify(new FollowUpReminderNotification($followUp));
                $sent++;

            } catch (\Throwable $e) {
                $failed++;
                Log::warning("SendFollowUpReminders: فشل لمتابعة {$followUp->id}: {$e->getMessage()}");
                $this->warn("  ✗ فشل: {$followUp->id} — {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("✓ أُرسل: {$sent} | ✗ فشل: {$failed}");

        Log::info("crm:send-follow-up-reminders — sent={$sent} failed={$failed}");

        return self::SUCCESS;
    }
}

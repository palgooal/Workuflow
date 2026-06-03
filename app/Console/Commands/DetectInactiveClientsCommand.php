<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Modules\CRM\Services\AutomationRuleEngine;
use App\Support\Enums\UserStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * DetectInactiveClientsCommand — اكتشاف العملاء الخاملين وتشغيل الأتمتة
 *
 * GAP-05 Fix — Sprint 6
 *
 * يُشغَّل يومياً الساعة 04:00 ويُقيِّم 3 triggers على كل المستخدمين النشطين:
 *
 *   1. days_since_contact  — عملاء لم يُتواصل معهم منذ فترة
 *   2. health_score_below  — عملاء انخفض مؤشر صحتهم
 *   3. invoice_overdue     — عملاء لديهم فواتير متأخرة
 *
 * الـ AutomationRuleEngine يُقيّم الشروط ويُطلق Actions عبر Queue.
 * لا يفعل الـ Command شيئاً إذا لم تكن هناك قواعد مُفعَّلة للـ trigger.
 *
 * الاستخدام:
 *   php artisan crm:detect-inactive
 *   php artisan crm:detect-inactive --user=5
 *   php artisan crm:detect-inactive --trigger=days_since_contact
 *   php artisan crm:detect-inactive --dry-run
 */
class DetectInactiveClientsCommand extends Command
{
    protected $signature = 'crm:detect-inactive
                            {--user=       : تشغيل لمستخدم محدد فقط}
                            {--trigger=    : تشغيل trigger محدد فقط (days_since_contact|health_score_below|invoice_overdue)}
                            {--dry-run     : تقرير فقط بدون تشغيل Actions}';

    protected $description = 'اكتشاف العملاء الخاملين وتشغيل قواعد الأتمتة (days_since_contact | health_score_below | invoice_overdue)';

    // الـ Triggers التي يُقيّمها هذا الـ Command يومياً
    private const DAILY_TRIGGERS = [
        'days_since_contact',
        'health_score_below',
        'invoice_overdue',
    ];

    public function __construct(private readonly AutomationRuleEngine $engine)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $startTime  = microtime(true);
        $dryRun     = (bool) $this->option('dry-run');
        $userId     = $this->option('user') ? (int) $this->option('user') : null;
        $onlyTrigger = $this->option('trigger');

        // تحديد الـ triggers المطلوبة
        $triggers = $onlyTrigger
            ? [$onlyTrigger]
            : self::DAILY_TRIGGERS;

        // تحديد المستخدمين
        $usersQuery = User::query()->where('status', UserStatus::Active->value);
        if ($userId) {
            $usersQuery->where('id', $userId);
        }
        $users = $usersQuery->get(['id', 'name', 'email']);

        if ($users->isEmpty()) {
            $this->warn('لا يوجد مستخدمون نشطون.');
            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->warn('🔍 وضع المعاينة — لن تُشغَّل أي Actions فعلياً.');
        }

        $this->info("🤖 اكتشاف العملاء الخاملين — {$users->count()} مستخدم، triggers: " . implode(', ', $triggers));
        $this->newLine();

        $totalFired = 0;
        $totalErrors = 0;

        foreach ($triggers as $trigger) {
            $triggerFired = 0;
            $this->line("  ▶ trigger: <fg=cyan>{$trigger}</>");

            foreach ($users as $user) {
                try {
                    if ($dryRun) {
                        // في وضع المعاينة: أحصِ القواعد النشطة فقط
                        $count = \App\Modules\CRM\Models\AutomationRule::where('user_id', $user->id)
                            ->where('trigger', $trigger)
                            ->where('is_active', true)
                            ->count();
                        if ($count > 0) {
                            $this->line("    [dry-run] {$user->email}: {$count} قاعدة نشطة");
                            $triggerFired += $count;
                        }
                    } else {
                        $fired = $this->engine->evaluateForAllClients($user->id, $trigger);
                        $triggerFired += $fired;
                    }
                } catch (\Throwable $e) {
                    $totalErrors++;
                    Log::warning("DetectInactiveClients: خطأ للمستخدم {$user->id} trigger={$trigger}: {$e->getMessage()}");
                    $this->warn("    ✗ خطأ للمستخدم {$user->id}: {$e->getMessage()}");
                }
            }

            $this->line("    → أُطلق: {$triggerFired} action(s)");
            $totalFired += $triggerFired;
        }

        $duration = round(microtime(true) - $startTime, 2);

        $this->newLine();
        $this->table(
            ['المقياس', 'القيمة'],
            [
                ['المستخدمون', $users->count()],
                ['الـ Triggers', implode(', ', $triggers)],
                ['Actions المُطلَقة', $totalFired],
                ['الأخطاء', $totalErrors],
                ['المدة', "{$duration} ثانية"],
            ]
        );

        Log::info("crm:detect-inactive completed — triggers=" . implode(',', $triggers)
            . " users={$users->count()} fired={$totalFired} errors={$totalErrors} duration={$duration}s");

        $this->info('✅ اكتمل.');

        return self::SUCCESS;
    }
}

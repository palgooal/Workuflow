<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Modules\CRM\Services\ClientHealthScoreService;
use App\Modules\CRM\Services\SmartTagSuggestionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * RecalculateHealthScoresCommand — إعادة حساب مؤشرات صحة العملاء
 *
 * Sprint 5 — S5.2
 *
 * الاستخدام:
 *   php artisan crm:recalculate-health-scores
 *   php artisan crm:recalculate-health-scores --user=5
 *   php artisan crm:recalculate-health-scores --apply-tags
 *
 * مُجدوَل يومياً في routes/console.php الساعة 02:00
 */
class RecalculateHealthScoresCommand extends Command
{
    protected $signature = 'crm:recalculate-health-scores
                            {--user= : معرّف مستخدم محدد (اختياري)}
                            {--apply-tags : تطبيق اقتراحات الوسوم ذات الثقة العالية تلقائياً}
                            {--chunk=200 : حجم الدفعة}';

    protected $description = 'إعادة حساب مؤشر صحة العملاء لكل المستخدمين (أو مستخدم محدد)';

    public function __construct(
        private readonly ClientHealthScoreService  $healthService,
        private readonly SmartTagSuggestionService $tagSuggestionService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $startTime  = microtime(true);
        $userId     = $this->option('user') ? (int)$this->option('user') : null;
        $applyTags  = (bool)$this->option('apply-tags');
        $chunkSize  = (int)($this->option('chunk') ?? 200);

        $this->info('🔄 بدء إعادة حساب مؤشرات صحة العملاء...');

        // تحديد المستخدمين المستهدفين
        $usersQuery = User::query()->where('status', \App\Support\Enums\UserStatus::Active->value);

        if ($userId) {
            $usersQuery->where('id', $userId);
        }

        $users      = $usersQuery->get(['id', 'name', 'email']);
        $totalUsers = $users->count();

        if ($totalUsers === 0) {
            $this->warn('لا يوجد مستخدمون نشطون.');
            return self::SUCCESS;
        }

        $this->info("📊 معالجة {$totalUsers} مستخدم...");

        $bar              = $this->output->createProgressBar($totalUsers);
        $totalProcessed   = 0;
        $totalTagsApplied = 0;

        $bar->start();

        foreach ($users as $user) {
            $result = $this->healthService->recalculateForUser($user->id, $chunkSize);
            $totalProcessed += $result['processed'];

            // تطبيق الوسوم التلقائية إذا طُلب
            if ($applyTags && $result['processed'] > 0) {
                $tagsApplied       = $this->tagSuggestionService->applyAutoRules($user->id);
                $totalTagsApplied += $tagsApplied;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $duration = round(microtime(true) - $startTime, 2);

        $this->table(
            ['المقياس', 'القيمة'],
            [
                ['العملاء المعالجون', number_format($totalProcessed)],
                ['المستخدمون',        $totalUsers],
                ['الوسوم المطبَّقة',  $applyTags ? number_format($totalTagsApplied) : 'معطَّل'],
                ['المدة',             "{$duration} ثانية"],
            ]
        );

        Log::info("crm:recalculate-health-scores completed — {$totalProcessed} clients | {$duration}s", [
            'users'        => $totalUsers,
            'tags_applied' => $totalTagsApplied,
        ]);

        $this->info('✅ اكتمل بنجاح.');

        return self::SUCCESS;
    }
}

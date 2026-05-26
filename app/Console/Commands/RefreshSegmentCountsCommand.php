<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Modules\CRM\Services\ClientSegmentEngine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * RefreshSegmentCountsCommand — تحديث أعداد الشرائح الديناميكية
 *
 * Sprint 5 — S5.3
 *
 * الاستخدام:
 *   php artisan crm:refresh-segments
 *   php artisan crm:refresh-segments --user=5
 *
 * مُجدوَل يومياً في routes/console.php الساعة 03:30
 */
class RefreshSegmentCountsCommand extends Command
{
    protected $signature = 'crm:refresh-segments
                            {--user= : معرّف مستخدم محدد (اختياري)}';

    protected $description = 'تحديث أعداد العملاء في الشرائح الديناميكية';

    public function __construct(
        private readonly ClientSegmentEngine $engine,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $startTime = microtime(true);
        $userId    = $this->option('user') ? (int)$this->option('user') : null;

        $this->info('🔄 تحديث أعداد الشرائح الديناميكية...');

        $usersQuery = User::query()->where('suspended', false);
        if ($userId) {
            $usersQuery->where('id', $userId);
        }

        $users        = $usersQuery->get(['id']);
        $totalUpdated = 0;

        foreach ($users as $user) {
            $updated       = $this->engine->refreshCountsForUser($user->id);
            $totalUpdated += $updated;
        }

        $duration = round(microtime(true) - $startTime, 2);

        $this->info("✅ تم تحديث {$totalUpdated} شريحة في {$duration} ثانية.");

        Log::info("crm:refresh-segments completed — {$totalUpdated} segments | {$duration}s");

        return self::SUCCESS;
    }
}

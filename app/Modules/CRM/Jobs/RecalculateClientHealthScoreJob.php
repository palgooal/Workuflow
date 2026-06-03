<?php

namespace App\Modules\CRM\Jobs;

use App\Models\Client;
use App\Modules\CRM\Services\ClientHealthScoreService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * RecalculateClientHealthScoreJob — إعادة حساب Health Score لعميل واحد
 *
 * GAP-02 Fix — يُطلَق بعد كل دفعة فاتورة (markPaid)
 *
 * يعمل على queue: 'crm-default'
 * tries = 2 — إذا فشل مرتين يُتجاوز بصمت (لا يُعيق تجربة المستخدم)
 *
 * الاستخدام:
 *   RecalculateClientHealthScoreJob::dispatch($client->id);
 */
class RecalculateClientHealthScoreJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 30;

    public function __construct(public readonly int $clientId) {}

    public function handle(ClientHealthScoreService $healthService): void
    {
        $client = Client::find($this->clientId);

        if (!$client) {
            Log::warning("RecalculateClientHealthScoreJob: عميل #{$this->clientId} غير موجود — تخطّي.");
            return;
        }

        if ($client->is_archived) {
            return; // لا داعي لحساب مؤشر عميل مؤرشف
        }

        try {
            $score = $healthService->calculate($client);
            Log::info("RecalculateClientHealthScoreJob: client #{$this->clientId} → score={$score}");
        } catch (\Throwable $e) {
            Log::warning("RecalculateClientHealthScoreJob: فشل لعميل #{$this->clientId}: {$e->getMessage()}");
            throw $e; // أعِد الرمي ليُحاوَل مرة أخرى (tries=2)
        }
    }

    public function uniqueId(): string
    {
        // منع تراكم Jobs متعددة لنفس العميل في وقت واحد
        return "health-score-{$this->clientId}";
    }
}

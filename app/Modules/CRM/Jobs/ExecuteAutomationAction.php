<?php

namespace App\Modules\CRM\Jobs;

use App\Models\Client;
use App\Modules\CRM\Automation\BaseAutomationAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * ExecuteAutomationAction — تنفيذ إجراء أتمتة بشكل غير متزامن
 *
 * Sprint 6 — S6.2
 *
 * يُشغَّل على queue: 'automations'
 * كل Action مستقلة في Job منفصل — إذا فشل action واحد، لا يؤثر على الباقين
 *
 * الاستخدام:
 *   ExecuteAutomationAction::dispatch(
 *       clientId:   $client->id,
 *       userId:     $rule->user_id,
 *       ruleId:     $rule->id,
 *       actionType: 'assign_tag',
 *       params:     ['tag_slug' => 'vip'],
 *   )->onQueue('automations');
 */
class ExecuteAutomationAction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;
    public int $backoff = 30;

    public function __construct(
        public readonly int    $clientId,
        public readonly int    $userId,
        public readonly int    $ruleId,
        public readonly string $actionType,
        public readonly array  $params  = [],
        public readonly array  $context = [],
    ) {}

    // ==================== Handle ====================

    public function handle(): void
    {
        $client = Client::find($this->clientId);

        if (!$client) {
            Log::warning("ExecuteAutomationAction: client {$this->clientId} not found — skipping");
            return;
        }

        // تجاهل العملاء المؤرشفين
        if ($client->is_archived) {
            Log::info("ExecuteAutomationAction: client {$this->clientId} is archived — skipping");
            return;
        }

        $action = BaseAutomationAction::make($this->actionType);

        if (!$action) {
            Log::warning("ExecuteAutomationAction: unknown action type '{$this->actionType}'");
            return;
        }

        // Guard
        if (!$action->canExecute($client, $this->params)) {
            Log::info("ExecuteAutomationAction: canExecute=false for client {$this->clientId} | action={$this->actionType}");
            return;
        }

        try {
            $action->execute($client, $this->userId, $this->params);

            Log::info("ExecuteAutomationAction: executed '{$this->actionType}' for client {$this->clientId} | rule={$this->ruleId}");

        } catch (Throwable $e) {
            Log::error("ExecuteAutomationAction: failed '{$this->actionType}' for client {$this->clientId}: {$e->getMessage()}", [
                'rule_id'   => $this->ruleId,
                'params'    => $this->params,
                'exception' => $e->getMessage(),
            ]);

            throw $e; // إعادة الرمي لتسجيل failed_jobs
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error("ExecuteAutomationAction: permanently failed '{$this->actionType}' after {$this->tries} tries | client={$this->clientId} rule={$this->ruleId}");
    }

    /**
     * اسم فريد للـ Job في queue (يمنع تكرار نفس الإجراء على نفس العميل)
     */
    public function uniqueId(): string
    {
        return "{$this->ruleId}-{$this->clientId}-{$this->actionType}";
    }
}

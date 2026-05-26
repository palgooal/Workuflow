<?php

namespace App\Modules\CRM\Services;

use App\Models\Client;
use App\Modules\CRM\Jobs\ExecuteAutomationAction;
use App\Modules\CRM\Models\AutomationRule;
use Illuminate\Support\Facades\Log;

/**
 * AutomationRuleEngine — محرك تقييم وتشغيل قواعد الأتمتة
 *
 * Sprint 6 — S6.1
 *
 * الاستخدام:
 *   app(AutomationRuleEngine::class)->evaluate($client, 'client_created');
 *   app(AutomationRuleEngine::class)->evaluate($client, 'status_changed', ['from' => 'prospect', 'to' => 'active']);
 *
 * الـ Actions تُنفَّذ **async** عبر Jobs (على queue 'automations')
 * لحماية أداء الـ request الحالي من التأثر.
 */
class AutomationRuleEngine
{
    public function __construct(
        private readonly AutomationConditionEvaluator $evaluator,
    ) {}

    // ==================== Main Entry ====================

    /**
     * تقييم كل القواعد النشطة للـ trigger وإطلاق Actions المطابقة
     *
     * @param  array  $context  بيانات سياق إضافية (مثلاً: ['from_status' => 'prospect', 'to_status' => 'active'])
     * @return int عدد القواعد التي أُطلقت
     */
    public function evaluate(Client $client, string $trigger, array $context = []): int
    {
        $rules = $this->getActiveRules($client->user_id, $trigger);

        if ($rules->isEmpty()) return 0;

        $fired = 0;

        foreach ($rules as $rule) {
            try {
                if ($this->evaluator->evaluate($client, $rule->conditions)) {
                    $this->dispatchActions($client, $rule, $context);
                    $rule->recordRun();
                    $fired++;

                    Log::info("AutomationRuleEngine: fired rule [{$rule->name}] for client {$client->id} | trigger={$trigger}");
                }
            } catch (\Throwable $e) {
                Log::error("AutomationRuleEngine: error evaluating rule {$rule->id}: {$e->getMessage()}", [
                    'client_id' => $client->id,
                    'trigger'   => $trigger,
                    'rule'      => $rule->id,
                ]);
            }
        }

        return $fired;
    }

    /**
     * تقييم trigger محدد لكل عملاء مستخدم (للأتمتة الليلية)
     * مثلاً: days_since_contact | invoice_overdue
     */
    public function evaluateForAllClients(int $userId, string $trigger): int
    {
        $rules = $this->getActiveRules($userId, $trigger);
        if ($rules->isEmpty()) return 0;

        $totalFired = 0;

        Client::where('user_id', $userId)
              ->where('is_archived', false)
              ->with(['tags:id,slug', 'followUps' => fn ($q) => $q->where('status', 'pending')])
              ->chunkById(100, function ($clients) use ($rules, &$totalFired) {
                  foreach ($clients as $client) {
                      foreach ($rules as $rule) {
                          try {
                              if ($this->evaluator->evaluate($client, $rule->conditions)) {
                                  $this->dispatchActions($client, $rule);
                                  $rule->recordRun();
                                  $totalFired++;
                              }
                          } catch (\Throwable $e) {
                              Log::error("AutomationRuleEngine: batch error rule {$rule->id} client {$client->id}: {$e->getMessage()}");
                          }
                      }
                  }
              });

        return $totalFired;
    }

    // ==================== Dispatch ====================

    private function dispatchActions(Client $client, AutomationRule $rule, array $context = []): void
    {
        $actions = $rule->actions ?? [];

        foreach ($actions as $action) {
            if (empty($action['type'])) continue;

            ExecuteAutomationAction::dispatch(
                clientId:  $client->id,
                userId:    $rule->user_id,
                ruleId:    $rule->id,
                actionType: $action['type'],
                params:    $action['params'] ?? [],
                context:   $context,
            )->onQueue('automations');
        }
    }

    // ==================== Query ====================

    private function getActiveRules(int $userId, string $trigger): \Illuminate\Support\Collection
    {
        return AutomationRule::where('user_id', $userId)
                             ->where('trigger', $trigger)
                             ->where('is_active', true)
                             ->orderBy('priority')
                             ->orderBy('created_at')
                             ->get();
    }
}

<?php

namespace App\Modules\CRM\Automation;

use App\Models\Client;
use App\Modules\CRM\DTOs\CreateFollowUpDTO;
use App\Modules\CRM\Services\FollowUpService;
use Illuminate\Support\Facades\Log;

/**
 * CreateFollowUpAutomationAction — إنشاء متابعة تلقائية
 *
 * params: {
 *   "message":      "تواصل مع العميل",
 *   "days_from_now": 3,
 *   "type":         "call"    (اختياري — default: call)
 * }
 */
class CreateFollowUpAutomationAction extends BaseAutomationAction
{
    public function __construct(
        private readonly FollowUpService $followUpService,
    ) {}

    public static function type(): string  { return 'create_follow_up'; }
    public static function label(): string { return 'إنشاء متابعة'; }

    public function execute(Client $client, int $userId, array $params = []): void
    {
        $message    = $params['message']      ?? 'متابعة تلقائية';
        $daysFromNow = (int)($params['days_from_now'] ?? 1);
        $followUpType = $params['type'] ?? 'call';

        // لا تُنشئ متابعة إذا كان هناك متابعة معلقة مطابقة مسبقاً
        $existingPending = $client->followUps()
                                  ->where('status', 'pending')
                                  ->where('notes', $message)
                                  ->exists();

        if ($existingPending) {
            Log::info("CreateFollowUpAutomationAction: skipped duplicate for client {$client->id}");
            return;
        }

        $dto = new CreateFollowUpDTO(
            clientId: $client->id,
            userId:   $userId,
            notes:    $message,
            dueAt:    now()->addDays($daysFromNow),
            type:     $followUpType,
        );

        $this->followUpService->create($dto);
        Log::info("CreateFollowUpAutomationAction: created for client {$client->id} in {$daysFromNow} days");
    }
}

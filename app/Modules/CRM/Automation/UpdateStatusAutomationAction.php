<?php

namespace App\Modules\CRM\Automation;

use App\Models\Client;
use App\Modules\CRM\DTOs\UpdateClientDTO;
use App\Modules\CRM\Enums\ClientStatus;
use App\Modules\CRM\Services\ClientService;
use Illuminate\Support\Facades\Log;

/**
 * UpdateStatusAutomationAction — تغيير حالة العميل
 *
 * params: { "status": "inactive" }
 */
class UpdateStatusAutomationAction extends BaseAutomationAction
{
    public function __construct(
        private readonly ClientService $clientService,
    ) {}

    public static function type(): string  { return 'update_status'; }
    public static function label(): string { return 'تغيير الحالة'; }

    public function execute(Client $client, int $userId, array $params = []): void
    {
        $statusValue = $params['status'] ?? null;
        if (!$statusValue) {
            Log::warning("UpdateStatusAutomationAction: missing status for client {$client->id}");
            return;
        }

        $newStatus = ClientStatus::tryFrom($statusValue);
        if (!$newStatus) {
            Log::warning("UpdateStatusAutomationAction: invalid status '{$statusValue}'");
            return;
        }

        // لا تُغيّر إذا كانت الحالة نفسها
        $currentStatus = $client->status instanceof ClientStatus
            ? $client->status
            : ClientStatus::tryFrom((string)$client->status);

        if ($currentStatus === $newStatus) return;

        // التحقق من صلاحية الانتقال
        if ($currentStatus && !$currentStatus->canTransitionTo($newStatus)) {
            Log::warning("UpdateStatusAutomationAction: invalid transition {$currentStatus->value} → {$newStatus->value} for client {$client->id}");
            return;
        }

        $dto = new UpdateClientDTO(status: $newStatus);
        $this->clientService->update($client, $dto);

        Log::info("UpdateStatusAutomationAction: client {$client->id} status → {$newStatus->value}");
    }

    public function canExecute(Client $client, array $params = []): bool
    {
        return parent::canExecute($client, $params)
            && $client->status !== ClientStatus::Archived;
    }
}

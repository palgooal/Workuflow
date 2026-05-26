<?php

namespace App\Modules\CRM\Services;

use App\Models\Client;
use App\Modules\CRM\Actions\FollowUp\CancelFollowUpAction;
use App\Modules\CRM\Actions\FollowUp\CompleteFollowUpAction;
use App\Modules\CRM\Actions\FollowUp\CreateFollowUpAction;
use App\Modules\CRM\DTOs\CreateFollowUpDTO;
use App\Modules\CRM\Enums\FollowUpStatus;
use App\Modules\CRM\Models\ClientFollowUp;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class FollowUpService
{
    public function __construct(
        private readonly CreateFollowUpAction   $createAction,
        private readonly CompleteFollowUpAction $completeAction,
        private readonly CancelFollowUpAction   $cancelAction,
    ) {}

    // ==================== CRUD ====================

    public function create(CreateFollowUpDTO $dto): ClientFollowUp
    {
        return $this->createAction->execute($dto);
    }

    public function complete(ClientFollowUp $followUp, int $actorId, ?string $notes = null): ClientFollowUp
    {
        return $this->completeAction->execute($followUp, $actorId, $notes);
    }

    public function cancel(ClientFollowUp $followUp, int $actorId): ClientFollowUp
    {
        return $this->cancelAction->execute($followUp, $actorId);
    }

    // ==================== Queries ====================

    /**
     * متابعات المستخدم المستحقة اليوم وخلال أسبوع
     */
    public function upcoming(int $userId): Collection
    {
        return ClientFollowUp::where('user_id', $userId)
            ->whereIn('status', [FollowUpStatus::Pending->value, FollowUpStatus::Overdue->value])
            ->where('due_at', '<=', now()->addDays(7))
            ->orderBy('due_at')
            ->with('client:id,name,company')
            ->get()
            ->map(function (ClientFollowUp $f) {
                // تحديث الحالة الديناميكية (overdue) بدون حفظ
                $f->setAttribute('actual_status', $f->actualStatus()->value);
                return $f;
            });
    }

    /**
     * متابعات عميل محدد
     */
    public function forClient(Client $client, bool $pendingOnly = false): Collection
    {
        $query = $client->followUps()->with('client:id,name');

        if ($pendingOnly) {
            $query->whereIn('status', [
                FollowUpStatus::Pending->value,
                FollowUpStatus::Overdue->value,
            ]);
        }

        return $query->orderBy('due_at')->get();
    }

    /**
     * تحديث تلقائي للمتابعات المتأخرة (يُستدعى من Scheduler)
     */
    public function markOverdue(): int
    {
        return ClientFollowUp::where('status', FollowUpStatus::Pending->value)
            ->where('due_at', '<', now())
            ->update(['status' => FollowUpStatus::Overdue->value]);
    }

    /**
     * عدد المتابعات المعلّقة للمستخدم
     */
    public function pendingCount(int $userId): int
    {
        return ClientFollowUp::where('user_id', $userId)
            ->whereIn('status', [FollowUpStatus::Pending->value, FollowUpStatus::Overdue->value])
            ->count();
    }

    /**
     * المتابعات التي تحتاج إرسال تذكير الآن
     */
    public function dueForReminder(): Collection
    {
        return ClientFollowUp::whereIn('status', [
                FollowUpStatus::Pending->value,
                FollowUpStatus::Overdue->value,
            ])
            ->whereNotNull('reminder_at')
            ->where('reminder_at', '<=', now())
            ->where('reminder_at', '>=', now()->subHour()) // نافذة ساعة لتجنب التكرار
            ->with(['client:id,name,user_id', 'client.user:id,email,name'])
            ->get();
    }
}

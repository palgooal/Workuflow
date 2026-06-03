<?php

namespace App\Modules\CRM\Listeners;

use App\Modules\CRM\Actions\Activity\LogClientActivityAction;
use App\Modules\CRM\Events\FollowUpCompleted;
use Illuminate\Support\Facades\Log;

/**
 * C-01 Fix: $afterCommit = true — يُنفَّذ بعد اكتمال Transaction
 * يسجّل نشاط إتمام المتابعة بأمان بعد تحديث حالتها في DB.
 */
class LogFollowUpCompletedActivity
{
    public bool $afterCommit = true;

    public function __construct(
        private readonly LogClientActivityAction $logger,
    ) {}

    public function handle(FollowUpCompleted $event): void
    {
        try {
            $followUp = $event->followUp;

            $this->logger->execute(
                clientId:    $followUp->client_id,
                userId:      $event->actorId,
                type:        \App\Modules\CRM\Enums\ActivityType::FollowUpCompleted,
                description: "اكتملت المتابعة: {$followUp->title}",
                metadata:    ['completed_at' => $followUp->completed_at?->toDateTimeString()],
            );
        } catch (\Throwable $e) {
            Log::warning('LogFollowUpCompletedActivity failed: ' . $e->getMessage());
        }
    }
}

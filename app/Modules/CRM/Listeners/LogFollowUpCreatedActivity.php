<?php

namespace App\Modules\CRM\Listeners;

use App\Modules\CRM\Actions\Activity\LogClientActivityAction;
use App\Modules\CRM\Events\FollowUpCreated;
use Illuminate\Support\Facades\Log;

/**
 * C-01 Fix: $afterCommit = true — يُنفَّذ بعد اكتمال Transaction
 * يسجّل نشاط إنشاء المتابعة بأمان بعد حفظها في DB.
 */
class LogFollowUpCreatedActivity
{
    public bool $afterCommit = true;

    public function __construct(
        private readonly LogClientActivityAction $logger,
    ) {}

    public function handle(FollowUpCreated $event): void
    {
        try {
            $followUp = $event->followUp;

            $this->logger->execute(
                clientId:    $followUp->client_id,
                userId:      $event->actorId,
                type:        \App\Modules\CRM\Enums\ActivityType::FollowUpCreated,
                description: "متابعة مجدولة: {$followUp->title}",
                metadata:    ['due_at' => $followUp->due_at?->toDateTimeString()],
            );
        } catch (\Throwable $e) {
            Log::warning('LogFollowUpCreatedActivity failed: ' . $e->getMessage());
        }
    }
}

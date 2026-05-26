<?php

namespace App\Modules\CRM\Listeners;

use App\Modules\CRM\Actions\Activity\LogClientActivityAction;
use App\Modules\CRM\Events\ClientTagAssigned;
use App\Modules\CRM\Events\ClientTagRemoved;

/**
 * مستمع مشترك لتسجيل أنشطة الوسوم (assign + remove)
 */
class LogClientTagActivity
{
    public bool $afterCommit = true;

    public function __construct(
        private readonly LogClientActivityAction $logger,
    ) {}

    public function handleAssigned(ClientTagAssigned $event): void
    {
        $this->logger->tagAssigned(
            clientId: $event->client->id,
            userId:   $event->actorId,
            tagName:  $event->tag->name,
        );
    }

    public function handleRemoved(ClientTagRemoved $event): void
    {
        $this->logger->tagRemoved(
            clientId: $event->client->id,
            userId:   $event->actorId,
            tagName:  $event->tag->name,
        );
    }
}

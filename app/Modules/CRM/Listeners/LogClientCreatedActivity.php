<?php

namespace App\Modules\CRM\Listeners;

use App\Modules\CRM\Actions\Activity\LogClientActivityAction;
use App\Modules\CRM\Events\ClientCreated;

/**
 * C-01: $afterCommit = true — يُنفَّذ بعد اكتمال الـ Transaction
 * لضمان وجود سجل العميل قبل تسجيل النشاط.
 */
class LogClientCreatedActivity
{
    public bool $afterCommit = true;

    public function __construct(
        private readonly LogClientActivityAction $logger,
    ) {}

    public function handle(ClientCreated $event): void
    {
        try {
            $this->logger->clientCreated(
                clientId:   $event->client->id,
                userId:     $event->actorId,
                clientName: $event->client->name,
            );
        } catch (\Throwable $e) {
            // تجاهل خطأ الجدول المفقود — يُحل بتشغيل php artisan migrate
            \Illuminate\Support\Facades\Log::warning('LogClientCreatedActivity failed: ' . $e->getMessage());
        }
    }
}

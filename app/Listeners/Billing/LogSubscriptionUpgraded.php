<?php

namespace App\Listeners\Billing;

use App\Models\ActivityLog;
use App\Models\Subscription;

class LogSubscriptionUpgraded
{
    public function handle(object $event): void
    {
        /** @var Subscription $subscription */
        $subscription = $event->subscription;

        ActivityLog::record(
            eventType:  'subscription.upgraded',
            userId:     $subscription->user_id,
            entityType: 'App\Models\Subscription',
            entityId:   (string) $subscription->id,
            metadata:   [
                'plan'     => $subscription->plan,
                'cycle'    => $subscription->cycle,
                'ends_at'  => $subscription->ends_at?->toIso8601String(),
            ],
        );
    }
}

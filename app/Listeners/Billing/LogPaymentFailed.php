<?php

namespace App\Listeners\Billing;

use App\Events\PaymentFailed;
use App\Models\ActivityLog;

class LogPaymentFailed
{
    public function handle(PaymentFailed $event): void
    {
        ActivityLog::record(
            eventType:  'payment.failed',
            userId:     $event->order->user_id,
            entityType: 'App\Models\PaymentOrder',
            entityId:   $event->order->id,
            metadata:   [
                'plan'     => $event->order->plan,
                'cycle'    => $event->order->cycle,
                'amount'   => $event->order->amount,
                'provider' => $event->order->provider,
                'reason'   => $event->reason,
            ],
        );
    }
}

<?php

namespace App\Listeners\Billing;

use App\Events\PaymentSucceeded;
use App\Models\ActivityLog;

class LogPaymentSucceeded
{
    public function handle(PaymentSucceeded $event): void
    {
        ActivityLog::record(
            eventType:  'payment.succeeded',
            userId:     $event->order->user_id,
            entityType: 'App\Models\PaymentOrder',
            entityId:   $event->order->id,
            metadata:   [
                'plan'     => $event->order->plan,
                'cycle'    => $event->order->cycle,
                'amount'   => $event->order->amount,
                'currency' => $event->order->currency,
                'provider' => $event->order->provider,
            ],
        );
    }
}

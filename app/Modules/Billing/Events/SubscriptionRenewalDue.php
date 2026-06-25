<?php

namespace App\Modules\Billing\Events;

use App\Models\Subscription;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * يُطلَق عندما يحين موعد تجديد اشتراك (N أيام قبل الانتهاء)
 *
 * المستمعون المتوقعون:
 *   - إرسال تذكير للمستخدم
 *   - تسجيل موعد التجديد في قائمة الانتظار (مستقبلاً)
 */
class SubscriptionRenewalDue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Subscription $subscription,
        public readonly int          $daysUntilExpiry,
    ) {}
}

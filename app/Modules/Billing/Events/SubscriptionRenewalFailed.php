<?php

namespace App\Modules\Billing\Events;

use App\Models\Subscription;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * يُطلَق عند فشل محاولة التجديد التلقائي
 *
 * المستمعون المتوقعون:
 *   - إخطار المستخدم بفشل التجديد وتعليمات إعادة المحاولة
 *   - تنبيه Admin
 *   - جدولة إعادة المحاولة (إذا دعمت البوابة ذلك)
 */
class SubscriptionRenewalFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Subscription $subscription,
        public readonly string       $reason = '',
        public readonly int          $attemptNumber = 1,
    ) {}
}

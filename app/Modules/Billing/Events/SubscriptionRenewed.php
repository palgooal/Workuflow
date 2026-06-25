<?php

namespace App\Modules\Billing\Events;

use App\Models\Subscription;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * يُطلَق بعد إتمام تجديد ناجح (يدوي أو تلقائي)
 *
 * المستمعون المتوقعون:
 *   - إرسال إيصال التجديد للمستخدم
 *   - تحديث سجلات الإيرادات
 *   - إلغاء تذكيرات الانتهاء المعلّقة
 */
class SubscriptionRenewed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Subscription $subscription,
        public readonly string       $cycle,
        public readonly float        $amount,
        public readonly string       $currency = 'USD',
    ) {}
}

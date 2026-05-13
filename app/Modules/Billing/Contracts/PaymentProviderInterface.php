<?php

namespace App\Modules\Billing\Contracts;

use App\Models\User;

/**
 * واجهة موحّدة لأي مزود دفع (Stripe / Paddle / PayMob / Moyasar / ...)
 * عند إضافة مزود جديد: أنشئ class يُنفّذ هذه الواجهة وسجّله في AppServiceProvider.
 */
interface PaymentProviderInterface
{
    /**
     * إنشاء رابط Checkout لخطة معيّنة
     * يُعيد URL يُحوَّل إليه المستخدم مباشرة
     */
    public function createCheckoutUrl(User $user, string $plan): string;

    /**
     * إنشاء رابط بوابة إدارة الاشتراك (تعديل / إلغاء / فواتير)
     */
    public function createPortalUrl(User $user): string;

    /**
     * التحقق من صحة Webhook وإرجاع نوع الحدث والبيانات
     *
     * @return array{event: string, data: array}
     */
    public function parseWebhook(string $payload, string $signature): array;
}

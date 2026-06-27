<?php

namespace App\Providers;

use App\Modules\Billing\Events\SubscriptionActivated;
use App\Modules\Referral\Listeners\CreateReferralCommission;
use Illuminate\Support\Facades\Event;

use Illuminate\Support\ServiceProvider;

/**
 * ReferralServiceProvider — تسجيل Events/Listeners/Routes لموديول الإحالات
 *
 * النمط المتبع: مطابق لـ CRMServiceProvider و ActivityLogServiceProvider
 * التسجيل: Event::listen() في boot() — لا EventServiceProvider (غير موجود في المشروع)
 *
 * Listeners مسجَّلة:
 *   SubscriptionActivated → CreateReferralCommission
 *       (ShouldQueue + afterCommit=true + queue='referrals')
 *
 * Routes: مُسجَّلة في bootstrap/app.php → then() (نفس نمط CRMServiceProvider)
 */
class ReferralServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // لا bindings مخصصة في هذه المرحلة
        // ReferralService و FraudDetectionService يُحلّان تلقائياً عبر Container
    }

    public function boot(): void
    {
        $this->registerEvents();
        $this->registerRoutes();
    }

    protected function registerEvents(): void
    {
        // تفعيل اشتراك مدفوع (أول مرة) → إنشاء عمولة الإحالة
        // الـ Listener يتحقق من isFirstActivation داخلياً — لا عمولة على التجديد
        Event::listen(SubscriptionActivated::class, CreateReferralCommission::class);
    }

    protected function registerRoutes(): void
    {
        // Routes are registered via bootstrap/app.php → then()
        // (نفس نمط CRMServiceProvider)
    }
}

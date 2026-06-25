<?php

namespace App\Modules\Billing\Contracts;

use App\Models\Subscription;
use App\Models\User;

/**
 * RenewalServiceInterface — واجهة خدمة تجديد الاشتراكات
 *
 * توفر نقاط التمديد لثلاثة سيناريوهات:
 *   1. تجديد يدوي (Admin أو المستخدم)
 *   2. تجديد تلقائي — مستقبلاً
 *   3. تذكيرات التجديد
 *
 * قاعدة: لا تُنفَّذ هنا — ينفِّذها `ManualRenewalService` أو `AutoRenewalService`.
 */
interface RenewalServiceInterface
{
    /**
     * تجديد اشتراك المستخدم يدوياً
     *
     * يُستخدَم من Admin أو من المستخدم بعد إتمام دفعة جديدة.
     *
     * @param  User         $user      المستخدم المراد تجديد اشتراكه
     * @param  string       $cycle     'monthly' | 'annual'
     * @param  int          $periods   عدد الدورات (شهور أو سنوات)
     * @return Subscription            سجل الاشتراك المُجدَّد
     */
    public function renew(User $user, string $cycle = 'monthly', int $periods = 1): Subscription;

    /**
     * فحص مؤهلية الاشتراك للتجديد التلقائي
     *
     * يُستخدَم مستقبلاً عند ربط بوابة دفع تدعم الاشتراكات المتكررة.
     *
     * @param  Subscription $subscription  الاشتراك قيد الفحص
     * @return bool                         true إذا كان الاشتراك مؤهلاً للتجديد
     */
    public function isEligibleForAutoRenewal(Subscription $subscription): bool;

    /**
     * إرسال تذكير التجديد للمستخدم
     *
     * @param  Subscription $subscription  الاشتراك الذي سيُرسَل له التذكير
     * @param  int          $daysLeft      عدد الأيام المتبقية حتى الانتهاء
     * @return void
     */
    public function sendRenewalReminder(Subscription $subscription, int $daysLeft): void;

    /**
     * منح فترة سماح (Grace Period) للاشتراكات المنتهية
     *
     * يُؤجِّل تخفيض المستخدم عدد أيام محدداً بعد انتهاء الاشتراك.
     *
     * @param  Subscription $subscription  الاشتراك المنتهي
     * @param  int          $graceDays     أيام السماح (default: 3)
     * @return Subscription                الاشتراك بعد منح فترة السماح
     */
    public function applyGracePeriod(Subscription $subscription, int $graceDays = 3): Subscription;
}

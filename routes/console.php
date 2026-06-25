<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ==================== Scheduled Tasks ====================

// إرسال تنبيهات الديون كل صباح الساعة 8
Schedule::command('debts:send-alerts')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/debt-alerts.log'));

// حذف الإشعارات القديمة أسبوعياً (الأحد منتصف الليل)
Schedule::call(function () {
    \App\Models\User::chunk(100, function ($users) {
        $service = app(\App\Modules\Notifications\Services\NotificationService::class);
        foreach ($users as $user) {
            $service->deleteOld($user);
        }
    });
})->weekly()->sundays()->at('00:00')->name('clean-old-notifications')->withoutOverlapping();

// معالجة الالتزامات المتكررة المستحقة يومياً الساعة 1 صباحاً
Schedule::command('recurring:process')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/recurring.log'));

// تنظيف الجلسات المنتهية الصلاحية يومياً
Schedule::command('session:gc')->daily()->runInBackground();

// ==================== CRM — Sprint 5 ====================

// إعادة حساب مؤشرات صحة العملاء + تطبيق الوسوم الذكية يومياً الساعة 02:00
Schedule::command('crm:recalculate-health-scores --apply-tags')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/crm-health-scores.log'));

// مطابقة إجماليات العملاء يومياً الساعة 03:00
Schedule::command('crm:reconcile-aggregates')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/crm-reconcile.log'));

// تحديث أعداد الشرائح الديناميكية يومياً الساعة 03:30
Schedule::command('crm:refresh-segments')
    ->dailyAt('03:30')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/crm-segments.log'));

// إحماء الـ Cache كل ساعة (اختياري في الإنتاج)
// Schedule::command('cache:prune-stale-tags')->hourly();

// ==================== CRM — Sprint 6: اكتشاف العملاء الخاملين ====================

// اكتشاف العملاء الخاملين وتشغيل قواعد الأتمتة يومياً الساعة 04:00
// يعمل بعد recalculate-health-scores (02:00) وreconcile-aggregates (03:00)
// حتى تكون البيانات محدَّثة قبل تقييم الشروط
Schedule::command('crm:detect-inactive')
    ->dailyAt('04:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/crm-detect-inactive.log'));

// ==================== CRM — Sprint 6: تذكيرات المتابعات ====================

// إرسال تذكيرات المتابعات كل 30 دقيقة (نافذة الساعة الأخيرة في FollowUpService)
Schedule::command('crm:send-follow-up-reminders')
    ->everyThirtyMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/crm-follow-up-reminders.log'));

// ==================== Billing — انتهاء الاشتراكات ====================

// إنهاء الاشتراكات المنتهية ومزامنة خطط المستخدمين يومياً الساعة 00:10
Schedule::command('subscriptions:expire')
    ->dailyAt('00:10')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/subscriptions-expire.log'));

// تذكيرات الاشتراكات التي ستنتهي خلال 7 أيام — يومياً الساعة 09:30
Schedule::command('subscriptions:send-expiry-reminders --days=7')
    ->dailyAt('09:30')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/subscriptions-expiry-reminders.log'));

// ==================== Invoices — تذكيرات الفواتير ====================

// تذكيرات الفواتير المستحقة والمتأخرة كل صباح الساعة 09:00
Schedule::command('invoices:send-reminders')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/invoice-reminders.log'));

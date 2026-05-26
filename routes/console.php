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

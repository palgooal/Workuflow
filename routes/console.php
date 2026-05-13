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

// تنظيف الجلسات المنتهية الصلاحية يومياً
Schedule::command('session:gc')->daily()->runInBackground();

// إحماء الـ Cache كل ساعة (اختياري في الإنتاج)
// Schedule::command('cache:prune-stale-tags')->hourly();

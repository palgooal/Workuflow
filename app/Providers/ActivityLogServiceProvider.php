<?php

namespace App\Providers;

use App\Events\PaymentFailed;
use App\Events\PaymentSucceeded;
use App\Listeners\Auth\LogUserLogin;
use App\Listeners\Auth\LogUserLogout;
use App\Listeners\Billing\LogNotificationFailed;
use App\Listeners\Billing\LogNotificationSent;
use App\Listeners\Billing\LogPaymentFailed;
use App\Listeners\Billing\LogPaymentSucceeded;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class ActivityLogServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // ── Auth ───────────────────────────────────────────────────────────
        Event::listen(Login::class,  LogUserLogin::class);
        Event::listen(Logout::class, LogUserLogout::class);

        // ── Payments ───────────────────────────────────────────────────────
        Event::listen(PaymentSucceeded::class, LogPaymentSucceeded::class);
        Event::listen(PaymentFailed::class,    LogPaymentFailed::class);

        // ── Email Delivery Logs ────────────────────────────────────────────
        Event::listen(NotificationSent::class,   LogNotificationSent::class);
        Event::listen(NotificationFailed::class, LogNotificationFailed::class);
    }
}

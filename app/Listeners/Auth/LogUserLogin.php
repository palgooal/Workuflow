<?php

namespace App\Listeners\Auth;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Request;

class LogUserLogin
{
    public function handle(Login $event): void
    {
        // تحديث بيانات آخر تسجيل دخول
        $event->user->update([
            'last_login_at' => now(),
            'last_login_ip' => Request::ip(),
        ]);

        // تسجيل في سجل النشاط
        ActivityLog::record(
            eventType:  'auth.login',
            userId:     $event->user->id,
            entityType: 'App\Models\User',
            entityId:   (string) $event->user->id,
            metadata:   ['guard' => $event->guard],
        );
    }
}

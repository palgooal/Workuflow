<?php

namespace App\Listeners\Auth;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Login;

class LogUserLogin
{
    public function handle(Login $event): void
    {
        ActivityLog::record(
            eventType:  'auth.login',
            userId:     $event->user->id,
            entityType: 'App\Models\User',
            entityId:   (string) $event->user->id,
            metadata:   ['guard' => $event->guard],
        );
    }
}

<?php

namespace App\Listeners\Auth;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Logout;

class LogUserLogout
{
    public function handle(Logout $event): void
    {
        ActivityLog::record(
            eventType:  'auth.logout',
            userId:     $event->user?->id,
            entityType: 'App\Models\User',
            entityId:   (string) ($event->user?->id ?? ''),
        );
    }
}

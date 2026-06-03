<?php

namespace App\Providers;

use App\Models\Client;
use App\Modules\CRM\Events\ClientCreated;
use App\Modules\CRM\Events\ClientUpdated;
use App\Modules\CRM\Events\ClientArchived;
use App\Modules\CRM\Events\ClientDeleted;
use App\Modules\CRM\Events\ClientTagAssigned;
use App\Modules\CRM\Events\ClientTagRemoved;
use App\Modules\CRM\Events\FollowUpCreated;
use App\Modules\CRM\Events\FollowUpCompleted;
use App\Modules\CRM\Listeners\LogClientCreatedActivity;
use App\Modules\CRM\Listeners\LogClientUpdatedActivity;
use App\Modules\CRM\Listeners\LogClientTagActivity;
use App\Modules\CRM\Listeners\LogFollowUpCreatedActivity;
use App\Modules\CRM\Listeners\LogFollowUpCompletedActivity;
use App\Modules\CRM\Models\ClientFollowUp;
use App\Modules\CRM\Models\ClientImportLog;
use App\Modules\CRM\Models\ClientPortalToken;
use App\Modules\CRM\Models\ClientTag;
use App\Modules\CRM\Models\SavedSegment;
use App\Modules\CRM\Policies\ClientFollowUpPolicy;
use App\Modules\CRM\Policies\ClientImportLogPolicy;
use App\Modules\CRM\Policies\ClientPolicy;
use App\Modules\CRM\Policies\ClientPortalTokenPolicy;
use App\Modules\CRM\Policies\ClientTagPolicy;
use App\Modules\CRM\Policies\SavedSegmentPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CRMServiceProvider extends ServiceProvider
{
    /**
     * Register CRM bindings.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(config_path('crm.php'), 'crm');
    }

    /**
     * Bootstrap CRM services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        $this->registerEvents();
        $this->registerRoutes();
    }

    /**
     * تسجيل صلاحيات CRM.
     */
    protected function registerPolicies(): void
    {
        Gate::policy(Client::class,            ClientPolicy::class);
        Gate::policy(ClientTag::class,         ClientTagPolicy::class);
        Gate::policy(ClientFollowUp::class,    ClientFollowUpPolicy::class);
        Gate::policy(ClientImportLog::class,   ClientImportLogPolicy::class);
        Gate::policy(SavedSegment::class,      SavedSegmentPolicy::class);
        Gate::policy(ClientPortalToken::class, ClientPortalTokenPolicy::class);
    }

    /**
     * تسجيل Events ↔ Listeners (C-01: afterCommit=true في كل Listener)
     */
    protected function registerEvents(): void
    {
        // إنشاء عميل
        Event::listen(ClientCreated::class, LogClientCreatedActivity::class);

        // تعديل عميل
        Event::listen(ClientUpdated::class, LogClientUpdatedActivity::class);

        // الوسوم
        Event::listen(
            ClientTagAssigned::class,
            [LogClientTagActivity::class, 'handleAssigned']
        );
        Event::listen(
            ClientTagRemoved::class,
            [LogClientTagActivity::class, 'handleRemoved']
        );

        // الأرشفة والحذف — بدون Listener حالياً (يُضاف في Sprint 6 مع Automation)
        // Event::listen(ClientArchived::class, ...);
        // Event::listen(ClientDeleted::class,  ...);

        // GAP-06 Fix: Follow-up Events — C-01 compliant ($afterCommit = true)
        Event::listen(FollowUpCreated::class,   LogFollowUpCreatedActivity::class);
        Event::listen(FollowUpCompleted::class, LogFollowUpCompletedActivity::class);
    }

    /**
     * تسجيل مسارات CRM — تمت إضافتها في bootstrap/app.php عبر then()
     * لضمان الأولوية على مسارات web.php
     */
    protected function registerRoutes(): void
    {
        // Routes are registered via bootstrap/app.php → then()
    }
}

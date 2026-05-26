<?php

namespace App\Modules\CRM\Policies;

use App\Models\User;
use App\Modules\CRM\Models\ClientImportLog;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * ClientImportLogPolicy — صلاحيات سجلات الاستيراد
 */
class ClientImportLogPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        // Pro+ فقط
        $limits = config("crm.limits.{$user->currentPlan()->value}", config('crm.limits.free'));

        return (bool) ($limits['can_import'] ?? false);
    }

    public function view(User $user, ClientImportLog $log): bool
    {
        return $user->id === $log->user_id;
    }
}

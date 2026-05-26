<?php

namespace App\Modules\CRM\Policies;

use App\Models\User;
use App\Modules\CRM\Models\ClientPortalToken;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * ClientPortalTokenPolicy — صلاحيات توكنات البوابة
 * Business فقط.
 */
class ClientPortalTokenPolicy
{
    use HandlesAuthorization;

    private function canUsePortal(User $user): bool
    {
        $limits = config("crm.limits.{$user->currentPlan()->value}", config('crm.limits.free'));

        return (bool) ($limits['can_portal'] ?? false);
    }

    public function viewAny(User $user): bool
    {
        return $this->canUsePortal($user);
    }

    public function view(User $user, ClientPortalToken $token): bool
    {
        return $this->canUsePortal($user)
            && $user->id === $token->client->user_id;
    }

    public function create(User $user): bool
    {
        return $this->canUsePortal($user);
    }

    public function delete(User $user, ClientPortalToken $token): bool
    {
        return $this->canUsePortal($user)
            && $user->id === $token->client->user_id;
    }
}

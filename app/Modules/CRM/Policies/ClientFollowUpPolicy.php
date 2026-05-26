<?php

namespace App\Modules\CRM\Policies;

use App\Models\User;
use App\Modules\CRM\Models\ClientFollowUp;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * ClientFollowUpPolicy — صلاحيات المتابعات
 * المالك هو صاحب العميل المرتبط بالمتابعة.
 */
class ClientFollowUpPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ClientFollowUp $followUp): bool
    {
        return $user->id === $followUp->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ClientFollowUp $followUp): bool
    {
        return $user->id === $followUp->user_id;
    }

    public function delete(User $user, ClientFollowUp $followUp): bool
    {
        return $user->id === $followUp->user_id;
    }
}

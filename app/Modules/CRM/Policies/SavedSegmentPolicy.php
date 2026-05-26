<?php

namespace App\Modules\CRM\Policies;

use App\Models\User;
use App\Modules\CRM\Models\SavedSegment;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * SavedSegmentPolicy — صلاحيات الشرائح المحفوظة
 * Pro+ فقط.
 */
class SavedSegmentPolicy
{
    use HandlesAuthorization;

    private function canUseSegments(User $user): bool
    {
        $limits = config("crm.limits.{$user->currentPlan()->value}", config('crm.limits.free'));

        return (bool) ($limits['can_segments'] ?? false);
    }

    public function viewAny(User $user): bool
    {
        return $this->canUseSegments($user);
    }

    public function view(User $user, SavedSegment $segment): bool
    {
        return $this->canUseSegments($user) && $user->id === $segment->user_id;
    }

    public function create(User $user): bool
    {
        return $this->canUseSegments($user);
    }

    public function update(User $user, SavedSegment $segment): bool
    {
        return $this->canUseSegments($user) && $user->id === $segment->user_id;
    }

    public function delete(User $user, SavedSegment $segment): bool
    {
        return $this->canUseSegments($user) && $user->id === $segment->user_id;
    }
}

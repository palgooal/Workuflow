<?php

namespace App\Modules\CRM\Policies;

use App\Models\User;
use App\Modules\CRM\Enums\TagType;
use App\Modules\CRM\Models\ClientTag;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * ClientTagPolicy — صلاحيات الوسوم
 *
 * - وسوم النظام (type=system): عرض فقط — لا حذف ولا تعديل
 * - وسوم المستخدم (type=custom): المالك فقط
 */
class ClientTagPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ClientTag $tag): bool
    {
        return $tag->isSystem() || $user->id === $tag->user_id;
    }

    public function create(User $user): bool
    {
        $limits = config("crm.limits.{$user->currentPlan()->value}", config('crm.limits.free'));
        $max    = $limits['max_tags'] ?? 3;

        if ($max === -1) {
            return true;
        }

        $current = ClientTag::where('user_id', $user->id)->count();

        return $current < $max;
    }

    public function update(User $user, ClientTag $tag): bool
    {
        // وسوم النظام غير قابلة للتعديل
        if ($tag->isSystem()) {
            return false;
        }

        return $user->id === $tag->user_id;
    }

    public function delete(User $user, ClientTag $tag): bool
    {
        // وسوم النظام غير قابلة للحذف
        if ($tag->isSystem()) {
            return false;
        }

        return $user->id === $tag->user_id;
    }
}

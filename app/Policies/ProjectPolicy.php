<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * عمليات تتطلب صاحب المشروع فقط
     */
    public function view(User $user, Project $project): bool
    {
        return $user->id === $project->user_id;
    }

    public function update(User $user, Project $project): bool
    {
        return $user->id === $project->user_id;
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->id === $project->user_id;
    }

    /**
     * إنشاء مشروع — يتحقق من حدود الخطة
     */
    public function create(User $user): bool
    {
        return $user->canCreateMoreProjects();
    }
}

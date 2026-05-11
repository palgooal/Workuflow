<?php

namespace App\Modules\Projects\Actions;

use App\Models\Project;

class DeleteProjectAction
{
    public function execute(Project $project): void
    {
        // SoftDelete — يحتفظ بالمعاملات المرتبطة
        $project->delete();
    }
}

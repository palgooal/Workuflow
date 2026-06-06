<?php

namespace App\Modules\Projects\Actions;

use App\Models\Project;
use App\Modules\Projects\DTOs\ProjectData;

class UpdateProjectAction
{
    public function execute(Project $project, ProjectData $data): Project
    {
        $project->update([
            'name'        => $data->name,
            'type'        => $data->type,
            'currency'    => $data->currency,
            'color'          => $data->color,
            'description'    => $data->description,
            'status'         => $data->status,
            'client_id'      => $data->client_id,
            'contract_value' => $data->contract_value,
        ]);

        return $project->fresh();
    }
}

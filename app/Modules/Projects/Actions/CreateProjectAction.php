<?php

namespace App\Modules\Projects\Actions;

use App\Models\Project;
use App\Modules\Projects\DTOs\ProjectData;

class CreateProjectAction
{
    public function execute(ProjectData $data): Project
    {
        return Project::create([
            'name'        => $data->name,
            'type'        => $data->type,
            'currency'    => $data->currency,
            'color'          => $data->color,
            'description'    => $data->description,
            'status'         => $data->status,
            'client_id'      => $data->client_id,
            'contract_value' => $data->contract_value,
        ]);
    }
}

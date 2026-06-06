<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectServiceMember extends Model
{
    protected $table = 'project_service_members';

    protected $fillable = [
        'project_service_id',
        'team_member_id',
        'team_cost',
        'team_cost_paid',
    ];

    protected function casts(): array
    {
        return [
            'team_cost'      => 'decimal:2',
            'team_cost_paid' => 'boolean',
        ];
    }

    // ==================== Relations ====================

    public function projectService(): BelongsTo
    {
        return $this->belongsTo(ProjectServicePivot::class, 'project_service_id');
    }

    public function teamMember(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class, 'team_member_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectServicePivot extends Pivot
{
    protected $table = 'project_service';

    // الـ pivot له id مستقل
    public $incrementing = true;

    protected $fillable = [
        'project_id',
        'service_id',
        'client_id',
        'amount',
        'type',
        'notes',
        'target_margin_pct',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    // ==================== Relations ====================

    public function members(): HasMany
    {
        return $this->hasMany(ProjectServiceMember::class, 'project_service_id');
    }
}

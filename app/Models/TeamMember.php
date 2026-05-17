<?php

namespace App\Models;

use App\Support\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeamMember extends Model
{
    use HasFactory, HasUlids, SoftDeletes, BelongsToUser;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'specialty',
        'phone',
        'email',
        'default_rate',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type'         => 'string',
            'is_active'    => 'boolean',
            'default_rate' => 'decimal:2',
        ];
    }

    // ==================== Scopes ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ==================== Helpers ====================

    public function typeLabel(): string
    {
        return match($this->type) {
            'employee'   => 'موظف',
            'freelancer' => 'فريلانسر',
            default      => $this->type,
        };
    }

    public function typeBadgeColor(): string
    {
        return match($this->type) {
            'employee'   => 'blue',
            'freelancer' => 'purple',
            default      => 'gray',
        };
    }
}

<?php

namespace App\Modules\CRM\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientFieldDefinition extends Model
{
    protected $table = 'client_field_definitions';

    protected $fillable = [
        'user_id',
        'name',
        'key',
        'type',
        'is_required',
        'options',
        'display_order',
        'plan_required',
    ];

    protected function casts(): array
    {
        return [
            'is_required'   => 'boolean',
            'options'       => 'array',
            'display_order' => 'integer',
        ];
    }

    // ==================== Relations ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(ClientFieldValue::class, 'field_definition_id');
    }

    // ==================== Scopes ====================

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId)->ordered();
    }

    // ==================== Helpers ====================

    public function isAvailableForPlan(string $plan): bool
    {
        if (is_null($this->plan_required)) {
            return true;
        }

        $hierarchy = ['free' => 0, 'pro' => 1, 'business' => 2];

        return ($hierarchy[$plan] ?? 0) >= ($hierarchy[$this->plan_required] ?? 99);
    }

    /** هل هذا الحقل من نوع قائمة خيارات؟ */
    public function isSelect(): bool
    {
        return $this->type === 'select';
    }
}

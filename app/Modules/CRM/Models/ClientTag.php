<?php

namespace App\Modules\CRM\Models;

use App\Models\Client;
use App\Models\User;
use App\Modules\CRM\Enums\TagType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ClientTag extends Model
{
    protected $table = 'client_tags';

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'color',
        'type',
        'icon',
        'is_active',
        'priority',
    ];

    protected function casts(): array
    {
        return [
            'type'      => TagType::class,
            'is_active' => 'boolean',
            'priority'  => 'integer',
        ];
    }

    // ==================== Relations ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(
            Client::class,
            'client_tag_assignments',
            'tag_id',
            'client_id'
        )->withPivot(['assigned_at', 'assigned_by']);
    }

    // ==================== Scopes ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSystem($query)
    {
        return $query->where('type', TagType::System->value);
    }

    public function scopeCustom($query)
    {
        return $query->where('type', TagType::Custom->value);
    }

    /** وسوم المستخدم: custom المملوكة له + system المشتركة */
    public static function scopeForUser($query, int $userId)
    {
        return $query->where(fn ($q) =>
            $q->where('user_id', $userId)
              ->orWhereNull('user_id')
        )->where('is_active', true)
         ->orderBy('priority');
    }

    // ==================== Helpers ====================

    public function isSystem(): bool
    {
        return $this->type === TagType::System;
    }

    public function isDeletable(): bool
    {
        return $this->type->isDeletable();
    }

    /** عدد العملاء المرتبطين بهذا الوسم */
    public function clientCount(): int
    {
        return $this->clients()->count();
    }
}

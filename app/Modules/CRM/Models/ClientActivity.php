<?php

namespace App\Modules\CRM\Models;

use App\Models\Client;
use App\Models\User;
use App\Modules\CRM\Enums\ActivityType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ClientActivity — سجل نشاط العميل
 *
 * ⚠️ لا تُسجَّل مباشرةً من Observers — استخدم Listeners مع $afterCommit = true (C-01 Fix)
 */
class ClientActivity extends Model
{
    protected $table = 'client_activities';

    // لا SoftDeletes — سجل النشاط دائم
    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'user_id',
        'type',
        'description',
        'metadata',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'type'        => ActivityType::class,
            'metadata'    => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $activity) {
            if (empty($activity->occurred_at)) {
                $activity->occurred_at = now();
            }
        });
    }

    // ==================== Relations ====================

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ==================== Scopes ====================

    public function scopeOfType($query, ActivityType $type)
    {
        return $query->where('type', $type->value);
    }

    public function scopeHighPriority($query)
    {
        $types = array_filter(
            ActivityType::cases(),
            fn ($t) => $t->isHighPriority()
        );

        return $query->whereIn('type', array_column($types, 'value'));
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('occurred_at', '>=', now()->subDays($days));
    }

    // ==================== Helpers ====================

    public function isHighPriority(): bool
    {
        return $this->type?->isHighPriority() ?? false;
    }
}

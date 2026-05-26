<?php

namespace App\Modules\CRM\Models;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * ClientTagAssignment — Pivot Model لربط الوسوم بالعملاء
 *
 * يُستخدم عند الحاجة للوصول المباشر لسجل الربط
 * (مثلاً: لمعرفة من عيّن الوسم ومتى).
 */
class ClientTagAssignment extends Pivot
{
    protected $table = 'client_tag_assignments';

    public $incrementing = true;

    protected $fillable = [
        'client_id',
        'tag_id',
        'assigned_by',
        'assigned_at',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $pivot) {
            if (empty($pivot->assigned_at)) {
                $pivot->assigned_at = now();
            }
        });
    }

    // ==================== Relations ====================

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(ClientTag::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}

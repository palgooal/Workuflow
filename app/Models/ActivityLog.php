<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class ActivityLog extends Model
{
    public $timestamps = false; // created_at only, set via useCurrent()

    protected $fillable = [
        'user_id',
        'event_type',
        'entity_type',
        'entity_id',
        'metadata',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata'   => 'array',
            'created_at' => 'datetime',
        ];
    }

    // ──────────────────────────────────────────
    // Relations
    // ──────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ──────────────────────────────────────────
    // Static Factory
    // ──────────────────────────────────────────

    /**
     * تسجيل حدث بسيط
     */
    public static function record(
        string  $eventType,
        ?int    $userId    = null,
        ?string $entityType = null,
        ?string $entityId   = null,
        array   $metadata  = [],
    ): self {
        $request = app()->runningInConsole() ? null : request();

        return static::create([
            'user_id'     => $userId,
            'event_type'  => $eventType,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'metadata'    => $metadata ?: null,
            'ip_address'  => $request?->ip(),
            'user_agent'  => $request?->userAgent(),
        ]);
    }

    /**
     * تسجيل حدث للمستخدم المسجّل حالياً
     */
    public static function recordFor(
        string  $eventType,
        ?string $entityType = null,
        ?string $entityId   = null,
        array   $metadata  = [],
    ): self {
        return static::record(
            eventType:  $eventType,
            userId:     auth()->id(),
            entityType: $entityType,
            entityId:   $entityId,
            metadata:   $metadata,
        );
    }
}

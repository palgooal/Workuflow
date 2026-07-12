<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DataRetentionLedgerEntry
 *
 * سجل تدقيق لتتبّع تاريخ إغلاق حساب/معالجة طلب حذف بيانات، وتاريخ استحقاق
 * التطهير النهائي وفق سياسة الاحتفاظ المعتمَدة (سنة واحدة افتراضياً).
 *
 * راجع: docs/legal/Privacy-Policy.md §8، docs/legal/Data-Deletion.md §3،
 * docs/operations/DATA-DELETION-SOP.md.
 *
 * هذا الموديل لتتبّع/تدقيق فقط. لا يوجد حذف تلقائي مبني عليه حالياً.
 */
class DataRetentionLedgerEntry extends Model
{
    protected $table = 'data_retention_ledger';

    protected $fillable = [
        'user_id',
        'user_email_snapshot',
        'closed_at',
        'purge_due_at',
        'legal_hold',
        'legal_hold_reason',
        'status',
        'purged_at',
        'triggered_by_admin_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'closed_at'    => 'datetime',
            'purge_due_at' => 'datetime',
            'purged_at'    => 'datetime',
            'legal_hold'   => 'boolean',
        ];
    }

    /**
     * تسجيل إغلاق حساب/معالجة طلب حذف بيانات، مع احتساب تاريخ استحقاق
     * التطهير تلقائياً (سنة واحدة من الآن) ما لم يُحدَّد خلاف ذلك.
     */
    public static function recordClosure(
        User $user,
        ?int $triggeredByAdminId = null,
        ?string $notes = null,
    ): self {
        return static::create([
            'user_id'               => $user->id,
            'user_email_snapshot'   => $user->email,
            'closed_at'             => now(),
            'purge_due_at'          => now()->addYear(),
            'status'                => 'pending',
            'legal_hold'            => false,
            'triggered_by_admin_id' => $triggeredByAdminId,
            'notes'                 => $notes,
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeDueForPurge($query)
    {
        return $query->where('status', 'pending')
            ->where('legal_hold', false)
            ->where('purge_due_at', '<=', now());
    }
}

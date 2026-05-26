<?php

namespace App\Modules\CRM\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * AutomationRule — قاعدة الأتمتة
 *
 * Sprint 6 — S6.1
 *
 * بنية conditions:
 * {
 *   "operator": "AND",
 *   "conditions": [
 *     {"field": "health_score", "op": "less_than", "value": 40},
 *     {"field": "status", "op": "equals", "value": "active"}
 *   ]
 * }
 *
 * بنية actions:
 * [
 *   {"type": "assign_tag",       "params": {"tag_slug": "inactive"}},
 *   {"type": "create_follow_up", "params": {"message": "تواصل مع العميل", "days_from_now": 3}},
 *   {"type": "send_notification", "params": {"message": "تحذير: عميل غير نشط"}},
 *   {"type": "update_status",    "params": {"status": "inactive"}},
 *   {"type": "log_note",         "params": {"note": "تم تطبيق قاعدة الأتمتة"}}
 * ]
 */
class AutomationRule extends Model
{
    use SoftDeletes;

    protected $table = 'automation_rules';

    protected $fillable = [
        'user_id',
        'name',
        'trigger',
        'conditions',
        'actions',
        'is_active',
        'priority',
        'run_count',
        'last_run_at',
    ];

    protected function casts(): array
    {
        return [
            'conditions'  => 'array',
            'actions'     => 'array',
            'is_active'   => 'boolean',
            'priority'    => 'integer',
            'run_count'   => 'integer',
            'last_run_at' => 'datetime',
        ];
    }

    // ==================== Triggers ====================

    /**
     * جميع الـ Triggers المدعومة
     */
    public static function triggers(): array
    {
        return [
            'client_created'      => 'إنشاء عميل جديد',
            'status_changed'      => 'تغيير حالة العميل',
            'tag_assigned'        => 'إضافة وسم',
            'health_score_below'  => 'انخفاض مؤشر الصحة',
            'follow_up_overdue'   => 'تأخر متابعة مستحقة',
            'days_since_contact'  => 'مرور أيام بدون تواصل',
            'invoice_paid'        => 'سداد فاتورة',
            'invoice_overdue'     => 'تأخر فاتورة',
        ];
    }

    // ==================== Relations ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==================== Scopes ====================

    public static function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function scopeForTrigger($query, string $trigger)
    {
        return $query->where('trigger', $trigger);
    }

    public static function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId)
                     ->orderBy('priority')
                     ->orderBy('created_at');
    }

    // ==================== Helpers ====================

    public function recordRun(): void
    {
        $this->increment('run_count');
        $this->update(['last_run_at' => now()]);
    }

    public function triggerLabel(): string
    {
        return self::triggers()[$this->trigger] ?? $this->trigger;
    }
}

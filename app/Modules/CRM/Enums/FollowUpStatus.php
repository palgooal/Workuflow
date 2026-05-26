<?php

namespace App\Modules\CRM\Enums;

use Carbon\Carbon;

/**
 * FollowUpStatus — حالة المتابعة
 */
enum FollowUpStatus: string
{
    case Pending   = 'pending';
    case Completed = 'completed';
    case Overdue   = 'overdue';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Pending   => 'معلقة',
            self::Completed => 'مكتملة',
            self::Overdue   => 'متأخرة',
            self::Cancelled => 'ملغاة',
        };
    }

    public function badgeColor(): string
    {
        return match($this) {
            self::Pending   => '#F59E0B',   // amber
            self::Completed => '#10B981',   // green
            self::Overdue   => '#EF4444',   // red
            self::Cancelled => '#6B7280',   // gray
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Pending   => 'bg-amber-100 text-amber-800',
            self::Completed => 'bg-green-100 text-green-800',
            self::Overdue   => 'bg-red-100 text-red-800',
            self::Cancelled => 'bg-gray-100 text-gray-600',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Pending   => '⏳',
            self::Completed => '✅',
            self::Overdue   => '🚨',
            self::Cancelled => '❌',
        };
    }

    /** هل يمكن الانتقال لهذه الحالة؟ */
    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled]);
    }

    /**
     * تحديد الحالة الفعلية بناءً على تاريخ الاستحقاق.
     * المتابعة "معلقة" لكن تاريخها مضى → "متأخرة".
     */
    public static function resolveActual(self $stored, Carbon $dueAt): self
    {
        if ($stored === self::Pending && $dueAt->isPast()) {
            return self::Overdue;
        }

        return $stored;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

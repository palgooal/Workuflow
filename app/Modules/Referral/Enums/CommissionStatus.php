<?php

namespace App\Modules\Referral\Enums;

/**
 * CommissionStatus — حالة العمولة
 *
 * مخزَّن كـ VARCHAR(20) في قاعدة البيانات (لا MySQL ENUM).
 * CHECK constraint: CHECK (status IN ('pending','approved','paid','rejected','cancelled'))
 *
 * دورة الحياة:
 *   pending → approved → paid        (المسار الطبيعي)
 *   pending → rejected               (احتيال أو خطأ)
 *   pending/approved → cancelled     (الاشتراك استُرد)
 *
 * ملاحظة: total_converted يشمل pending+approved+paid لضمان الترقية الفورية للمستوى.
 * أما total_earned فيشمل approved+paid فقط (راجع §15).
 */
enum CommissionStatus: string
{
    case Pending   = 'pending';    // قيد الانتظار (7 أيام مراجعة Fraud)
    case Approved  = 'approved';   // معتمدة، تنتظر الدفع
    case Paid      = 'paid';       // مدفوعة
    case Rejected  = 'rejected';   // مرفوضة (احتيال أو خطأ)
    case Cancelled = 'cancelled';  // ملغاة (الاشتراك استُرد)

    public function label(): string
    {
        return match($this) {
            self::Pending   => 'قيد المراجعة',
            self::Approved  => 'معتمدة',
            self::Paid      => 'مدفوعة',
            self::Rejected  => 'مرفوضة',
            self::Cancelled => 'ملغاة',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending   => 'warning',
            self::Approved  => 'info',
            self::Paid      => 'success',
            self::Rejected  => 'danger',
            self::Cancelled => 'gray',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Pending   => 'bg-yellow-100 text-yellow-800',
            self::Approved  => 'bg-blue-100 text-blue-800',
            self::Paid      => 'bg-green-100 text-green-800',
            self::Rejected  => 'bg-red-100 text-red-800',
            self::Cancelled => 'bg-gray-100 text-gray-500',
        };
    }

    /** هل تُحتسب ضمن total_converted (للترقية الفورية للمستوى)؟ */
    public function countsForTier(): bool
    {
        return in_array($this, [self::Pending, self::Approved, self::Paid]);
    }

    /** هل تُحتسب ضمن total_earned (للرصيد المالي)؟ */
    public function countsForEarnings(): bool
    {
        return in_array($this, [self::Approved, self::Paid]);
    }

    /** هل هي حالة نهائية (لا يمكن التغيير منها)؟ */
    public function isFinal(): bool
    {
        return in_array($this, [self::Paid, self::Rejected, self::Cancelled]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

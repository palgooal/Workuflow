<?php

namespace App\Modules\Referral\Enums;

/**
 * PayoutStatus — حالة طلب صرف العمولة
 *
 * مخزَّن كـ VARCHAR(20) في قاعدة البيانات (لا MySQL ENUM).
 * CHECK constraint: CHECK (status IN ('requested','processing','paid','rejected'))
 *
 * دورة الحياة:
 *   requested → processing → paid    (المسار الطبيعي)
 *   requested/processing → rejected  (رفض الأدمن)
 */
enum PayoutStatus: string
{
    case Requested  = 'requested';   // طلب جديد من المسوّق
    case Processing = 'processing';  // قيد المعالجة من الأدمن
    case Paid       = 'paid';        // تم الصرف
    case Rejected   = 'rejected';    // مرفوض

    public function label(): string
    {
        return match($this) {
            self::Requested  => 'طلب جديد',
            self::Processing => 'قيد المعالجة',
            self::Paid       => 'تم الصرف',
            self::Rejected   => 'مرفوض',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Requested  => 'warning',
            self::Processing => 'info',
            self::Paid       => 'success',
            self::Rejected   => 'danger',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Requested  => 'bg-yellow-100 text-yellow-800',
            self::Processing => 'bg-blue-100 text-blue-800',
            self::Paid       => 'bg-green-100 text-green-800',
            self::Rejected   => 'bg-red-100 text-red-800',
        };
    }

    /** هل هي حالة نهائية؟ */
    public function isFinal(): bool
    {
        return in_array($this, [self::Paid, self::Rejected]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

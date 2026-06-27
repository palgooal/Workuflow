<?php

namespace App\Modules\Referral\Enums;

/**
 * AffiliateStatus — حالة حساب المسوّق
 *
 * مخزَّن كـ VARCHAR(20) في قاعدة البيانات (لا MySQL ENUM).
 * CHECK constraint: CHECK (status IN ('pending','active','suspended'))
 */
enum AffiliateStatus: string
{
    case Pending   = 'pending';    // بانتظار موافقة الأدمن
    case Active    = 'active';     // مفعَّل ويستطيع كسب عمولات
    case Suspended = 'suspended';  // موقوف (احتيال أو مخالفة)

    public function label(): string
    {
        return match($this) {
            self::Pending   => 'بانتظار الموافقة',
            self::Active    => 'نشط',
            self::Suspended => 'موقوف',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending   => 'warning',
            self::Active    => 'success',
            self::Suspended => 'danger',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Pending   => 'bg-yellow-100 text-yellow-800',
            self::Active    => 'bg-green-100 text-green-800',
            self::Suspended => 'bg-red-100 text-red-800',
        };
    }

    /** هل المسوّق يستطيع كسب عمولات جديدة؟ */
    public function canEarn(): bool
    {
        return $this === self::Active;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

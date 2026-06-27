<?php

namespace App\Modules\Referral\Enums;

/**
 * AffiliateTier — مستوى المسوّق وعمولته
 *
 * مخزَّن كـ VARCHAR(20) في قاعدة البيانات (لا MySQL ENUM).
 * CHECK constraint: CHECK (tier IN ('standard','silver','gold','platinum'))
 *
 * الترقية التلقائية عبر UpgradeAffiliateTierAction بعد كل اشتراك ناجح.
 * الحساب يعتمد على total_converted (يشمل pending+approved+paid) راجع §7 و§15.
 *
 * | المستوى  | الحد الأدنى لـ total_converted | نسبة العمولة |
 * |---------|-------------------------------|--------------|
 * | Standard | 0                             | 30%          |
 * | Silver   | 10                            | 35%          |
 * | Gold     | 30                            | 40%          |
 * | Platinum | 100                           | 45%          |
 */
enum AffiliateTier: string
{
    case Standard = 'standard';
    case Silver   = 'silver';
    case Gold     = 'gold';
    case Platinum = 'platinum';

    /** نسبة العمولة الافتراضية لكل مستوى */
    public function defaultRate(): float
    {
        return match($this) {
            self::Standard => 30.00,
            self::Silver   => 35.00,
            self::Gold     => 40.00,
            self::Platinum => 45.00,
        };
    }

    /** الحد الأدنى من التحويلات للوصول لهذا المستوى */
    public function minConversions(): int
    {
        return match($this) {
            self::Standard => 0,
            self::Silver   => 10,
            self::Gold     => 30,
            self::Platinum => 100,
        };
    }

    public function label(): string
    {
        return match($this) {
            self::Standard => 'Standard',
            self::Silver   => 'Silver',
            self::Gold     => 'Gold',
            self::Platinum => 'Platinum',
        };
    }

    public function labelAr(): string
    {
        return match($this) {
            self::Standard => 'أساسي',
            self::Silver   => 'فضي',
            self::Gold     => 'ذهبي',
            self::Platinum => 'بلاتيني',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Standard => 'bg-gray-100 text-gray-700',
            self::Silver   => 'bg-slate-100 text-slate-700',
            self::Gold     => 'bg-yellow-100 text-yellow-800',
            self::Platinum => 'bg-purple-100 text-purple-800',
        };
    }

    /**
     * حساب المستوى المناسب بناءً على عدد التحويلات.
     * يُستخدَم في UpgradeAffiliateTierAction.
     */
    public static function fromConversions(int $totalConverted): self
    {
        return match(true) {
            $totalConverted >= 100 => self::Platinum,
            $totalConverted >= 30  => self::Gold,
            $totalConverted >= 10  => self::Silver,
            default                => self::Standard,
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

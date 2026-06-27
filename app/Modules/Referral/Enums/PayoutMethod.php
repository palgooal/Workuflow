<?php

namespace App\Modules\Referral\Enums;

/**
 * PayoutMethod — طريقة صرف العمولة
 *
 * مخزَّن كـ VARCHAR(20) في قاعدة البيانات (لا MySQL ENUM).
 * CHECK constraint: CHECK (method IN ('bank','whatsapp','credit'))
 *
 * يُستخدَم في جدولَي: affiliates.payout_method + referral_payouts.method
 */
enum PayoutMethod: string
{
    case Bank      = 'bank';       // تحويل بنكي
    case Whatsapp  = 'whatsapp';   // واتساب (طريقة دراهم الافتراضية)
    case Credit    = 'credit';     // رصيد داخلي في المنصة

    public function label(): string
    {
        return match($this) {
            self::Bank     => 'تحويل بنكي',
            self::Whatsapp => 'واتساب',
            self::Credit   => 'رصيد داخلي',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Bank     => 'heroicon-o-building-library',
            self::Whatsapp => 'heroicon-o-chat-bubble-left',
            self::Credit   => 'heroicon-o-credit-card',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

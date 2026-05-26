<?php

namespace App\Modules\CRM\Enums;

/**
 * ClientSource — مصدر اكتساب العميل
 *
 * يُساعد في تحليل قنوات اكتساب العملاء.
 */
enum ClientSource: string
{
    case Direct      = 'direct';
    case Referral    = 'referral';
    case SocialMedia = 'social_media';
    case Website     = 'website';
    case Import      = 'import';
    case Other       = 'other';

    public function label(): string
    {
        return match($this) {
            self::Direct      => 'مباشر',
            self::Referral    => 'إحالة',
            self::SocialMedia => 'وسائل التواصل',
            self::Website     => 'الموقع الإلكتروني',
            self::Import      => 'استيراد',
            self::Other       => 'أخرى',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Direct      => '🤝',
            self::Referral    => '📢',
            self::SocialMedia => '📱',
            self::Website     => '🌐',
            self::Import      => '📥',
            self::Other       => '📌',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

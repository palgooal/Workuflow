<?php

namespace App\Support\Enums;

/**
 * المجموعة التي يظهر ضمنها رابط الصفحة في الفوتر (إن ظهر أصلاً).
 * مخزَّنة كـ VARCHAR (وليس ENUM) حسب قاعدة المشروع.
 */
enum PageFooterGroup: string
{
    case Product = 'product';
    case Company = 'company';
    case Legal   = 'legal';
    case None    = 'none';

    public function label(): string
    {
        return match ($this) {
            self::Product => 'المنتج',
            self::Company => 'الشركة',
            self::Legal   => 'قانوني',
            self::None    => 'لا تظهر في الفوتر',
        };
    }
}

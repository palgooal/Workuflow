<?php

namespace App\Support\Enums;

/**
 * نوع الصفحة في نظام إدارة المحتوى المصغّر.
 *
 * مخزَّن كـ VARCHAR في قاعدة البيانات (وليس ENUM حسب قاعدة المشروع)،
 * عبر cast من نوع enum على الموديل App\Models\Page.
 */
enum PageType: string
{
    case General   = 'general';
    case Marketing = 'marketing';
    case Legal     = 'legal';

    public function label(): string
    {
        return match ($this) {
            self::General   => 'عامة',
            self::Marketing => 'تسويقية',
            self::Legal     => 'قانونية',
        };
    }
}

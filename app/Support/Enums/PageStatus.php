<?php

namespace App\Support\Enums;

/**
 * حالة نشر الصفحة. مخزَّنة كـ VARCHAR (وليس ENUM) حسب قاعدة المشروع.
 */
enum PageStatus: string
{
    case Draft     = 'draft';
    case Published = 'published';
    case Archived  = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft     => 'مسودة',
            self::Published => 'منشورة',
            self::Archived  => 'مؤرشفة',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft     => 'gray',
            self::Published => 'success',
            self::Archived  => 'warning',
        };
    }
}

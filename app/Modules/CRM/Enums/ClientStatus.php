<?php

namespace App\Modules\CRM\Enums;

/**
 * ClientStatus — حالة العميل
 *
 * VARCHAR في قاعدة البيانات (C-03 Fix — zero-downtime migrations).
 * الإضافة: تغيير قيمة في هذا الـ Enum فقط بدون ALTER TABLE.
 */
enum ClientStatus: string
{
    case Active   = 'active';
    case Inactive = 'inactive';
    case Prospect = 'prospect';
    case Archived = 'archived';

    public function label(): string
    {
        return match($this) {
            self::Active   => 'نشط',
            self::Inactive => 'غير نشط',
            self::Prospect => 'عميل محتمل',
            self::Archived => 'مؤرشف',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Active   => '#2DCEA8',   // teal — نشط
            self::Inactive => '#6B7280',   // gray — غير نشط
            self::Prospect => '#3B82F6',   // blue — محتمل
            self::Archived => '#9CA3AF',   // light gray — مؤرشف
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Active   => 'bg-teal-100 text-teal-800',
            self::Inactive => 'bg-gray-100 text-gray-700',
            self::Prospect => 'bg-blue-100 text-blue-800',
            self::Archived => 'bg-gray-100 text-gray-400',
        };
    }

    /** هل يمكن ظهور العميل في القوائم العادية؟ */
    public function isVisible(): bool
    {
        return $this !== self::Archived;
    }

    /** العوامل الأساسية الأربعة للانتقال بين الحالات */
    public function canTransitionTo(self $new): bool
    {
        return match($this) {
            self::Prospect => in_array($new, [self::Active, self::Inactive, self::Archived]),
            self::Active   => in_array($new, [self::Inactive, self::Archived]),
            self::Inactive => in_array($new, [self::Active, self::Archived]),
            self::Archived => in_array($new, [self::Active]),
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

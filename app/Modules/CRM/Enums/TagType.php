<?php

namespace App\Modules\CRM\Enums;

/**
 * TagType — نوع الوسم
 *
 * system: وسوم ثابتة تُنشأ بالـ Seeder، لا تُحذف، user_id = NULL
 * custom: وسوم مُنشأة بالمستخدم، مرتبطة بـ user_id، قابلة للحذف
 */
enum TagType: string
{
    case System = 'system';
    case Custom = 'custom';

    public function label(): string
    {
        return match($this) {
            self::System => 'وسم النظام',
            self::Custom => 'وسم مخصص',
        };
    }

    /** هل يمكن حذف هذا الوسم؟ */
    public function isDeletable(): bool
    {
        return $this === self::Custom;
    }

    /** هل يمكن تعديل لون هذا الوسم؟ */
    public function isEditable(): bool
    {
        return $this === self::Custom;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

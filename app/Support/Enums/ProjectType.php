<?php

namespace App\Support\Enums;

enum ProjectType: string
{
    case Personal = 'personal';
    case Business = 'business';

    public function label(): string
    {
        return match($this) {
            self::Personal => 'شخصي',
            self::Business => 'تجاري',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Personal => '🏠',
            self::Business => '💼',
        };
    }
}

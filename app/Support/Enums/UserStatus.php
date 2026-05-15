<?php

namespace App\Support\Enums;

enum UserStatus: string
{
    case Active    = 'active';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match($this) {
            self::Active    => 'نشط',
            self::Suspended => 'موقوف',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Active    => 'success',
            self::Suspended => 'danger',
        };
    }
}

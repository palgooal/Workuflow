<?php

namespace App\Support\Enums;

enum WalletType: string
{
    case Cash   = 'cash';
    case Bank   = 'bank';
    case Custom = 'custom';

    public function label(): string
    {
        return match($this) {
            self::Cash   => 'كاش',
            self::Bank   => 'بنك',
            self::Custom => 'مخصص',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Cash   => '💵',
            self::Bank   => '🏦',
            self::Custom => '📦',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Cash   => 'emerald',
            self::Bank   => 'blue',
            self::Custom => 'purple',
        };
    }

    public function tailwindBadge(): string
    {
        return match($this) {
            self::Cash   => 'bg-emerald-100 text-emerald-700',
            self::Bank   => 'bg-blue-100 text-blue-700',
            self::Custom => 'bg-purple-100 text-purple-700',
        };
    }
}

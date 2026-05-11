<?php

namespace App\Support\Enums;

enum DebtType: string
{
    case Borrowed = 'borrowed'; // دين عليك — اقترضت من شخص
    case Lent     = 'lent';     // دين لك — أقرضت شخصاً

    public function label(): string
    {
        return match($this) {
            self::Borrowed => 'دين عليّ',
            self::Lent     => 'دين لي',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Borrowed => 'red',
            self::Lent     => 'green',
        };
    }
}

<?php

namespace App\Support\Enums;

enum DebtStatus: string
{
    case Active        = 'active';
    case PartiallyPaid = 'partially_paid';
    case Paid          = 'paid';

    public function label(): string
    {
        return match($this) {
            self::Active        => 'نشط',
            self::PartiallyPaid => 'مدفوع جزئياً',
            self::Paid          => 'مدفوع بالكامل',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Active        => 'red',
            self::PartiallyPaid => 'yellow',
            self::Paid          => 'green',
        };
    }

    public function isPaid(): bool
    {
        return $this === self::Paid;
    }
}

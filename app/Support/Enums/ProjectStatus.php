<?php

namespace App\Support\Enums;

enum ProjectStatus: string
{
    case Active    = 'active';
    case Completed = 'completed';
    case OnHold    = 'on_hold';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Active    => 'نشط',
            self::Completed => 'مكتمل',
            self::OnHold    => 'متوقف',
            self::Cancelled => 'ملغي',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Active    => '🟢',
            self::Completed => '✅',
            self::OnHold    => '⏸',
            self::Cancelled => '❌',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Active    => 'green',
            self::Completed => 'blue',
            self::OnHold    => 'amber',
            self::Cancelled => 'red',
        };
    }

    public function tailwindBadge(): string
    {
        return match($this) {
            self::Active    => 'bg-emerald-100 text-emerald-700',
            self::Completed => 'bg-blue-100 text-blue-700',
            self::OnHold    => 'bg-amber-100 text-amber-700',
            self::Cancelled => 'bg-red-100 text-red-700',
        };
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }

    /** ترتيب للعرض في القوائم */
    public function sortOrder(): int
    {
        return match($this) {
            self::Active    => 1,
            self::Completed => 2,
            self::OnHold    => 3,
            self::Cancelled => 4,
        };
    }
}

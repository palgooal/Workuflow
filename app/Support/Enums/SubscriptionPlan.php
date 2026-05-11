<?php

namespace App\Support\Enums;

enum SubscriptionPlan: string
{
    case Free     = 'free';
    case Pro      = 'pro';
    case Business = 'business';

    public function label(): string
    {
        return match($this) {
            self::Free     => 'مجاني',
            self::Pro      => 'Pro',
            self::Business => 'Business',
        };
    }

    public function maxProjects(): int
    {
        return match($this) {
            self::Free     => 2,
            self::Pro      => 10,
            self::Business => PHP_INT_MAX,
        };
    }

    public function maxTransactionsPerMonth(): int
    {
        return match($this) {
            self::Free     => 50,
            self::Pro      => 500,
            self::Business => PHP_INT_MAX,
        };
    }

    public function canExport(): bool
    {
        return $this !== self::Free;
    }

    public function canAccessApi(): bool
    {
        return $this === self::Business;
    }

    public function hasAdvancedReports(): bool
    {
        return $this !== self::Free;
    }
}

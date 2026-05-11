<?php

namespace App\Support\Helpers;

class MoneyFormatter
{
    /**
     * تنسيق المبلغ بالعملة المحددة
     */
    public static function format(float $amount, string $currency = 'SAR'): string
    {
        $formatted = number_format(abs($amount), 2);

        return match($currency) {
            'SAR' => $formatted . ' ر.س',
            'USD' => '$ ' . $formatted,
            'EUR' => '€ ' . $formatted,
            'GBP' => '£ ' . $formatted,
            'AED' => $formatted . ' د.إ',
            'KWD' => $formatted . ' د.ك',
            default => $formatted . ' ' . $currency,
        };
    }

    /**
     * تنسيق مع إشارة + أو - حسب النوع
     */
    public static function formatWithSign(float $amount, string $currency = 'SAR'): string
    {
        $sign = $amount >= 0 ? '+' : '-';
        return $sign . ' ' . self::format($amount, $currency);
    }

    /**
     * تحويل المبلغ لصيغة مختصرة: 1.2k | 1.5M
     */
    public static function compact(float $amount): string
    {
        if ($amount >= 1_000_000) {
            return number_format($amount / 1_000_000, 1) . 'M';
        }

        if ($amount >= 1_000) {
            return number_format($amount / 1_000, 1) . 'k';
        }

        return number_format($amount, 2);
    }
}

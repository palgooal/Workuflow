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
            // ── خليج عربي ──────────────────────────────────
            'SAR' => $formatted . ' ر.س',
            'AED' => $formatted . ' د.إ',
            'KWD' => $formatted . ' د.ك',
            'QAR' => $formatted . ' ر.ق',
            'BHD' => $formatted . ' د.ب',
            'OMR' => $formatted . ' ر.ع',
            // ── المشرق العربي ───────────────────────────────
            'JOD' => $formatted . ' د.أ',
            'IQD' => $formatted . ' ع.د',
            'SYP' => $formatted . ' ل.س',
            'LBP' => $formatted . ' ل.ل',
            'ILS' => '₪ '      . $formatted,
            'YER' => $formatted . ' ر.ي',
            // ── شمال أفريقيا ────────────────────────────────
            'EGP' => $formatted . ' ج.م',
            'LYD' => $formatted . ' ل.د',
            'TND' => $formatted . ' د.ت',
            'DZD' => $formatted . ' دج',
            'MAD' => $formatted . ' د.م.',
            'SDG' => $formatted . ' ج.س',
            // ── أفريقيا جنوب الصحراء (دول الجامعة) ─────────
            'SOS' => $formatted . ' ش.ص',
            'MRU' => $formatted . ' أ.م',
            'DJF' => $formatted . ' ف.ج',
            'KMF' => $formatted . ' ف.ق',
            // ── دولي ────────────────────────────────────────
            'USD' => '$ '      . $formatted,
            'EUR' => '€ '      . $formatted,
            'GBP' => '£ '      . $formatted,
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

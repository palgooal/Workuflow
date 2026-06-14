<?php

namespace App\Support\Helpers;

/**
 * قائمة مركزية لجميع العملات المدعومة.
 * استخدم Currency::all()   في Controllers للـ dropdown
 * استخدم Currency::codes() في FormRequests للـ validation
 * استخدم Currency::label() لعرض اسم العملة
 * استخدم Currency::symbol() للرمز المختصر
 */
class Currency
{
    /**
     * جميع العملات: code => ['label' => ..., 'symbol' => ...]
     */
    private static array $list = [
        // ── خليج عربي ──────────────────────────────────────────────────
        'SAR' => ['label' => 'ريال سعودي',        'symbol' => 'ر.س'],
        'AED' => ['label' => 'درهم إماراتي',      'symbol' => 'د.إ'],
        'KWD' => ['label' => 'دينار كويتي',       'symbol' => 'د.ك'],
        'QAR' => ['label' => 'ريال قطري',         'symbol' => 'ر.ق'],
        'BHD' => ['label' => 'دينار بحريني',      'symbol' => 'د.ب'],
        'OMR' => ['label' => 'ريال عُماني',       'symbol' => 'ر.ع'],
        // ── المشرق العربي ───────────────────────────────────────────────
        'JOD' => ['label' => 'دينار أردني',       'symbol' => 'د.أ'],
        'IQD' => ['label' => 'دينار عراقي',       'symbol' => 'ع.د'],
        'SYP' => ['label' => 'ليرة سورية',        'symbol' => 'ل.س'],
        'LBP' => ['label' => 'ليرة لبنانية',      'symbol' => 'ل.ل'],
        'ILS' => ['label' => 'شيكل',              'symbol' => '₪'],
        'YER' => ['label' => 'ريال يمني',         'symbol' => 'ر.ي'],
        // ── شمال أفريقيا ────────────────────────────────────────────────
        'EGP' => ['label' => 'جنيه مصري',         'symbol' => 'ج.م'],
        'LYD' => ['label' => 'دينار ليبي',        'symbol' => 'ل.د'],
        'TND' => ['label' => 'دينار تونسي',       'symbol' => 'د.ت'],
        'DZD' => ['label' => 'دينار جزائري',      'symbol' => 'دج'],
        'MAD' => ['label' => 'درهم مغربي',        'symbol' => 'د.م.'],
        'SDG' => ['label' => 'جنيه سوداني',       'symbol' => 'ج.س'],
        // ── أفريقيا (باقي دول الجامعة) ──────────────────────────────────
        'SOS' => ['label' => 'شلن صومالي',        'symbol' => 'ش.ص'],
        'MRU' => ['label' => 'أوقية موريتانية',   'symbol' => 'أ.م'],
        'DJF' => ['label' => 'فرنك جيبوتي',       'symbol' => 'ف.ج'],
        'KMF' => ['label' => 'فرنك قمري',         'symbol' => 'ف.ق'],
        // ── دولي ────────────────────────────────────────────────────────
        'USD' => ['label' => 'دولار أمريكي',      'symbol' => '$'],
        'EUR' => ['label' => 'يورو',               'symbol' => '€'],
        'GBP' => ['label' => 'جنيه إسترليني',     'symbol' => '£'],
    ];

    /**
     * مصفوفة code => "رمز | اسم (CODE)" — للـ <select> dropdowns
     */
    public static function all(): array
    {
        return collect(self::$list)
            ->mapWithKeys(fn ($v, $k) => [$k => "{$v['symbol']} {$v['label']} ({$k})"])
            ->all();
    }

    /**
     * قائمة الأكواد فقط — لـ validation 'in:...'
     */
    public static function codes(): array
    {
        return array_keys(self::$list);
    }

    /**
     * اسم العملة بالعربية
     */
    public static function label(string $code): string
    {
        return self::$list[$code]['label'] ?? $code;
    }

    /**
     * رمز العملة
     */
    public static function symbol(string $code): string
    {
        return self::$list[$code]['symbol'] ?? $code;
    }

    /**
     * هل الكود مدعوم؟
     */
    public static function isValid(string $code): bool
    {
        return isset(self::$list[$code]);
    }
}

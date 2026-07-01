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

    /**
     * عملات "الفلس" ذات الثلاث خانات العشرية فعلياً (1 دينار = 1000 فلس) —
     * على عكس أغلب العملات الأخرى المدعومة هنا (خانتان عشريتان).
     */
    private static array $threeDecimalCodes = ['JOD', 'KWD', 'BHD', 'OMR'];

    /**
     * عدد الخانات العشرية الصحيح لهذه العملة — 3 لعملات الفلس (JOD/KWD/BHD/OMR)
     * و2 لكل ما عداها. استخدمها بدل تثبيت "2" يدوياً عند تنسيق أي مبلغ مرتبط
     * بعملة الفاتورة/العرض (number_format, step على حقول HTML, إلخ).
     */
    public static function decimals(?string $code): int
    {
        return in_array(strtoupper((string) $code), self::$threeDecimalCodes, true) ? 3 : 2;
    }

    /**
     * قيمة step المناسبة لحقل <input type="number"> بهذه العملة — "0.001" أو "0.01".
     */
    public static function step(?string $code): string
    {
        return '0.' . str_repeat('0', self::decimals($code) - 1) . '1';
    }

    /**
     * تنسيق مبلغ بعدد الخانات العشرية الصحيح لعملته.
     */
    public static function format(float|int|string $amount, ?string $code): string
    {
        return number_format((float) $amount, self::decimals($code));
    }

    /**
     * مصفوفة code => عدد الخانات العشرية — لتمريرها لـ JS (Alpine/JSON) بدل
     * تكرار نفس القائمة الثابتة (JOD/KWD/BHD/OMR) في كل ملف Blade.
     */
    public static function decimalsMap(): array
    {
        return collect(self::codes())
            ->mapWithKeys(fn ($code) => [$code => self::decimals($code)])
            ->all();
    }

    /**
     * مصفوفة code => رمز العملة — لتمريرها لـ JS (Alpine/JSON) حتى تعرض
     * حقول مثل "الخصم" رمز العملة المختارة فعلياً، لا رمزاً ثابتاً (كان
     * النموذج يعرض "₪" دائماً بغض النظر عن العملة المختارة).
     */
    public static function symbolsMap(): array
    {
        return collect(self::$list)
            ->map(fn ($v) => $v['symbol'])
            ->all();
    }
}

<?php

namespace App\Support\Helpers;

/**
 * قائمة مركزية لأكواد الدول (ISO 3166-1 alpha-2) المستخدمة في حقل
 * clients.country / users.billing_country — بنفس أسلوب Currency.php.
 *
 * Why: بوابة الدفع Togo تتطلب "country_name" كنص إنجليزي كامل عند إنشاء
 * receiver_address لكل عميل/مشترك (راجع docs/PAYMENT-COLLECTION.md —
 * "receiver_address لكل عميل")، بينما عندنا الكود المختصر فقط (مثل "PS").
 * استخدم Country::name() لتحويل الكود لاسم إنجليزي ASCII آمن للإرسال لـ Togo،
 * و Country::all() لبناء dropdown بالعربية في نماذج العميل/الإعدادات.
 */
class Country
{
    /**
     * كل الدول: code => ['label_ar' => ..., 'name_en' => ...]
     * name_en يجب أن يبقى ASCII بالكامل — يُرسَل مباشرة لـ Togo API.
     */
    private static array $list = [
        'PS' => ['label_ar' => 'فلسطين',        'name_en' => 'Palestine'],
        'JO' => ['label_ar' => 'الأردن',         'name_en' => 'Jordan'],
        'IL' => ['label_ar' => 'إسرائيل',        'name_en' => 'Israel'],
        'EG' => ['label_ar' => 'مصر',            'name_en' => 'Egypt'],
        'SA' => ['label_ar' => 'السعودية',       'name_en' => 'Saudi Arabia'],
        'AE' => ['label_ar' => 'الإمارات',       'name_en' => 'United Arab Emirates'],
        'QA' => ['label_ar' => 'قطر',            'name_en' => 'Qatar'],
        'KW' => ['label_ar' => 'الكويت',         'name_en' => 'Kuwait'],
        'BH' => ['label_ar' => 'البحرين',        'name_en' => 'Bahrain'],
        'OM' => ['label_ar' => 'عُمان',          'name_en' => 'Oman'],
        'LB' => ['label_ar' => 'لبنان',          'name_en' => 'Lebanon'],
        'SY' => ['label_ar' => 'سوريا',          'name_en' => 'Syria'],
        'IQ' => ['label_ar' => 'العراق',         'name_en' => 'Iraq'],
        'YE' => ['label_ar' => 'اليمن',          'name_en' => 'Yemen'],
        'LY' => ['label_ar' => 'ليبيا',          'name_en' => 'Libya'],
        'TN' => ['label_ar' => 'تونس',           'name_en' => 'Tunisia'],
        'DZ' => ['label_ar' => 'الجزائر',        'name_en' => 'Algeria'],
        'MA' => ['label_ar' => 'المغرب',         'name_en' => 'Morocco'],
        'SD' => ['label_ar' => 'السودان',        'name_en' => 'Sudan'],
        'US' => ['label_ar' => 'الولايات المتحدة', 'name_en' => 'United States'],
        'GB' => ['label_ar' => 'المملكة المتحدة', 'name_en' => 'United Kingdom'],
        'DE' => ['label_ar' => 'ألمانيا',        'name_en' => 'Germany'],
        'FR' => ['label_ar' => 'فرنسا',          'name_en' => 'France'],
        'TR' => ['label_ar' => 'تركيا',          'name_en' => 'Turkey'],
    ];

    /**
     * مصفوفة code => "اسم عربي" — لبناء <select> dropdown.
     */
    public static function all(): array
    {
        return collect(self::$list)
            ->mapWithKeys(fn ($v, $k) => [$k => $v['label_ar']])
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
     * الاسم الإنجليزي الكامل (ASCII) — يُرسَل مباشرة لحقل country_name عند Togo.
     * لو الكود غير معروف بقائمتنا، نُرجع الكود نفسه (ASCII دائماً بحكم أنه
     * كود ISO من حرفين) بدل استثناء أو قيمة فارغة قد تُسقِط الطلب.
     */
    public static function name(?string $code): string
    {
        if (empty($code)) {
            return '';
        }

        return self::$list[strtoupper($code)]['name_en'] ?? strtoupper($code);
    }

    /**
     * الاسم العربي المعروض بالواجهة.
     */
    public static function label(?string $code): string
    {
        if (empty($code)) {
            return '';
        }

        return self::$list[strtoupper($code)]['label_ar'] ?? $code;
    }
}

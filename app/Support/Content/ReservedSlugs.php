<?php

namespace App\Support\Content;

/**
 * قائمة الـslugs المحجوزة التي لا يجوز لصفحة من نظام إدارة المحتوى أن
 * تستخدمها (لتفادي أي تعارض مع Routes النظام الحالية أو المستقبلية).
 *
 * تُستخدَم في: PageResource (تحقق عند الحفظ) وPageController (حارس إضافي
 * وقت العرض، دفاع في العمق).
 *
 * القائمة مبنية على فحص فعلي لكل segment أول في routes/web.php،
 * routes/auth.php، وroutes/crm.php — وليست فقط القائمة المختصرة المُقترحة
 * أصلاً، لضمان عدم وجود أي تعارض حقيقي.
 */
class ReservedSlugs
{
    public const LIST = [
        // المصادقة (routes/auth.php)
        'login', 'register', 'logout', 'password', 'forgot-password',
        'reset-password', 'verify-email', 'confirm-password',

        // النظام العام
        'dashboard', 'admin', 'settings', 'profile', 'api', 'pages',
        'notifications', 'help', 'onboarding', 'reports',

        // الوحدات المالية / CRM
        'projects', 'quotes', 'invoices', 'invoice', 'clients', 'team',
        'wallets', 'wallets-transfer', 'services', 'categories',
        'transactions', 'collections', 'settlement-requests', 'debts',
        'budget', 'recurring', 'billing', 'pay', 'q', 'stripe',

        // المسارات التسويقية/القانونية الحالية (تبقى بأسمائها الخاصة، لا تُستبدَل)
        'legal', 'features', 'pricing', 'faq', 'contact', 'affiliate-program',

        // الجذر
        '', 'up',
    ];

    public static function isReserved(string $slug): bool
    {
        return in_array(strtolower(trim($slug, '/')), self::LIST, true);
    }
}

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payment Provider
    |--------------------------------------------------------------------------
    | اسم مزود الدفع المفعّل حالياً.
    | القيم الممكنة: null | 'stripe' | 'paddle' | 'paymob' | 'moyasar' | ...
    | اتركه null حتى يتم ربط مزود الدفع.
    */

    'provider' => env('BILLING_PROVIDER', null), // 'togo' | 'stripe' | null

    /*
    |--------------------------------------------------------------------------
    | Plan Prices (Display Only) — USD-first
    |--------------------------------------------------------------------------
    | مصدر الحقيقة: docs/PRICING-SOURCE-OF-TRUTH.md
    | الفوترة بالدولار الأمريكي · المعادلات تقديرية (تُعرض للمستخدم)
    | founder_* = أسعار المؤسسين الحصرية (Early Adopters)
    */

    'plans' => [
        'pro' => [
            'label'          => 'الاحترافي',
            'monthly'        => ['price' => '17', 'currency' => 'USD', 'sar_equiv' => '64',  'jod_equiv' => '12', 'ils_equiv' => '63'],
            'annual'         => ['price' => '13', 'currency' => 'USD', 'sar_equiv' => '49',  'jod_equiv' => '9',  'ils_equiv' => '48'],
            'founder_monthly'=> ['price' => '10', 'currency' => 'USD'],
            'founder_annual' => ['price' => '8',  'currency' => 'USD'],
        ],
        'business' => [
            'label'          => 'الأعمال',
            'monthly'        => ['price' => '45', 'currency' => 'USD', 'sar_equiv' => '169', 'jod_equiv' => '32', 'ils_equiv' => '167'],
            'annual'         => ['price' => '34', 'currency' => 'USD', 'sar_equiv' => '127', 'jod_equiv' => '24', 'ils_equiv' => '126'],
            'founder_monthly'=> ['price' => '26', 'currency' => 'USD'],
            'founder_annual' => ['price' => '21', 'currency' => 'USD'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Owner WhatsApp (للترقية اليدوية قبل تفعيل Payment Gateway)
    |--------------------------------------------------------------------------
    | رقم واتساب المؤسس مع كود الدولة بدون + (مثال: 966501234567)
    */

    'owner_whatsapp' => env('OWNER_WHATSAPP', ''),

    /*
    |--------------------------------------------------------------------------
    | Provider Credentials (يُضاف عند ربط المزود)
    |--------------------------------------------------------------------------
    */

    'credentials' => [
        'key'            => env('PAYMENT_KEY'),
        'secret'         => env('PAYMENT_SECRET'),
        'webhook_secret' => env('PAYMENT_WEBHOOK_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Togo Payment Gateway (togo.ps)
    |--------------------------------------------------------------------------
    | الخطوة 1 (مرة واحدة): php artisan togo:setup-receiver
    | ثم أضف TOGO_RECEIVER_ADDRESS_ID للـ .env
    */

    'togo' => [
        'api_key'             => env('TOGO_API_KEY'),
        'receiver_address_id' => env('TOGO_RECEIVER_ADDRESS_ID'),
        'currency'            => env('TOGO_CURRENCY', 'ILS'), // ILS | USD
    ],

];

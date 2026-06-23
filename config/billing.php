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
    | Plan Prices (Display Only)
    |--------------------------------------------------------------------------
    | الأسعار المعروضة في صفحة الأسعار — مستقلة عن مزود الدفع.
    */

    'plans' => [
        'pro' => [
            'label'    => 'Pro',
            'price'    => env('BILLING_PRICE_PRO', '99'),
            'currency' => env('BILLING_CURRENCY', 'SAR'),
        ],
        'business' => [
            'label'    => 'Business',
            'price'    => env('BILLING_PRICE_BUSINESS', '299'),
            'currency' => env('BILLING_CURRENCY', 'SAR'),
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

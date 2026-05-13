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

    'provider' => env('BILLING_PROVIDER', null),

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
    | Provider Credentials (يُضاف عند ربط المزود)
    |--------------------------------------------------------------------------
    */

    'credentials' => [
        'key'            => env('PAYMENT_KEY'),
        'secret'         => env('PAYMENT_SECRET'),
        'webhook_secret' => env('PAYMENT_WEBHOOK_SECRET'),
    ],

];

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Referral Program Configuration
    |--------------------------------------------------------------------------
    */

    /**
     * البريد الإلكتروني الذي يستقبل تنبيهات الاحتيال
     * القيمة الافتراضية = MAIL_FROM_ADDRESS (بريد الإرسال)
     */
    'admin_email' => env('REFERRAL_ADMIN_EMAIL', env('MAIL_FROM_ADDRESS', 'admin@workuflow.com')),

    /**
     * الحد الأدنى للرصيد (USD) لطلب الصرف
     */
    'min_payout_amount' => (float) env('REFERRAL_MIN_PAYOUT', 20.00),

    /**
     * عدد أيام المراجعة قبل الاعتماد التلقائي للعمولة
     * (غير مفعَّل تلقائياً — يُعتمد يدوياً من Filament)
     */
    'review_days' => (int) env('REFERRAL_REVIEW_DAYS', 7),

    /**
     * الحد الأقصى للنقرات من IP واحد يومياً (click spam)
     */
    'max_clicks_per_ip_daily' => (int) env('REFERRAL_MAX_CLICKS_IP', 20),

    /**
     * حصص Tier (total_converted)
     */
    'tiers' => [
        'standard' => ['min_conversions' => 0,   'rate' => 30.0],
        'silver'   => ['min_conversions' => 10,  'rate' => 35.0],
        'gold'     => ['min_conversions' => 30,  'rate' => 40.0],
        'platinum' => ['min_conversions' => 100, 'rate' => 45.0],
    ],

];

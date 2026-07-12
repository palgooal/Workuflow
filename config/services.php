<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | روابط وسائل التواصل الاجتماعي الرسمية لدراهم
    |--------------------------------------------------------------------------
    |
    | تُستخدَم في فوتر الموقع التسويقي. أي منصة لا يوجد لها رابط فعلي هنا
    | (قيمتها null) لا تُعرض أيقونتها إطلاقاً في الفوتر — بمجرد إضافة الرابط
    | في .env تظهر الأيقونة تلقائياً دون أي تعديل إضافي على الكود.
    |
    */
    'social' => [
        'x'         => env('SOCIAL_X_URL'),
        'facebook'  => env('SOCIAL_FACEBOOK_URL'),
        'linkedin'  => env('SOCIAL_LINKEDIN_URL'),
        'instagram' => env('SOCIAL_INSTAGRAM_URL'),
        'whatsapp'  => env('SOCIAL_WHATSAPP_URL'),
    ],

];

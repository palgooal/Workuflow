<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CRM Module — الإعدادات الرئيسية
    |--------------------------------------------------------------------------
    | مرجع: docs/CLIENTS-CRM-SPEC-V2.md
    */

    /*
    |--------------------------------------------------------------------------
    | حدود الخطط (Plan Limits)
    |--------------------------------------------------------------------------
    | كل خطة تحدد الحد الأقصى للعملاء والوسوم والحقول المخصصة.
    | القيمة -1 تعني "غير محدود".
    */

    'limits' => [
        'free' => [
            'max_clients'        => 10,
            'max_tags'           => 3,
            'max_custom_fields'  => 0,
            'can_import'         => false,
            'can_export'         => false,
            'can_portal'         => false,
            'can_automation'     => false,
            'can_health_score'   => false,
            'can_segments'       => false,
        ],
        'pro' => [
            'max_clients'        => 500,
            'max_tags'           => 10,
            'max_custom_fields'  => 5,
            'can_import'         => true,
            'can_export'         => true,
            'can_portal'         => false,
            'can_automation'     => false,
            'can_health_score'   => true,
            'can_segments'       => true,
        ],
        'business' => [
            'max_clients'        => -1,
            'max_tags'           => -1,
            'max_custom_fields'  => -1,
            'can_import'         => true,
            'can_export'         => true,
            'can_portal'         => true,
            'can_automation'     => true,
            'can_health_score'   => true,
            'can_segments'       => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | الاستيراد (Import)
    |--------------------------------------------------------------------------
    */

    'import' => [
        'max_file_size_kb'   => 10240,       // 10 MB
        'allowed_mimes'      => ['xlsx', 'csv', 'xls'],
        'chunk_size'         => 1000,        // صفوف لكل Chunk
        'batch_size'         => 500,         // صفوف لكل Batch Insert
        'idempotency_ttl'    => 86400 * 7,   // 7 أيام (ثواني)
    ],

    /*
    |--------------------------------------------------------------------------
    | Health Score
    |--------------------------------------------------------------------------
    | أوزان مكونات مؤشر صحة العميل — المجموع يجب أن يساوي 1.0
    */

    'health_score' => [
        'weights' => [
            'payment_rate'       => 0.35,  // معدل الدفع
            'work_frequency'     => 0.25,  // تكرار العمل
            'revenue_value'      => 0.20,  // قيمة الإيراد
            'contact_regularity' => 0.10,  // انتظام التواصل
            'response_rate'      => 0.10,  // معدل الاستجابة
        ],
        'recency_bias' => [
            'recent_months'  => 3,
            'recent_weight'  => 0.70,
            'historic_months'=> 12,
            'historic_weight'=> 0.30,
        ],
        'grades' => [
            'excellent' => 80,
            'good'      => 60,
            'fair'      => 40,
            // poor = أقل من 40
        ],
        'recalculate_schedule' => '02:00',  // وقت إعادة الحساب الليلي
    ],

    /*
    |--------------------------------------------------------------------------
    | وسوم النظام (System Tags)
    |--------------------------------------------------------------------------
    | هذه الوسوم ثابتة وتُنشأ بالـ Seeder — لا تُحذف.
    */

    'system_tags' => [
        ['slug' => 'vip',            'name' => 'VIP',            'color' => '#10B981', 'icon' => '⭐', 'priority' => 1],
        ['slug' => 'late-payer',     'name' => 'Late Payer',     'color' => '#EF4444', 'icon' => '⚠️', 'priority' => 2],
        ['slug' => 'hesitant',       'name' => 'Hesitant',       'color' => '#F59E0B', 'icon' => '🤔', 'priority' => 3],
        ['slug' => 'new-client',     'name' => 'New Client',     'color' => '#3B82F6', 'icon' => '🆕', 'priority' => 4],
        ['slug' => 'inactive',       'name' => 'Inactive',       'color' => '#6B7280', 'icon' => '💤', 'priority' => 5],
        ['slug' => 'high-value',     'name' => 'High Value',     'color' => '#8B5CF6', 'icon' => '💎', 'priority' => 6],
        ['slug' => 'referred',       'name' => 'Referred',       'color' => '#EC4899', 'icon' => '🤝', 'priority' => 7],
        ['slug' => 'pending-review', 'name' => 'Pending Review', 'color' => '#F97316', 'icon' => '🔍', 'priority' => 8],
    ],

    /*
    |--------------------------------------------------------------------------
    | الأتمتة (Automation)
    |--------------------------------------------------------------------------
    */

    'automation' => [
        'max_rules_per_user' => 20,
        'auto_tag_confidence_threshold' => 0.85,  // تطبيق وسم تلقائي عند ثقة ≥ 85%
    ],

    /*
    |--------------------------------------------------------------------------
    | بوابة العميل (Client Portal)
    |--------------------------------------------------------------------------
    */

    'portal' => [
        'token_lengths'    => [1, 7, 30, 90, 365],  // مدد الصلاحية بالأيام
        'max_attempts'     => 5,                      // محاولات تسجيل دخول/ساعة
        'rate_limit_decay' => 3600,                   // ثانية (1 ساعة)
        'delay_min_ms'     => 50000,                  // تأخير أدنى عند الفشل (microseconds)
        'delay_max_ms'     => 150000,                 // تأخير أقصى عند الفشل
    ],

    /*
    |--------------------------------------------------------------------------
    | الكاش (Cache)
    |--------------------------------------------------------------------------
    | TTL بالثواني. يستخدم key-based invalidation (لا Redis Tags مطلوب).
    */

    'cache' => [
        'client_profile_ttl'  => 900,   // 15 دقيقة
        'client_list_ttl'     => 300,   // 5 دقائق
        'health_score_ttl'    => 3600,  // 1 ساعة
        'tag_list_ttl'        => 1800,  // 30 دقيقة
        'segment_count_ttl'   => 600,   // 10 دقائق
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue (الطوابير)
    |--------------------------------------------------------------------------
    */

    'queues' => [
        'default'  => 'crm-default',
        'imports'  => 'crm-imports',
        'exports'  => 'crm-exports',
        'emails'   => 'crm-emails',
    ],

];

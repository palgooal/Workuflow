<?php

return [

    /*
    |--------------------------------------------------------------------------
    | تصدير بيانات المستخدم (Data Export)
    |--------------------------------------------------------------------------
    |
    | إعدادات ميزة "تنزيل نسخة من بياناتي" من لوحة حساب المستخدم.
    | هذه ليست نسخة نظام كاملة — فقط بيانات المستخدم الحالي وفق عزل user_id.
    |
    */
    'user_export' => [
        // الـ disk المستخدَم لتخزين ملفات ZIP الخاصة بتصدير المستخدم (مؤقت وخاص، ليس public)
        'disk' => env('DATA_EXPORT_DISK', 'exports'),

        // مسار فرعي داخل الـ disk
        'path' => 'user-data-exports',

        // مدة صلاحية رابط التنزيل الموقّع (Signed URL) بالدقائق
        'signed_url_ttl_minutes' => (int) env('DATA_EXPORT_SIGNED_URL_TTL', 60),

        // مدة الاحتفاظ بالملف قبل الحذف التلقائي (بالساعات)
        'retention_hours' => (int) env('DATA_EXPORT_RETENTION_HOURS', 72),

        // الحد الأدنى بين طلبين لنفس المستخدم (بالساعات)
        'rate_limit_hours' => (int) env('DATA_EXPORT_RATE_LIMIT_HOURS', 24),

        // أقصى عدد طلبات نشطة (pending/processing) في آن واحد لكل مستخدم
        'max_active_requests' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | النسخ الاحتياطية التشغيلية (System Backups) — Filament / super_admin فقط
    |--------------------------------------------------------------------------
    */
    'system_backup' => [
        // الـ disk الأساسي لتخزين نسخ النظام. محلي مؤقتاً — راجع docs/BACKUP-SYSTEM.md
        // بخصوص القرار المعلَّق حول تفعيل تخزين خارجي (S3 أو غيره).
        'disk' => env('SYSTEM_BACKUP_DISK', 'backups'),

        'path' => 'system-backups',

        // هل الـ disk الحالي "خارج الخادم" فعلياً؟ يُستخدم فقط لعرض تحذير في الواجهة —
        // لا تغيّره إلى true إلا بعد ربط disk خارجي فعلي (S3/SFTP/إلخ) والتحقق منه.
        'disk_is_offsite' => (bool) env('SYSTEM_BACKUP_DISK_IS_OFFSITE', false),

        // القيمة الفعلية لمفتاح التشفير — تُقرأ هنا فقط (داخل ملف config) عبر env()
        // حتى تبقى صحيحة بعد php artisan config:cache. لا تُستخدَم env() لهذا المفتاح
        // في أي مكان آخر خارج ملف config هذا (راجع BackupEncryptor::rawKey()).
        'encryption_key' => env('BACKUP_ENCRYPTION_KEY'),

        // اسم متغيّر البيئة الذي يحمل مفتاح التشفير — لأغراض التوثيق/العرض فقط،
        // غير مستخدَم في القراءة الفعلية للمفتاح.
        'encryption_key_env' => 'BACKUP_ENCRYPTION_KEY',

        // الاحتفاظ (Retention) — قابلة للتعديل من Config أو من Setting (مجموعة backup)
        'retention' => [
            'daily'   => (int) env('BACKUP_RETENTION_DAILY', 7),
            'weekly'  => (int) env('BACKUP_RETENTION_WEEKLY', 4),
            'monthly' => (int) env('BACKUP_RETENTION_MONTHLY', 3),
        ],

        // مسارات storage التي تُضمَّن في النسخة "الكاملة" (Full) — مرفقات ضرورية فقط
        'include_storage_paths' => [
            'app/private/client-attachments',
            'app/public',
        ],

        // مسارات/أنماط مستبعدة صراحة من أي نسخة (Cache، logs كبيرة، vendor، node_modules)
        'exclude_patterns' => [
            'app/private/imports/tmp',
            'app/private/user-data-exports',
            'framework/cache',
            'framework/sessions',
            'framework/testing',
            'framework/views',
            'logs',
        ],

        // مهلة تنفيذ Job النسخ الاحتياطي (ثواني) — نسخ قواعد بيانات كبيرة قد تستغرق وقتاً
        'job_timeout' => (int) env('SYSTEM_BACKUP_JOB_TIMEOUT', 1800),
    ],

];

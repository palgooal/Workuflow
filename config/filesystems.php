<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

        // ── نسخ بيانات المستخدم (تصدير من لوحة الحساب) ────────────────────
        // خاص تماماً (غير public)، بدون رابط مباشر. التنزيل فقط عبر Signed URL
        // من DataExportController. راجع docs/DATA-EXPORT.md.
        'exports' => [
            'driver' => env('DATA_EXPORT_FILESYSTEM_DRIVER', 'local'),
            'root' => storage_path('app/private/user-data-exports'),
            'serve' => false,
            'throw' => true,
            'report' => false,
        ],

        // ── النسخ الاحتياطية التشغيلية الكاملة (Filament / super_admin) ───
        // local الآن فقط. راجع config/backups.php وdocs/BACKUP-SYSTEM.md —
        // يجب ربط disk خارجي (SFTP/S3/إلخ) قبل اعتبار النظام جاهزاً لـ Disaster Recovery.
        // لا تغيّر driver هنا إلى s3 قبل تثبيت league/flysystem-aws-s3-v3 والتحقق من الإنتاج.
        'backups' => [
            'driver' => env('SYSTEM_BACKUP_FILESYSTEM_DRIVER', 'local'),
            'root' => storage_path('app/private/system-backups'),
            'serve' => false,
            'throw' => true,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];

<?php

use App\Models\Backup;
use App\Models\Setting;
use App\Services\Backup\BackupMonitoringService;
use App\Support\Enums\BackupStatus;
use App\Support\Enums\BackupType;
use Illuminate\Support\Facades\Storage;

// المرحلة السادسة (الجزء الأول) — Backup Monitoring Dashboard. هذا الملف
// يختبر BackupMonitoringService فقط (منطق حساب/عرض اللوحة) — لا علاقة له
// بمحرك النسخ/الاستعادة أو الجدولة نفسها (مغطّيان بالكامل في ملفات أخرى ضمن
// نفس المجلد ولا يُعاد اختبارهما هنا).

test('snapshot handles the case of no backups at all', function () {
    $snapshot = app(BackupMonitoringService::class)->snapshot();

    expect($snapshot['last_successful'])->toBeNull()
        ->and($snapshot['last_failed'])->toBeNull()
        ->and($snapshot['last_database_backup'])->toBeNull()
        ->and($snapshot['last_full_backup'])->toBeNull()
        ->and($snapshot['counts'])->toBe([
            'completed' => 0, 'running' => 0, 'failed' => 0, 'database' => 0, 'full' => 0,
        ])
        ->and($snapshot['total_size_bytes'])->toBe(0)
        ->and($snapshot['total_size_human'])->toBe('0 B')
        // Critical: لا توجد أي نسخة ناجحة إطلاقاً
        ->and($snapshot['health_status'])->toBe('critical');
});

test('snapshot reports the most recently completed successful backup, overall and per type', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');

    $olderDb = makeCompletedBackup($disk, BackupType::Database, now()->subHours(5));
    $newerDb = makeCompletedBackup($disk, BackupType::Database, now()->subHours(1));
    $full    = makeCompletedBackup($disk, BackupType::Full, now()->subMinutes(30));

    $snapshot = app(BackupMonitoringService::class)->snapshot();

    // الأحدث بوقت الاكتمال الفعلي (completed_at) عبر كل الأنواع
    expect($snapshot['last_successful']->id)->toBe($full->id)
        ->and($snapshot['last_database_backup']->id)->toBe($newerDb->id)
        ->and($snapshot['last_full_backup']->id)->toBe($full->id)
        ->and($snapshot['health_status'])->toBe('healthy');
});

test('snapshot reports the last failed backup with its reason', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');

    // نسخة ناجحة حديثة حتى لا تكون الحالة critical بسبب "لا نجاح إطلاقاً"،
    // فنختبر مسار "يوجد فشل حديث" بمعزل عن ذلك الشرط الآخر.
    makeCompletedBackup($disk, BackupType::Database, now()->subMinutes(10));

    $failed = Backup::create([
        'name'         => 'failed-backup',
        'type'         => BackupType::Database,
        'status'       => BackupStatus::Failed,
        'error_message' => 'فشل الاتصال بقاعدة البيانات',
        'started_at'   => now()->subMinutes(5),
        'completed_at' => now()->subMinutes(4),
    ]);

    $snapshot = app(BackupMonitoringService::class)->snapshot();

    expect($snapshot['last_failed']->id)->toBe($failed->id)
        ->and($snapshot['last_failed']->error_message)->toBe('فشل الاتصال بقاعدة البيانات')
        ->and($snapshot['counts']['failed'])->toBe(1)
        // Warning: يوجد فشل حديث (خلال آخر 24 ساعة)
        ->and($snapshot['health_status'])->toBe('warning');
});

test('snapshot counts running backups and flags a stuck one as critical even with a recent success', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');

    // نجاح حديث جداً — يثبت أن "Running عالقة" وحدها تفرض critical، بمعزل
    // تماماً عن شرط "لا توجد أي نسخة ناجحة".
    makeCompletedBackup($disk, BackupType::Database, now()->subMinutes(5));

    $stuckRunning = Backup::create([
        'name'       => 'stuck-running',
        'type'       => BackupType::Full,
        'status'     => BackupStatus::Running,
        // أقدم من ضعف job_timeout الافتراضي (1800×2 = 3600 ثانية) — "عالقة"
        'started_at' => now()->subHours(3),
    ]);

    $snapshot = app(BackupMonitoringService::class)->snapshot();

    expect($snapshot['counts']['running'])->toBe(1)
        // Critical: توجد Running عالقة، رغم وجود نجاح حديث
        ->and($snapshot['health_status'])->toBe('critical');
});

test('snapshot does not treat a freshly running backup as stuck', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    makeCompletedBackup($disk, BackupType::Database, now()->subMinutes(5));

    Backup::create([
        'name'       => 'fresh-running',
        'type'       => BackupType::Full,
        'status'     => BackupStatus::Running,
        'started_at' => now()->subMinutes(2),
    ]);

    $snapshot = app(BackupMonitoringService::class)->snapshot();

    expect($snapshot['counts']['running'])->toBe(1)
        ->and($snapshot['health_status'])->toBe('healthy');
});

test('snapshot sums the total size of all backups regardless of status', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');

    $a = makeCompletedBackup($disk, BackupType::Database, now()->subHours(2));
    $a->update(['size_bytes' => 1_000_000]); // 1 MB تقريباً

    $b = makeCompletedBackup($disk, BackupType::Full, now()->subHour());
    $b->update(['size_bytes' => 2_000_000]); // 2 MB تقريباً

    $snapshot = app(BackupMonitoringService::class)->snapshot();

    expect($snapshot['total_size_bytes'])->toBe(3_000_000)
        ->and($snapshot['total_size_human'])->toContain('MB');
});

test('snapshot marks health as warning when the last successful backup is stale', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');

    makeCompletedBackup($disk, BackupType::Database, now()->subHours(72)); // أقدم من 48 ساعة

    $snapshot = app(BackupMonitoringService::class)->snapshot();

    expect($snapshot['health_status'])->toBe('warning');
});

test('snapshot computes the next scheduled database and full backup runs from settings', function () {
    Setting::setGroup('backup_schedule', [
        'database_backup_enabled' => '1',
        'full_backup_enabled'     => '1',
        'backup_time'             => '02:00',
        'backup_timezone'         => 'UTC',
    ]);

    $snapshot = app(BackupMonitoringService::class)->snapshot();

    expect($snapshot['next_database_run'])->not->toBeNull()
        ->and($snapshot['next_database_run']->format('H:i'))->toBe('02:00')
        ->and($snapshot['next_database_run']->isFuture())->toBeTrue()
        ->and($snapshot['next_full_run'])->not->toBeNull()
        ->and($snapshot['next_full_run']->format('H:i'))->toBe('02:00')
        ->and($snapshot['next_full_run']->isFriday())->toBeTrue()
        ->and($snapshot['next_full_run']->isFuture())->toBeTrue();
});

test('snapshot returns null next-run values when scheduling is disabled', function () {
    Setting::setGroup('backup_schedule', [
        'database_backup_enabled' => '0',
        'full_backup_enabled'     => '0',
    ]);

    $snapshot = app(BackupMonitoringService::class)->snapshot();

    expect($snapshot['next_database_run'])->toBeNull()
        ->and($snapshot['next_full_run'])->toBeNull();
});

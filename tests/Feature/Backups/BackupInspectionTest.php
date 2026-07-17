<?php

use App\Services\Backup\BackupInspectionService;
use App\Support\Enums\BackupType;
use Illuminate\Support\Facades\Storage;

// makeEncryptedTestBackupArchive() وtmpBackupDirSnapshot() موحّدتان في
// tests/Helpers.php (محمَّلة من tests/Pest.php) — الأولى تبني أرشيفاً مشفَّراً
// حقيقياً عبر BackupEncryptor الحالية (بدون أي تعديل عليها)، والثانية تلتقط
// لقطة من محتوى مجلد الملفات المؤقتة للتحقق من التنظيف.

// ==================== readManifest() ====================

test('readManifest returns manifest fields and computed file stats for a valid backup', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    $backup = makeEncryptedTestBackupArchive($disk, BackupType::Full);

    $result = app(BackupInspectionService::class)->readManifest($backup);

    expect($result['manifest']['type'])->toBe('full');
    expect($result['manifest']['laravel'])->toBe(app()->version());
    expect($result['manifest']['storage_paths'])->toBe(['app/private/client-attachments']);
    expect($result['file_count'])->toBeGreaterThanOrEqual(3); // manifest.json + database.sql + storage/app/public/test.txt
    expect($result['total_size'])->toBeGreaterThan(0);
});

test('readManifest deletes all temporary files after reading', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    $backup = makeEncryptedTestBackupArchive($disk, BackupType::Database);

    $before = tmpBackupDirSnapshot();

    app(BackupInspectionService::class)->readManifest($backup);

    $after = tmpBackupDirSnapshot();
    expect($after->diff($before))->toBeEmpty();
});

test('readManifest deletes temporary files even when manifest.json is missing', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    $backup = makeEncryptedTestBackupArchive($disk, BackupType::Database, includeManifest: false);

    $before = tmpBackupDirSnapshot();

    try {
        app(BackupInspectionService::class)->readManifest($backup);
    } catch (\Throwable) {
        // متوقَّع — نتحقق فقط من التنظيف أدناه
    }

    $after = tmpBackupDirSnapshot();
    expect($after->diff($before))->toBeEmpty();
});

// ==================== verify() ====================

test('verify succeeds for a genuinely valid backup and updates integrity fields only', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    $backup = makeEncryptedTestBackupArchive($disk, BackupType::Database);

    $result = app(BackupInspectionService::class)->verify($backup);

    expect($result['ok'])->toBeTrue();
    expect($result['reason'])->toBeNull();

    $backup->refresh();
    expect($backup->integrity_verified)->toBeTrue();
    expect($backup->integrity_checked_at)->not->toBeNull();
});

test('verify fails when checksum does not match and does not touch integrity fields', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    $backup = makeEncryptedTestBackupArchive($disk, BackupType::Database);
    $backup->update(['checksum' => 'tampered-checksum-value']);

    $result = app(BackupInspectionService::class)->verify($backup);

    expect($result['ok'])->toBeFalse();
    expect($result['reason'])->not->toBeEmpty();

    $backup->refresh();
    expect($backup->integrity_verified)->toBeNull();
    expect($backup->integrity_checked_at)->toBeNull();
});

test('verify fails when manifest.json is missing from the archive and does not touch integrity fields', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    $backup = makeEncryptedTestBackupArchive($disk, BackupType::Database, includeManifest: false);

    $result = app(BackupInspectionService::class)->verify($backup);

    expect($result['ok'])->toBeFalse();
    expect($result['reason'])->toContain('manifest.json');

    $backup->refresh();
    expect($backup->integrity_verified)->toBeNull();
    expect($backup->integrity_checked_at)->toBeNull();
});

test('verify fails when database.sql is missing from the archive and does not touch integrity fields', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    $backup = makeEncryptedTestBackupArchive($disk, BackupType::Database, includeDatabaseSql: false);

    $result = app(BackupInspectionService::class)->verify($backup);

    expect($result['ok'])->toBeFalse();
    expect($result['reason'])->toContain('database.sql');

    $backup->refresh();
    expect($backup->integrity_verified)->toBeNull();
    expect($backup->integrity_checked_at)->toBeNull();
});

test('verify fails for a full backup missing the storage/ directory', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    $backup = makeEncryptedTestBackupArchive($disk, BackupType::Full, includeStorageDir: false);

    $result = app(BackupInspectionService::class)->verify($backup);

    expect($result['ok'])->toBeFalse();

    $backup->refresh();
    expect($backup->integrity_verified)->toBeNull();
});

test('verify deletes all temporary files after running, on success and on failure', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');

    $before = tmpBackupDirSnapshot();

    $ok = makeEncryptedTestBackupArchive($disk, BackupType::Database);
    app(BackupInspectionService::class)->verify($ok);

    $broken = makeEncryptedTestBackupArchive($disk, BackupType::Database, includeManifest: false);
    app(BackupInspectionService::class)->verify($broken);

    $after = tmpBackupDirSnapshot();
    expect($after->diff($before))->toBeEmpty();
});

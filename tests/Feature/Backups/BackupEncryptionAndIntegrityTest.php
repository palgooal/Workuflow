<?php

use App\Models\Backup;
use App\Services\Backup\BackupEncryptor;
use App\Services\Backup\SystemBackupService;
use App\Support\Enums\BackupStatus;
use App\Support\Enums\BackupType;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    // ⚠️ المفتاح يُقرأ الآن عبر config('backups.system_backup.encryption_key')
    // (مُحمَّل مرة واحدة من env() داخل config/backups.php)، وليس عبر env() مباشرة
    // في BackupEncryptor — لأن env() المباشر خارج ملفات config يفشل بعد
    // php artisan config:cache. لذا نتحكم بالمفتاح هنا عبر config()->set() لمحاكاة
    // القيمة الفعلية التي يقرأها الكود، بدل putenv() التي لم تعد تؤثر على النتيجة.
    $this->originalConfigKey = config('backups.system_backup.encryption_key');
});

afterEach(function () {
    config(['backups.system_backup.encryption_key' => $this->originalConfigKey]);
});

// ==================== BackupEncryptor: تشفير/فك تشفير + checksum ====================

test('encrypted file can be decrypted back to the original content', function () {
    config(['backups.system_backup.encryption_key' => base64_encode(random_bytes(32))]);

    $encryptor = app(BackupEncryptor::class);

    $source = sys_get_temp_dir().'/plain-'.uniqid().'.txt';
    $enc    = sys_get_temp_dir().'/enc-'.uniqid().'.bin';
    $dec    = sys_get_temp_dir().'/dec-'.uniqid().'.txt';

    file_put_contents($source, 'محتوى سري للاختبار فقط — 1234567890');

    $encryptor->encryptFile($source, $enc);
    expect(file_get_contents($enc))->not->toBe(file_get_contents($source));

    $encryptor->decryptFile($enc, $dec);
    expect(file_get_contents($dec))->toBe(file_get_contents($source));

    @unlink($source); @unlink($enc); @unlink($dec);
});

test('checksum is deterministic for identical content', function () {
    $encryptor = app(BackupEncryptor::class);

    $a = sys_get_temp_dir().'/a-'.uniqid().'.bin';
    $b = sys_get_temp_dir().'/b-'.uniqid().'.bin';
    file_put_contents($a, 'same-content');
    file_put_contents($b, 'same-content');

    expect($encryptor->checksum($a))->toBe($encryptor->checksum($b));

    @unlink($a); @unlink($b);
});

test('hasKey reports true when the key is present in config', function () {
    config(['backups.system_backup.encryption_key' => base64_encode(random_bytes(32))]);

    expect(app(BackupEncryptor::class)->hasKey())->toBeTrue();
});

test('hasKey reports false when the key is absent from config', function () {
    config(['backups.system_backup.encryption_key' => null]);

    expect(app(BackupEncryptor::class)->hasKey())->toBeFalse();
});

test('encrypting without a key throws instead of writing plaintext', function () {
    config(['backups.system_backup.encryption_key' => null]);

    $encryptor = app(BackupEncryptor::class);
    $source = sys_get_temp_dir().'/plain-'.uniqid().'.txt';
    file_put_contents($source, 'secret');

    expect(fn () => $encryptor->encryptFile($source, $source.'.enc'))->toThrow(RuntimeException::class);

    @unlink($source);
});

// ==================== SystemBackupService: فشل واضح مع سبب مسجَّل ====================

test('backup fails with a clear reason when encryption key is missing', function () {
    config(['backups.system_backup.encryption_key' => null]);

    $backup = Backup::create([
        'name' => 'no-key-test', 'type' => BackupType::Database, 'status' => BackupStatus::Pending,
    ]);

    app(SystemBackupService::class)->run($backup);

    $backup->refresh();
    expect($backup->status)->toBe(BackupStatus::Failed);
    expect($backup->error_message)->toContain('BACKUP_ENCRYPTION_KEY');
});

test('backup fails clearly when the database driver is not mysql', function () {
    // ⚠️ ترتيب حرِج ومقصود:
    // 1) ننشئ Backup أولاً على اتصال الاختبارات الحقيقي (mysql) — لو بدّلنا
    //    database.default إلى sqlite قبل create()، فإن الكتابة نفسها ستفشل على
    //    اتصال sqlite غير موجود، قبل أن تصل الخدمة المُختبَرة أصلاً.
    // 2) نحفظ إعدادات الاتصال الأصلية، ثم نبدّل إلى sqlite لمحاكاة بيئة يرفضها
    //    SystemBackupService (يدعم mysqldump فقط).
    // 3) نستدعي الخدمة ونتحقق من رمي RuntimeException.
    // 4) قبل $backup->refresh() نُعيد الاتصال إلى mysql — refresh() يقرأ عبر
    //    database.default الحالي، ولو بقي sqlite فسيفشل هو الآخر.
    // 5) finally يعيد كل القيم مرة أخرى (بلا ضرر إن كانت أُعيدت مسبقاً) لضمان
    //    عدم تسرّبها لبقية الاختبارات مهما حدث.
    config(['backups.system_backup.encryption_key' => base64_encode(random_bytes(32))]);
    Storage::fake('backups');

    $backup = Backup::create([
        'name' => 'sqlite-rejection-test', 'type' => BackupType::Database, 'status' => BackupStatus::Pending,
    ]);

    $originalDefaultConnection = config('database.default');
    $originalSqliteDriver      = config('database.connections.sqlite.driver');

    config([
        'database.default'                   => 'sqlite',
        'database.connections.sqlite.driver' => 'sqlite',
    ]);

    try {
        expect(fn () => app(SystemBackupService::class)->run($backup))->toThrow(RuntimeException::class);

        config([
            'database.default'                   => $originalDefaultConnection,
            'database.connections.sqlite.driver' => $originalSqliteDriver,
        ]);

        $backup->refresh();
        expect($backup->status)->toBe(BackupStatus::Failed);
        expect($backup->error_message)->not->toBeEmpty();
    } finally {
        config([
            'database.default'                   => $originalDefaultConnection,
            'database.connections.sqlite.driver' => $originalSqliteDriver,
        ]);
    }
});

// ==================== Backup: duration_seconds (يجب ألا تكون سالبة أبداً) ====================
// راجع Backup::calculateDurationSeconds() — بدون absolute: true، بعض إصدارات Carbon
// (3.x مع Laravel 12) قد تُرجع فرقاً سالباً عندما تكون started_at/completed_at
// قريبتين جداً من بعضهما، وعمود duration_seconds من نوع unsignedInteger فيفشل التحديث.

test('markCompleted stores a non-negative integer duration_seconds', function () {
    $backup = Backup::create([
        'name' => 'duration-completed-test', 'type' => BackupType::Database, 'status' => BackupStatus::Pending,
    ]);

    $backup->markRunning();
    $backup->markCompleted(
        disk: 'backups',
        path: 'system-backups/fake.zip.enc',
        sizeBytes: 100,
        checksum: 'fake-checksum',
        encrypted: true,
    );

    $backup->refresh();
    expect($backup->status)->toBe(BackupStatus::Completed);
    expect($backup->duration_seconds)->toBeInt();
    expect($backup->duration_seconds)->toBeGreaterThanOrEqual(0);
});

test('markFailed stores a non-negative integer duration_seconds and never leaves status running', function () {
    $backup = Backup::create([
        'name' => 'duration-failed-test', 'type' => BackupType::Database, 'status' => BackupStatus::Pending,
    ]);

    $backup->markRunning();
    $backup->markFailed('فشل تجريبي لاختبار duration_seconds');

    $backup->refresh();
    expect($backup->status)->toBe(BackupStatus::Failed);
    expect($backup->status)->not->toBe(BackupStatus::Running);
    expect($backup->duration_seconds)->toBeInt();
    expect($backup->duration_seconds)->toBeGreaterThanOrEqual(0);
});

// ==================== فحص السلامة (verifyIntegrity) ====================

test('verifyIntegrity returns true when checksum matches stored file', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    $path = 'system-backups/verify-me.zip.enc';

    Storage::disk($disk)->put($path, 'archive-bytes');
    $checksum = hash('sha256', 'archive-bytes');

    $backup = Backup::create([
        'name' => 'verify-test', 'type' => BackupType::Database, 'status' => BackupStatus::Completed,
        'disk' => $disk, 'path' => $path, 'checksum' => $checksum,
    ]);

    expect(app(SystemBackupService::class)->verifyIntegrity($backup))->toBeTrue();
    expect($backup->fresh()->integrity_verified)->toBeTrue();
});

test('verifyIntegrity returns false when the file was tampered with', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    $path = 'system-backups/tampered.zip.enc';

    Storage::disk($disk)->put($path, 'archive-bytes');

    $backup = Backup::create([
        'name' => 'tampered-test', 'type' => BackupType::Database, 'status' => BackupStatus::Completed,
        'disk' => $disk, 'path' => $path, 'checksum' => 'wrong-checksum-value',
    ]);

    expect(app(SystemBackupService::class)->verifyIntegrity($backup))->toBeFalse();
    expect($backup->fresh()->integrity_verified)->toBeFalse();
});

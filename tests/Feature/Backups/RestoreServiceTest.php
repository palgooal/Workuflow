<?php

use App\Exceptions\Backup\BackupIntegrityException;
use App\Exceptions\Backup\BackupManifestException;
use App\Exceptions\Backup\BackupNotFoundException;
use App\Exceptions\Backup\BackupRestoreException;
use App\Services\Backup\DatabaseRestoreService;
use App\Services\Backup\FilesRestoreService;
use App\Services\Backup\RestoreService;
use App\Support\Enums\BackupType;
use Illuminate\Support\Facades\Storage;

// makeEncryptedTestBackupArchive() وmakeZipSlipTestBackupArchive() وrestoreTempDirSnapshot()
// موحّدة في tests/Helpers.php (محمَّلة من tests/Pest.php) — الأولى تبني أرشيفاً
// مشفَّراً حقيقياً عبر BackupEncryptor الحالية (بدون أي تعديل عليها)، الثانية
// تبني نفس الشيء مع مدخل ZIP خبيث (Zip Slip)، والثالثة تلتقط لقطة من محتوى
// storage/app/backups/restore-temp للتحقق من التنظيف الكامل بعد run().
//
// ⚠️ هذا الملف يركّز حصراً على مسؤولية RestoreService كمنسِّق: checksum،
// manifest، الاستخراج الآمن (Zip Slip)، تنظيف الملفات المؤقتة، والتفويض
// الصحيح. منذ أن أصبح DatabaseRestoreService::restore() ينفّذ استعادة حقيقية
// فعلية (نسخة طارئة + Maintenance Mode + استيراد mysql)، تُموَّه (mock) هنا في
// أي اختبار يصل لتلك الخطوة — لا تُشغَّل هنا أبداً بشكل حقيقي، ولا تُنشَأ نسخة
// طارئة حقيقية، ولا يدخل التطبيق في Maintenance Mode. تغطية سيناريوهات
// DatabaseRestoreService الفعلية (CLI، الاستيراد، STDERR، Maintenance Mode)
// موجودة حصراً في tests/Feature/Backups/DatabaseRestoreServiceTest.php.

test('run throws BackupNotFoundException when the backup record does not exist', function () {
    Storage::fake('backups');

    expect(fn () => app(RestoreService::class)->run('01JNONEXISTENTULID000000000'))
        ->toThrow(BackupNotFoundException::class);
});

test('run throws BackupIntegrityException when the archive file does not exist on disk', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    $backup = makeEncryptedTestBackupArchive($disk, BackupType::Database);

    Storage::disk($disk)->delete($backup->path);

    expect(fn () => app(RestoreService::class)->run($backup->id))
        ->toThrow(BackupIntegrityException::class);
});

test('run throws BackupIntegrityException when checksum does not match', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    $backup = makeEncryptedTestBackupArchive($disk, BackupType::Database);
    $backup->update(['checksum' => 'tampered-checksum-value']);

    expect(fn () => app(RestoreService::class)->run($backup->id))
        ->toThrow(BackupIntegrityException::class);
});

test('run throws BackupManifestException when manifest.json is missing from the archive', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    $backup = makeEncryptedTestBackupArchive($disk, BackupType::Database, includeManifest: false);

    expect(fn () => app(RestoreService::class)->run($backup->id))
        ->toThrow(BackupManifestException::class);
});

test('run throws BackupManifestException when database.sql is missing from the archive', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    $backup = makeEncryptedTestBackupArchive($disk, BackupType::Database, includeDatabaseSql: false);

    expect(fn () => app(RestoreService::class)->run($backup->id))
        ->toThrow(BackupManifestException::class);
});

test('run throws BackupManifestException for a full backup missing the storage/ directory', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    $backup = makeEncryptedTestBackupArchive($disk, BackupType::Full, includeStorageDir: false);

    expect(fn () => app(RestoreService::class)->run($backup->id))
        ->toThrow(BackupManifestException::class);
});

test('run succeeds end to end and delegates the database restore exactly once with the correct path', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    $backup = makeEncryptedTestBackupArchive($disk, BackupType::Database);

    // DatabaseRestoreService::restore() مموَّهة عمداً — تنفيذها الحقيقي مدمِّر
    // (نسخة طارئة + Maintenance Mode + mysql import) ويُغطَّى بالكامل في
    // DatabaseRestoreServiceTest.php. هنا نتحقق فقط من أن RestoreService
    // تفوّض إليها مرة واحدة بالضبط، بالمسار الصحيح لملف database.sql
    // المستخرَج فعلياً داخل restore-temp.
    $this->mock(DatabaseRestoreService::class, function ($mock) {
        $mock->shouldReceive('restore')
            ->once()
            ->withArgs(fn (string $sqlPath) => str_ends_with($sqlPath, '/extracted/database.sql'))
            ->andReturnNull();
    });

    $result = app(RestoreService::class)->run($backup->id);

    expect($result['ok'])->toBeTrue();
    expect($result['backup_id'])->toBe($backup->id);
    expect($result['manifest']['type'])->toBe('database');
    expect($result['restore_id'])->not->toBeEmpty();
});

test('run deletes the restore-temp work directory after a successful run', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    $backup = makeEncryptedTestBackupArchive($disk, BackupType::Database);

    $this->mock(DatabaseRestoreService::class, function ($mock) {
        $mock->shouldReceive('restore')->once()->andReturnNull();
    });

    $before = restoreTempDirSnapshot();

    app(RestoreService::class)->run($backup->id);

    $after = restoreTempDirSnapshot();
    expect($after->diff($before))->toBeEmpty();
});

test('run deletes the restore-temp work directory after a failed run', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    $backup = makeEncryptedTestBackupArchive($disk, BackupType::Database, includeManifest: false);

    $before = restoreTempDirSnapshot();

    try {
        app(RestoreService::class)->run($backup->id);
    } catch (\Throwable) {
        // متوقَّع — نتحقق فقط من التنظيف أدناه. لا حاجة لتمويه
        // DatabaseRestoreService هنا لأن الفشل يحدث قبل الوصول لخطوة التفويض.
    }

    $after = restoreTempDirSnapshot();
    expect($after->diff($before))->toBeEmpty();
});

test('run delegates to DatabaseRestoreService before FilesRestoreService, in that exact order', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    // Full: يحتوي storage/app/public/test.txt افتراضياً — يصل فعلياً لخطوتَي
    // التفويض معاً (وليس Database التي كانت تكفي للاختبارات الأخرى أعلاه).
    $backup = makeEncryptedTestBackupArchive($disk, BackupType::Full);

    // كلتا الخدمتين مموَّهتان بالكامل — لا mysql حقيقي، ولا لمس فعلي
    // لـstorage/app الحقيقي؛ الهدف هنا فقط إثبات ترتيب الاستدعاء.
    $this->mock(DatabaseRestoreService::class, function ($mock) {
        $mock->shouldReceive('restore')->once()->ordered()->andReturnNull();
    });

    $this->mock(FilesRestoreService::class, function ($mock) {
        $mock->shouldReceive('restore')->once()->ordered()->andReturnNull();
    });

    $result = app(RestoreService::class)->run($backup->id);

    expect($result['ok'])->toBeTrue();
});

test('run rejects a Zip Slip attempt and does not write anything outside restore-temp', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    $backup = makeZipSlipTestBackupArchive($disk);

    $before = restoreTempDirSnapshot();

    expect(fn () => app(RestoreService::class)->run($backup->id))
        ->toThrow(BackupRestoreException::class);

    // لا يوجد أي أثر لملف الخروج الخبيث فوق storage/app/backups/
    $escapedFile = storage_path('app/backups/evil-zip-slip.txt');
    expect(is_file($escapedFile))->toBeFalse();

    // ولا يوجد أي أثر لملف الخروج الخبيث فوق storage/app/ (مستوى أعلى)
    $escapedFileAppLevel = storage_path('app/evil-zip-slip.txt');
    expect(is_file($escapedFileAppLevel))->toBeFalse();

    $after = restoreTempDirSnapshot();
    expect($after->diff($before))->toBeEmpty();
});

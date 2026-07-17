<?php

// --------------------------------------------------------------------------
// Test Helpers — نقطة واحدة موحّدة لكل الدوال المساعدة العامة (Pest)
// --------------------------------------------------------------------------
//
// ⚠️ لا تُعرَّف أي دالة عامة (function foo() {}) داخل ملفات الاختبار مباشرة.
// Pest يحمّل جميع ملفات الاختبارات (كل ملفات .php داخل مجلد tests ومجلداته
// الفرعية) في نفس نطاق PHP العام (global scope)، فأي دالتين بنفس الاسم في
// ملفين مختلفين تسبّبان:
//   Fatal error: Cannot redeclare foo()
// وتوقف كامل test suite عن العمل. هذا الملف يُحمَّل مرة واحدة من tests/Pest.php
// ويُتاح لكل الاختبارات — أضف أي Helper جديد هنا فقط.

use App\Models\Backup;
use App\Models\DataExportRequest;
use App\Models\User;
use App\Support\Enums\BackupType;
use App\Support\Enums\DataExportStatus;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

// ==================== صلاحيات ====================

/**
 * ينشئ مستخدماً بدور super_admin (لاختبارات Filament / لوحة الأدمن).
 */
function makeSuperAdmin(): User
{
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    $user = User::factory()->create();
    $user->assignRole('super_admin');

    return $user;
}

// ==================== تصدير بيانات المستخدم (Data Export) ====================

/**
 * يفكّ ضغط أرشيف تصدير مستخدم من disk اختباري (Storage::fake) إلى مجلد مؤقت،
 * ويعيد المسار لقراءة محتوياته (data/*.csv, data.json, README.md).
 */
function extractExportZip(string $disk, string $storagePath): string
{
    $absoluteZipPath = Storage::disk($disk)->path($storagePath);
    $extractTo = sys_get_temp_dir().'/export-test-'.uniqid();

    $zip = new ZipArchive();
    $zip->open($absoluteZipPath);
    $zip->extractTo($extractTo);
    $zip->close();

    return $extractTo;
}

/**
 * ينشئ سجل DataExportRequest مكتملاً (status=completed) مع ملف ZIP وهمي فعلي
 * على disk اختباري، جاهز لاختبار مسارات التنزيل.
 */
function makeCompletedExport(User $user, string $disk): DataExportRequest
{
    $path = 'user-data-exports/'.$user->id.'.zip';
    Storage::disk($disk)->put($path, 'fake-zip-content');

    return DataExportRequest::create([
        'user_id'      => $user->id,
        'status'       => DataExportStatus::Completed,
        'file_path'    => $path,
        'file_size'    => 17,
        'requested_at' => now()->subMinutes(5),
        'completed_at' => now(),
        'expires_at'   => now()->addHours(72),
    ]);
}

// ==================== النسخ الاحتياطية للنظام (System Backups) ====================

/**
 * ينشئ سجل Backup مكتملاً (status=completed) مع ملف وهمي فعلي على disk
 * اختباري، جاهز لاختبار الاحتفاظ (Retention) والتنزيل وفحص السلامة.
 */
function makeCompletedBackup(string $disk, BackupType $type, \Carbon\Carbon $completedAt): Backup
{
    $path = 'system-backups/'.uniqid().'.zip.enc';
    Storage::disk($disk)->put($path, 'content');

    return Backup::create([
        'name'         => 'backup-'.uniqid(),
        'type'         => $type,
        'status'       => \App\Support\Enums\BackupStatus::Completed,
        'disk'         => $disk,
        'path'         => $path,
        'checksum'     => hash('sha256', 'content'),
        'completed_at' => $completedAt,
    ]);
}

/**
 * نفس makeCompletedBackup() لكن مع integrity_verified=true فوراً — تُستخدَم في
 * اختبارات واجهة Filament لزر "استعادة النسخة" (يظهر فقط عند
 * status=completed وintegrity_verified=true معاً).
 */
function makeRestorableBackup(string $disk, BackupType $type = BackupType::Database): Backup
{
    $backup = makeCompletedBackup($disk, $type, now());

    $backup->update([
        'integrity_verified'   => true,
        'integrity_checked_at' => now(),
    ]);

    return $backup;
}

/**
 * ينشئ أرشيف نسخة احتياطية مشفَّر **حقيقياً فعلياً** (عبر BackupEncryptor
 * الحالية بدون أي تعديل عليها) على disk اختباري، مع سجل Backup مكتمل يشير
 * إليه — لاختبار BackupInspectionService (قراءة manifest + فحص السلامة)
 * ضد أرشيف صالح فعلياً وليس محتوى وهمي بسيط.
 *
 * @param bool $includeManifest هل يحتوي الأرشيف على manifest.json
 * @param bool $includeDatabaseSql هل يحتوي الأرشيف على database.sql
 * @param bool|null $includeStorageDir هل يحتوي الأرشيف على مجلد storage/ (افتراضياً: فقط لنوع Full)
 */
function makeEncryptedTestBackupArchive(
    string $disk,
    \App\Support\Enums\BackupType $type = \App\Support\Enums\BackupType::Database,
    bool $includeManifest = true,
    bool $includeDatabaseSql = true,
    ?bool $includeStorageDir = null,
): Backup {
    config(['backups.system_backup.encryption_key' => base64_encode(random_bytes(32))]);

    $workDir = sys_get_temp_dir().'/backup-inspection-test-'.uniqid();
    mkdir($workDir, 0777, true);

    $zipPath = $workDir.'/archive.zip';
    $zip = new ZipArchive();
    $zip->open($zipPath, ZipArchive::CREATE);

    if ($includeManifest) {
        $zip->addFromString('manifest.json', json_encode([
            'name'          => 'inspection-test',
            'type'          => $type->value,
            'created_at'    => now()->toIso8601String(),
            'app_env'       => 'testing',
            'laravel'       => app()->version(),
            'database'      => ['driver' => 'mysql', 'dump_file' => 'database.sql'],
            'storage_paths' => ['app/private/client-attachments'],
        ]));
    }

    if ($includeDatabaseSql) {
        $zip->addFromString('database.sql', "-- fake dump for tests\nSELECT 1;\n");
    }

    $includeStorageDir ??= $type === \App\Support\Enums\BackupType::Full;
    if ($includeStorageDir) {
        $zip->addFromString('storage/app/public/test.txt', 'fake file content for tests');
    }

    $zip->close();

    $encryptor = app(\App\Services\Backup\BackupEncryptor::class);
    $encPath = $workDir.'/archive.zip.enc';
    $encryptor->encryptFile($zipPath, $encPath);
    $checksum = $encryptor->checksum($encPath);

    $path = 'system-backups/'.uniqid().'.zip.enc';
    Storage::disk($disk)->put($path, file_get_contents($encPath));

    @unlink($zipPath);
    @unlink($encPath);
    @rmdir($workDir);

    return Backup::create([
        'name'         => 'inspection-test-'.uniqid(),
        'type'         => $type,
        'status'       => \App\Support\Enums\BackupStatus::Completed,
        'disk'         => $disk,
        'path'         => $path,
        'checksum'     => $checksum,
        'encrypted'    => true,
        'started_at'   => now()->subMinutes(2),
        'completed_at' => now(),
    ]);
}

/**
 * لقطة بأسماء كل الملفات الموجودة حالياً داخل storage/app/private/tmp — تُستخدَم
 * في اختبارات BackupInspectionService للتأكد أن الملفات المؤقتة (فك تشفير/فك
 * ضغط مؤقت) تُحذَف فعلياً بعد كل عملية قراءة/فحص، بمقارنة "قبل" و"بعد".
 */
function tmpBackupDirSnapshot(): \Illuminate\Support\Collection
{
    $dir = storage_path('app/private/tmp');
    \Illuminate\Support\Facades\File::ensureDirectoryExists($dir);

    return collect(\Illuminate\Support\Facades\File::allFiles($dir))->map(fn ($f) => $f->getPathname());
}

// ==================== Restore Engine v1 (RestoreService) ====================

/**
 * لقطة بأسماء كل الملفات الموجودة حالياً داخل storage/app/backups/restore-temp
 * — مجلد العمل المؤقت الخاص بـ RestoreService (مختلف عن storage/app/private/tmp
 * الذي تستخدمه BackupInspectionService). تُستخدَم للتأكد أن RestoreService
 * تحذف مجلد عملها بالكامل في finally، سواء نجحت أو فشلت العملية.
 */
function restoreTempDirSnapshot(): \Illuminate\Support\Collection
{
    $dir = storage_path('app/backups/restore-temp');
    \Illuminate\Support\Facades\File::ensureDirectoryExists($dir);

    return collect(\Illuminate\Support\Facades\File::allFiles($dir))->map(fn ($f) => $f->getPathname());
}

/**
 * ينشئ أرشيف نسخة احتياطية مشفَّر **حقيقياً فعلياً** (نفس أسلوب
 * makeEncryptedTestBackupArchive()) يحتوي manifest.json وdatabase.sql
 * صالحَين، بالإضافة إلى مدخل ZIP خبيث يحاول الخروج عن مجلد الاستخراج
 * (Zip Slip / Path Traversal) — لاختبار أن RestoreService::extractSafely()
 * ترفض العملية كاملةً قبل كتابة أي شيء خارج مجلد العمل المؤقت.
 */
function makeZipSlipTestBackupArchive(string $disk): Backup
{
    config(['backups.system_backup.encryption_key' => base64_encode(random_bytes(32))]);

    $workDir = sys_get_temp_dir().'/backup-zipslip-test-'.uniqid();
    mkdir($workDir, 0777, true);

    $zipPath = $workDir.'/archive.zip';
    $zip = new ZipArchive();
    $zip->open($zipPath, ZipArchive::CREATE);

    $zip->addFromString('manifest.json', json_encode([
        'name'       => 'zipslip-test',
        'type'       => BackupType::Database->value,
        'created_at' => now()->toIso8601String(),
        'app_env'    => 'testing',
        'laravel'    => app()->version(),
        'database'   => ['driver' => 'mysql', 'dump_file' => 'database.sql'],
    ]));
    $zip->addFromString('database.sql', "-- fake dump for tests\nSELECT 1;\n");
    // مدخل خبيث: يحاول الكتابة خارج مجلد الاستخراج عبر ../..
    $zip->addFromString('../../evil-zip-slip.txt', 'malicious content that must never be written outside restore-temp');

    $zip->close();

    $encryptor = app(\App\Services\Backup\BackupEncryptor::class);
    $encPath = $workDir.'/archive.zip.enc';
    $encryptor->encryptFile($zipPath, $encPath);
    $checksum = $encryptor->checksum($encPath);

    $path = 'system-backups/'.uniqid().'.zip.enc';
    Storage::disk($disk)->put($path, file_get_contents($encPath));

    @unlink($zipPath);
    @unlink($encPath);
    @rmdir($workDir);

    return Backup::create([
        'name'         => 'zipslip-test-'.uniqid(),
        'type'         => BackupType::Database,
        'status'       => \App\Support\Enums\BackupStatus::Completed,
        'disk'         => $disk,
        'path'         => $path,
        'checksum'     => $checksum,
        'encrypted'    => true,
        'started_at'   => now()->subMinutes(2),
        'completed_at' => now(),
    ]);
}

// ==================== استعادة قاعدة البيانات الفعلية (Restore Engine — المرحلة الثانية) ====================

/**
 * ينشئ ملف database.sql مؤقتاً صالحاً (موجود، قابل للقراءة، غير فارغ) خارج
 * أي مجلد إدارته الاختبارات — لاختبار DatabaseRestoreService::restore()
 * مباشرة بمعزل عن RestoreService (بدون الحاجة لأرشيف ZIP كامل). المستدعي
 * مسؤول عن حذف الملف بعد الاختبار (unlink)، لأن هذا الملف خارج أي مجلد
 * مؤقت يُنظَّف تلقائياً.
 */
function makeValidSqlFileForRestoreTests(): string
{
    $path = sys_get_temp_dir().'/db-restore-test-'.uniqid().'.sql';
    file_put_contents($path, "-- fake dump for tests\nSELECT 1;\n");

    return $path;
}

/**
 * يُشغِّل $callback بينما backups.restore.connection يشير إلى اتصال mysql
 * وهمي منفصل تماماً عن database.default الحقيقي (sqlite :memory: لاختبارات
 * Laravel) — يسمح لـ DatabaseRestoreService::resolveMysqlConnection() بتجاوز
 * فحص "driver != mysql" والوصول لسيناريوهات أعمق (CLI/نسخة طارئة/استيراد)
 * دون أي محاولة فتح اتصال PDO فعلي بهذا الاسم (DatabaseRestoreService تقرأ
 * فقط مصفوفة الإعدادات، لا تتصل به أبداً).
 *
 * يعيد قيمتَي backups.restore.connection وdatabase.connections.restore_mysql_test
 * إلى ما كانتا عليه داخل finally — بغض النظر عن نجاح $callback أو فشله أو
 * رميه استثناءً — لمنع أي تسرّب إعداد بين الاختبارات.
 */
function withFakeMysqlRestoreConnection(callable $callback): mixed
{
    $originalRestoreConnection = config('backups.restore.connection');
    $originalFakeConnectionConfig = config('database.connections.restore_mysql_test');

    config()->set('database.connections.restore_mysql_test', [
        'driver'   => 'mysql',
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'database' => 'restore_testing',
        'username' => 'restore_user',
        'password' => 'restore_password',
    ]);
    config()->set('backups.restore.connection', 'restore_mysql_test');

    try {
        return $callback();
    } finally {
        config()->set('backups.restore.connection', $originalRestoreConnection);
        config()->set('database.connections.restore_mysql_test', $originalFakeConnectionConfig);
    }
}

// ==================== استعادة ملفات storage/app الفعلية (Restore Engine — المرحلة الثالثة) ====================

/**
 * يُشغِّل $callback بينما backups.restore.storage_app_path يشير إلى مجلد مؤقت
 * معزول تماماً عن storage/app الحقيقي للمشروع — يمرَّر مسار هذا المجلد
 * للـ$callback مباشرة. يحذف المجلد المؤقت (ومجلدَي .__old/.__restore
 * الجانبيَّين إن بقيا بالخطأ) ويعيد الإعداد لأصله داخل finally دائماً، بغض
 * النظر عن نجاح $callback أو فشله — لضمان عدم لمس storage/app الحقيقي أبداً
 * أثناء اختبارات FilesRestoreService، وعدم تسرّب أي حالة بين الاختبارات.
 */
function withFakeStorageAppDir(callable $callback): mixed
{
    $fakeAppDir = sys_get_temp_dir().'/files-restore-test-app-'.uniqid();
    mkdir($fakeAppDir, 0777, true);

    $original = config('backups.restore.storage_app_path');
    config()->set('backups.restore.storage_app_path', $fakeAppDir);

    try {
        return $callback($fakeAppDir);
    } finally {
        config()->set('backups.restore.storage_app_path', $original);

        foreach ([$fakeAppDir, $fakeAppDir.'.__old', $fakeAppDir.'.__restore'] as $dir) {
            if (is_dir($dir)) {
                \Illuminate\Support\Facades\File::deleteDirectory($dir);
            }
        }
    }
}

/**
 * ينشئ شجرة مجلد مستخرَج وهمية بالبنية storage/app/... التي تتوقعها
 * FilesRestoreService::restore() فعلياً ($extractDir.'/storage/app') — بمحتوى
 * $files (مفتاح = مسار نسبي، قيمة = محتوى الملف). المستدعي مسؤول عن حذف
 * المجلد المُعاد بعد الاختبار (File::deleteDirectory)، لأنه خارج أي تنظيف تلقائي.
 *
 * @param array<string,string> $files
 */
function makeFakeExtractedStorageAppTree(array $files): string
{
    $extractDir = sys_get_temp_dir().'/files-restore-source-'.uniqid();
    $appDir = $extractDir.'/storage/app';
    mkdir($appDir, 0777, true);

    foreach ($files as $relativePath => $content) {
        $fullPath = $appDir.'/'.$relativePath;
        \Illuminate\Support\Facades\File::ensureDirectoryExists(dirname($fullPath));
        file_put_contents($fullPath, $content);
    }

    return $extractDir;
}

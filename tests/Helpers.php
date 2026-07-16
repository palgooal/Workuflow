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

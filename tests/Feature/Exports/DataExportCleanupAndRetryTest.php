<?php

use App\Jobs\Export\ExportUserDataJob;
use App\Models\DataExportRequest;
use App\Models\User;
use App\Support\Enums\DataExportStatus;
use Illuminate\Support\Facades\Storage;

// ==================== Cleanup: حذف الملفات المنتهية ====================

test('expired data export files are deleted and marked expired', function () {
    Storage::fake('exports');
    $disk = config('backups.user_export.disk');

    $user = User::factory()->create();
    $path = 'user-data-exports/expired.zip';
    Storage::disk($disk)->put($path, 'content');

    $export = DataExportRequest::create([
        'user_id'      => $user->id,
        'status'       => DataExportStatus::Completed,
        'file_path'    => $path,
        'requested_at' => now()->subDays(4),
        'completed_at' => now()->subDays(4),
        'expires_at'   => now()->subHour(), // منتهية بالفعل
    ]);

    $this->artisan('exports:purge-expired')->assertSuccessful();

    Storage::disk($disk)->assertMissing($path);
    expect($export->fresh()->status)->toBe(DataExportStatus::Expired);
});

test('non-expired exports are left untouched by cleanup', function () {
    Storage::fake('exports');
    $disk = config('backups.user_export.disk');

    $user = User::factory()->create();
    $path = 'user-data-exports/still-valid.zip';
    Storage::disk($disk)->put($path, 'content');

    $export = DataExportRequest::create([
        'user_id'      => $user->id,
        'status'       => DataExportStatus::Completed,
        'file_path'    => $path,
        'requested_at' => now(),
        'completed_at' => now(),
        'expires_at'   => now()->addHours(72),
    ]);

    $this->artisan('exports:purge-expired');

    Storage::disk($disk)->assertExists($path);
    expect($export->fresh()->status)->toBe(DataExportStatus::Completed);
});

// ==================== Queue Job قابل لإعادة المحاولة بأمان (Idempotency) ====================

test('export job skips already completed requests without side effects', function () {
    Storage::fake('exports');

    $user = User::factory()->create();
    $export = DataExportRequest::create([
        'user_id'      => $user->id,
        'status'       => DataExportStatus::Completed,
        'file_path'    => 'user-data-exports/already-done.zip',
        'requested_at' => now()->subHour(),
        'completed_at' => now()->subHour(),
        'expires_at'   => now()->addDay(),
    ]);

    // تشغيل الـ Job يدوياً (يُحاكي إعادة محاولة من الـ queue بعد نجاح فعلي)
    app(ExportUserDataJob::class, ['dataExportRequestId' => $export->id])
        ->handle(app(\App\Services\DataExport\UserDataExportService::class));

    // لا تغيير — لم يُعَد بناء الملف ولم تتغيّر الحالة
    expect($export->fresh()->file_path)->toBe('user-data-exports/already-done.zip');
    expect($export->fresh()->status)->toBe(DataExportStatus::Completed);
});

test('export job marks request as failed with a reason when it throws', function () {
    Storage::fake('exports');

    $user = User::factory()->create();
    $export = DataExportRequest::create([
        'user_id'      => $user->id,
        'status'       => DataExportStatus::Pending,
        'requested_at' => now(),
    ]);

    // نحذف المستخدم من الذاكرة بعد الإنشاء لمحاكاة حالة فشل داخل handle()
    // عبر moc بسيط: نستدعي failed() مباشرة كما يفعل Laravel بعد استنفاد المحاولات
    $job = new ExportUserDataJob($export->id);
    $job->failed(new \RuntimeException('فشل اصطناعي للاختبار'));

    expect($export->fresh()->status)->toBe(DataExportStatus::Failed);
    expect($export->fresh()->failure_reason)->toBe('فشل اصطناعي للاختبار');
});

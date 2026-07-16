<?php

use App\Http\Controllers\Account\DataExportController;
use App\Models\DataExportRequest;
use App\Models\User;
use App\Support\Enums\DataExportStatus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

// makeCompletedExport() موحّدة الآن في tests/Helpers.php (محمَّلة من tests/Pest.php)

// ==================== منع تنزيل نسخة مستخدم آخر ====================

test('user cannot download another users export via signed url', function () {
    Storage::fake('exports');
    $disk = config('backups.user_export.disk');

    $owner   = User::factory()->create();
    $stranger = User::factory()->create();

    $export = makeCompletedExport($owner, $disk);
    $url = DataExportController::signedDownloadUrl($export);

    $this->actingAs($stranger)
        ->get($url)
        ->assertNotFound();
});

test('owner can download their own completed export', function () {
    Storage::fake('exports');
    $disk = config('backups.user_export.disk');

    $owner = User::factory()->create();
    $export = makeCompletedExport($owner, $disk);
    $url = DataExportController::signedDownloadUrl($export);

    $this->actingAs($owner)
        ->get($url)
        ->assertOk();
});

test('guest cannot download even with a valid signed url', function () {
    Storage::fake('exports');
    $disk = config('backups.user_export.disk');

    $owner = User::factory()->create();
    $export = makeCompletedExport($owner, $disk);
    $url = DataExportController::signedDownloadUrl($export);

    $this->get($url)->assertRedirect(route('login'));
});

// ==================== انتهاء صلاحية الرابط ====================

test('expired signed url is rejected', function () {
    Storage::fake('exports');
    $disk = config('backups.user_export.disk');

    $owner = User::factory()->create();
    $export = makeCompletedExport($owner, $disk);

    // نبني رابطاً موقّعاً منتهي الصلاحية بالفعل
    $expiredUrl = URL::temporarySignedRoute(
        'data-export.download',
        now()->subMinute(),
        ['dataExportRequest' => $export->id]
    );

    $this->actingAs($owner)
        ->get($expiredUrl)
        ->assertForbidden();
});

test('download is rejected once the export request itself has expired', function () {
    Storage::fake('exports');
    $disk = config('backups.user_export.disk');

    $owner = User::factory()->create();
    $export = makeCompletedExport($owner, $disk);
    $export->update(['expires_at' => now()->subHour()]); // انتهت صلاحية الطلب منطقياً

    $url = DataExportController::signedDownloadUrl($export);

    $this->actingAs($owner)
        ->get($url)
        ->assertRedirect(); // back() مع رسالة خطأ — وليس تنزيلاً فعلياً
});

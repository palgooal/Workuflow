<?php

use App\Models\Client;
use App\Models\Quote;
use App\Models\Setting;
use App\Models\User;
use App\Services\DataExport\UserDataExportService;
use Illuminate\Support\Facades\Storage;

// extractExportZip() موحّدة الآن في tests/Helpers.php (محمَّلة من tests/Pest.php)

// ==================== العزل ====================

test('export contains only the requesting user data, not other users', function () {
    Storage::fake('exports');

    $userA = User::factory()->create();
    $userB = User::factory()->create();

    Client::factory()->for($userA)->create(['name' => 'عميل المستخدم أ']);
    Client::factory()->for($userB)->create(['name' => 'عميل المستخدم ب']);

    $disk = config('backups.user_export.disk');
    $path = app(UserDataExportService::class)->build($userA);

    $extractTo = extractExportZip($disk, $path);
    $clientsCsv = file_get_contents($extractTo.'/data/clients.csv');

    expect($clientsCsv)->toContain('عميل المستخدم أ');
    expect($clientsCsv)->not->toContain('عميل المستخدم ب');

    $dataJson = json_decode(file_get_contents($extractTo.'/data.json'), true);
    expect(collect($dataJson['clients'])->pluck('name')->all())->toBe(['عميل المستخدم أ']);
});

// ==================== الحقول الحساسة ====================

test('export excludes quote token entirely', function () {
    Storage::fake('exports');

    $user = User::factory()->create();
    $quote = Quote::factory()->for($user)->create();

    expect($quote->token)->not->toBeEmpty(); // تأكيد أن الحقل موجود فعلاً في DB

    $disk = config('backups.user_export.disk');
    $path = app(UserDataExportService::class)->build($user);
    $extractTo = extractExportZip($disk, $path);

    $quotesCsv = file_get_contents($extractTo.'/data/quotes.csv');
    $dataJson  = file_get_contents($extractTo.'/data.json');

    expect($quotesCsv)->not->toContain($quote->token);
    expect($dataJson)->not->toContain($quote->token);
    expect($quotesCsv)->not->toContain('token');
});

test('export excludes payment gateway secrets from settings', function () {
    Storage::fake('exports');

    $user = User::factory()->create();
    // togo_api_key محفوظ بمجموعة "payment" وليس مرتبطاً بمستخدم — يجب ألا يظهر أبداً
    Setting::set('togo_api_key', 'super-secret-key-should-not-leak', 'payment');

    $disk = config('backups.user_export.disk');
    $path = app(UserDataExportService::class)->build($user);
    $extractTo = extractExportZip($disk, $path);

    $settingsCsv = file_get_contents($extractTo.'/data/settings.csv');
    $dataJson    = file_get_contents($extractTo.'/data.json');

    expect($settingsCsv)->not->toContain('super-secret-key-should-not-leak');
    expect($dataJson)->not->toContain('super-secret-key-should-not-leak');
});

test('export does not contain password, remember_token or .env files', function () {
    Storage::fake('exports');

    $user = User::factory()->create(['remember_token' => 'should-never-appear-token']);

    $disk = config('backups.user_export.disk');
    $path = app(UserDataExportService::class)->build($user);
    $extractTo = extractExportZip($disk, $path);

    $accountCsv = file_get_contents($extractTo.'/data/account.csv');

    expect($accountCsv)->not->toContain('should-never-appear-token');
    expect($accountCsv)->not->toContain($user->password);
    expect(file_exists($extractTo.'/.env'))->toBeFalse();

    // لا يوجد أي ملف باسم .env في كامل الأرشيف
    $allFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($extractTo, FilesystemIterator::SKIP_DOTS));
    foreach ($allFiles as $file) {
        expect($file->getFilename())->not->toBe('.env');
    }
});

test('export README explains it is not a restore tool', function () {
    Storage::fake('exports');

    $user = User::factory()->create();

    $disk = config('backups.user_export.disk');
    $path = app(UserDataExportService::class)->build($user);
    $extractTo = extractExportZip($disk, $path);

    $readme = file_get_contents($extractTo.'/README.md');
    expect($readme)->toContain('ليست أداة استعادة');
});

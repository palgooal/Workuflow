<?php

use App\Filament\Pages\BackupScheduleSettings;
use App\Models\ActivityLog;
use App\Models\Setting;
use App\Models\User;
use App\Support\Enums\BackupType;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

// المرحلة التاسعة — Backup Policies & Settings UI. يختبر فقط صفحة إعدادات
// النسخ الاحتياطي (App\Filament\Pages\BackupScheduleSettings) — الوصول،
// الحفظ، الـValidation، الـAtomicity، الـAudit، وعرض حالة التشفير/إحصائيات
// النظام. لا علاقة له بمحرك النسخ/الاستعادة/الجدولة/الاحتفاظ/المراقبة/
// الإشعارات/الـTimeline نفسها (لم يُعدَّل أيٌّ منها في هذه المرحلة).
//
// makeSuperAdmin() موحّدة في tests/Helpers.php.
//
// ⚠️ Livewire::test() يبني المكوّن مباشرة دون المرور بـMiddleware لوحة
// /admin، فيبقى Filament::getCurrentPanel() فارغاً بدون تعيينه يدوياً أولاً —
// نفس السبب والحل المستخدَمَين فعلياً في
// tests/Feature/Filament/BackupRestoreActionTest.php (والمُتَّبَع أيضاً في
// tests/Feature/Pages/PageRevisionVersioningTest.php)، لا آلية جديدة.
beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

test('a super admin can open the backup settings page', function () {
    $admin = makeSuperAdmin();

    $this->actingAs($admin)
        ->get(BackupScheduleSettings::getUrl(panel: 'admin'))
        ->assertOk()
        ->assertSee('إعدادات النسخ الاحتياطي');
});

test('a regular user gets a 403 when trying to open the backup settings page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(BackupScheduleSettings::getUrl(panel: 'admin'))
        ->assertForbidden();
});

test('saving valid settings persists them to the backup_schedule setting group', function () {
    $admin = makeSuperAdmin();
    $this->actingAs($admin);

    Livewire::test(BackupScheduleSettings::class)
        ->fillForm([
            'database_backup_enabled' => true,
            'full_backup_enabled'     => false,
            'backup_time'             => '03:30',
            'backup_timezone'         => 'Asia/Riyadh',
            'retention_daily'         => 10,
            'retention_weekly'        => 5,
            'retention_monthly'       => 4,
            'running_timeout'         => 2400,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $saved = Setting::group('backup_schedule');

    expect($saved['full_backup_enabled'])->toBe('0')
        ->and($saved['backup_time'])->toBe('03:30')
        ->and($saved['backup_timezone'])->toBe('Asia/Riyadh')
        ->and($saved['retention_daily'])->toBe('10')
        ->and($saved['retention_weekly'])->toBe('5')
        ->and($saved['retention_monthly'])->toBe('4')
        ->and($saved['running_timeout'])->toBe('2400');
});

test('invalid retention values are rejected by form validation', function () {
    $admin = makeSuperAdmin();
    $this->actingAs($admin);

    Livewire::test(BackupScheduleSettings::class)
        ->fillForm([
            'backup_time'       => '02:00',
            'backup_timezone'   => 'UTC',
            'retention_daily'   => 0, // غير صالحة: يجب أن تكون 1 على الأقل
            'retention_weekly'  => 4,
            'retention_monthly' => 3,
            'running_timeout'   => 1800,
        ])
        ->call('save')
        ->assertHasFormErrors(['retention_daily' => 'min']);
});

test('a failed validation does not change any previously saved value (atomic save)', function () {
    $admin = makeSuperAdmin();
    $this->actingAs($admin);

    Setting::setGroup('backup_schedule', [
        'database_backup_enabled' => '1',
        'full_backup_enabled'     => '1',
        'backup_time'             => '05:00',
        'backup_timezone'         => 'UTC',
        'retention_daily'         => '9',
        'retention_weekly'        => '3',
        'retention_monthly'       => '2',
        'running_timeout'         => '900',
    ]);

    Livewire::test(BackupScheduleSettings::class)
        ->fillForm([
            'backup_time'       => '05:00',
            'backup_timezone'   => 'UTC',
            'retention_daily'   => 9,
            'retention_weekly'  => 3,
            'retention_monthly' => 0, // غير صالحة
            'running_timeout'   => 900,
        ])
        ->call('save')
        ->assertHasFormErrors(['retention_monthly' => 'min']);

    // لا شيء تغيّر — القيم المحفوظة سابقاً تبقى كما هي تماماً.
    $saved = Setting::group('backup_schedule');
    expect($saved['retention_monthly'])->toBe('2')
        ->and($saved['backup_time'])->toBe('05:00');
});

test('a successful save creates an activity log entry with the acting user and the changed fields', function () {
    $admin = makeSuperAdmin();
    $this->actingAs($admin);

    Livewire::test(BackupScheduleSettings::class)
        ->fillForm([
            'database_backup_enabled' => true,
            'full_backup_enabled'     => true,
            'backup_time'             => '04:15',
            'backup_timezone'         => 'UTC',
            'retention_daily'         => 7,
            'retention_weekly'        => 4,
            'retention_monthly'       => 3,
            'running_timeout'         => 1800,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $log = ActivityLog::query()->where('event_type', 'backup_settings.updated')->latest('created_at')->first();

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($admin->id)
        ->and($log->metadata['changed'])->toHaveKey('backup_time');
});

test('the encryption status badge reflects whether an encryption key is configured', function () {
    $admin = makeSuperAdmin();
    $this->actingAs($admin);

    config(['backups.system_backup.encryption_key' => null]);
    Livewire::test(BackupScheduleSettings::class)->assertSee('غير مفعّل');

    config(['backups.system_backup.encryption_key' => base64_encode(random_bytes(32))]);
    Livewire::test(BackupScheduleSettings::class)->assertSee('مفعّل');
});

test('system statistics render without errors when backups exist', function () {
    $admin = makeSuperAdmin();
    $this->actingAs($admin);

    Storage::fake('backups');
    makeCompletedBackup('backups', BackupType::Database, now());
    makeCompletedBackup('backups', BackupType::Full, now());

    Livewire::test(BackupScheduleSettings::class)
        ->assertSuccessful()
        ->assertSee('معلومات النظام')
        ->assertSee('عدد النسخ');
});

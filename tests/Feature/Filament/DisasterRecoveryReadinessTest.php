<?php

use App\Filament\Pages\DisasterRecoveryReadiness;
use App\Models\ActivityLog;
use App\Models\Backup;
use App\Models\Setting;
use App\Models\User;
use App\Support\Enums\BackupType;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

// المرحلة العاشرة — Disaster Recovery Readiness. يختبر فقط صفحة "جاهزية
// التعافي" (App\Filament\Pages\DisasterRecoveryReadiness) — صفحة قراءة فقط
// بالكامل، لا تُنشئ نسخة ولا تُشغِّل استعادة ولا تحفظ أي إعداد. لا علاقة له
// بمحرك النسخ/الاستعادة/الجدولة/الاحتفاظ/المراقبة/الإشعارات/الـTimeline/
// صفحة الإعدادات نفسها (لم يُعدَّل أيٌّ منها في هذه المرحلة).
//
// makeSuperAdmin()، makeCompletedBackup()، makeRestorableBackup() موحّدة في
// tests/Helpers.php.
//
// ⚠️ Livewire::test() يبني المكوّن مباشرة دون المرور بـMiddleware لوحة
// /admin، فيبقى Filament::getCurrentPanel() فارغاً بدون تعيينه يدوياً أولاً —
// نفس الآلية المعتمدة فعلياً في BackupRestoreActionTest/BackupSettingsPageTest.
beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

test('a super admin can open the disaster recovery readiness page', function () {
    $admin = makeSuperAdmin();

    $this->actingAs($admin)
        ->get(DisasterRecoveryReadiness::getUrl(panel: 'admin'))
        ->assertOk()
        ->assertSee('جاهزية التعافي من الكوارث');
});

test('a regular user gets a 403 when trying to open the disaster recovery readiness page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(DisasterRecoveryReadiness::getUrl(panel: 'admin'))
        ->assertForbidden();
});

test('READY is shown when every readiness element is healthy', function () {
    $admin = makeSuperAdmin(); // يضمن أيضاً وجود مستلم لإشعارات الفشل
    $this->actingAs($admin);

    config(['backups.system_backup.encryption_key' => base64_encode(random_bytes(32))]);

    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    makeRestorableBackup($disk, BackupType::Database); // completed + integrity_verified=true

    // الجدولة والاحتفاظ تعتمد على الافتراضيات (كلاهما مفعَّل/قيم موجبة) دون
    // أي إعداد صريح — نفس افتراضيات ScheduledBackupRunner::isEnabled() وconfig().

    Livewire::test(DisasterRecoveryReadiness::class)
        ->assertSuccessful()
        ->assertSee('READY');
});

test('WARNING is shown when exactly one readiness element is degraded', function () {
    $admin = makeSuperAdmin();
    $this->actingAs($admin);

    config(['backups.system_backup.encryption_key' => base64_encode(random_bytes(32))]);

    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    makeRestorableBackup($disk, BackupType::Database);

    // عنصر واحد فقط ناقص: الجدولة (نوع واحد فقط مفعَّل بدل الاثنين معاً) —
    // كل شيء آخر سليم (نسخة مكتملة ومتحقَّق منها، تشفير مفعَّل، احتفاظ موجب،
    // super_admin موجود لاستقبال الإشعارات).
    Setting::setGroup('backup_schedule', [
        'database_backup_enabled' => '1',
        'full_backup_enabled'     => '0',
    ]);

    Livewire::test(DisasterRecoveryReadiness::class)
        ->assertSuccessful()
        ->assertSee('WARNING');
});

test('CRITICAL is shown when there is no valid backup at all', function () {
    $admin = makeSuperAdmin();
    $this->actingAs($admin);

    // لا نسخ إطلاقاً — لا last_successful، فـIntegrity Verification وRestore
    // Available كلاهما Critical.
    Livewire::test(DisasterRecoveryReadiness::class)
        ->assertSuccessful()
        ->assertSee('CRITICAL');
});

test('page statistics render without errors when backups and a restore log exist', function () {
    $admin = makeSuperAdmin();
    $this->actingAs($admin);

    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    makeCompletedBackup($disk, BackupType::Database, now());
    makeCompletedBackup($disk, BackupType::Full, now());

    ActivityLog::record(eventType: 'backup.restored', metadata: ['backup_id' => 'x']);

    Livewire::test(DisasterRecoveryReadiness::class)
        ->assertSuccessful()
        ->assertSee('حالة النظام')
        ->assertSee('سلامة النظام')
        ->assertSee('التحقق من الجاهزية')
        ->assertSee('عدد النسخ');
});

test('opening the page does not dispatch any backup or restore job', function () {
    $admin = makeSuperAdmin();
    $this->actingAs($admin);

    Queue::fake();

    Livewire::test(DisasterRecoveryReadiness::class)->assertSuccessful();

    Queue::assertNothingPushed();
});

test('opening the page does not change any data in the database', function () {
    $admin = makeSuperAdmin();
    $this->actingAs($admin);

    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    makeCompletedBackup($disk, BackupType::Database, now());

    $backupsCountBefore     = Backup::query()->count();
    $activityLogCountBefore = ActivityLog::query()->count();
    $settingsBefore         = Setting::group('backup_schedule');

    Livewire::test(DisasterRecoveryReadiness::class)->assertSuccessful();

    expect(Backup::query()->count())->toBe($backupsCountBefore)
        ->and(ActivityLog::query()->count())->toBe($activityLogCountBefore)
        ->and(Setting::group('backup_schedule'))->toBe($settingsBefore);
});

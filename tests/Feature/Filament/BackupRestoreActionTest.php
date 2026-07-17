<?php

use App\Exceptions\Backup\BackupRestoreException;
use App\Filament\Resources\BackupResource\Pages\ListBackups;
use App\Models\Backup;
use App\Services\Backup\RestoreService;
use App\Support\Enums\BackupStatus;
use App\Support\Enums\BackupType;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

// makeSuperAdmin()، makeCompletedBackup()، makeRestorableBackup() موحّدة في
// tests/Helpers.php. RestoreService مموَّهة بالكامل في كل اختبار هنا يستدعي
// الزر فعلياً — هذا الملف يختبر واجهة Filament فقط (الظهور/الإخفاء، نموذج
// التأكيد، القفل، الإشعارات)، وليس محرك الاستعادة نفسه (مُغطّى بالكامل
// ومنفصلاً في tests/Feature/Backups).
//
// ⚠️ Livewire::test() يبني المكوّن مباشرة دون المرور بـMiddleware لوحة
// /admin، فيبقى Filament::getCurrentPanel() فارغاً بدون تعيينه يدوياً أولاً
// (نفس النمط المستخدَم في tests/Feature/Pages/PageRevisionVersioningTest.php).

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

test('restore action is hidden for a failed backup', function () {
    $admin = makeSuperAdmin();

    $backup = Backup::create([
        'name'   => 'failed-backup-restore-test',
        'type'   => BackupType::Database,
        'status' => BackupStatus::Failed,
    ]);

    Livewire::actingAs($admin)
        ->test(ListBackups::class)
        ->assertTableActionHidden('restore', $backup);
});

test('restore action is hidden when the backup has not been integrity-verified', function () {
    $admin = makeSuperAdmin();
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');

    // completed لكن integrity_verified يبقى null افتراضياً (لم يُفحَص بعد)
    $backup = makeCompletedBackup($disk, BackupType::Database, now());

    Livewire::actingAs($admin)
        ->test(ListBackups::class)
        ->assertTableActionHidden('restore', $backup);
});

test('restore action is visible only when completed and integrity-verified together', function () {
    $admin = makeSuperAdmin();
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');

    $backup = makeRestorableBackup($disk);

    Livewire::actingAs($admin)
        ->test(ListBackups::class)
        ->assertTableActionVisible('restore', $backup);
});

test('restore succeeds and calls RestoreService exactly once after typing RESTORE', function () {
    $admin = makeSuperAdmin();
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    $backup = makeRestorableBackup($disk);

    $this->mock(RestoreService::class, function ($mock) use ($backup) {
        $mock->shouldReceive('run')
            ->once()
            ->with($backup->id)
            ->andReturn(['ok' => true, 'backup_id' => $backup->id, 'manifest' => [], 'restore_id' => 'test-restore-id']);
    });

    Livewire::actingAs($admin)
        ->test(ListBackups::class)
        ->callTableAction('restore', $backup, data: ['confirmation' => 'RESTORE'])
        ->assertHasNoTableActionErrors();
});

test('restore is rejected and RestoreService is never called when RESTORE is not typed exactly', function (string $typedValue) {
    $admin = makeSuperAdmin();
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    $backup = makeRestorableBackup($disk);

    $this->mock(RestoreService::class, function ($mock) {
        $mock->shouldNotReceive('run');
    });

    Livewire::actingAs($admin)
        ->test(ListBackups::class)
        ->callTableAction('restore', $backup, data: ['confirmation' => $typedValue])
        ->assertHasTableActionErrors(['confirmation']);
})->with([
    'lowercase' => ['restore'],
    'trailing space' => ['RESTORE '],
    'leading space' => [' RESTORE'],
    'mixed case' => ['Restore'],
]);

test('restore shows a success notification after RestoreService succeeds', function () {
    $admin = makeSuperAdmin();
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    $backup = makeRestorableBackup($disk);

    $this->mock(RestoreService::class, function ($mock) {
        $mock->shouldReceive('run')->once()->andReturn(['ok' => true, 'backup_id' => 'x', 'manifest' => [], 'restore_id' => 'y']);
    });

    Livewire::actingAs($admin)
        ->test(ListBackups::class)
        ->callTableAction('restore', $backup, data: ['confirmation' => 'RESTORE'])
        ->assertNotified('تمت استعادة النسخة الاحتياطية بنجاح.');
});

test('restore shows the exact BackupRestoreException message on failure, not a generic one', function () {
    $admin = makeSuperAdmin();
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    $backup = makeRestorableBackup($disk);

    $this->mock(RestoreService::class, function ($mock) {
        $mock->shouldReceive('run')->once()->andThrow(
            new BackupRestoreException('رسالة خطأ محدَّدة من محرك الاستعادة يجب أن تظهر كما هي بدون استبدال')
        );
    });

    Livewire::actingAs($admin)
        ->test(ListBackups::class)
        ->callTableAction('restore', $backup, data: ['confirmation' => 'RESTORE'])
        ->assertNotified('فشلت عملية الاستعادة');
});

test('restore prevents a second concurrent restore while a lock is already held, without calling RestoreService', function () {
    $admin = makeSuperAdmin();
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    $backup = makeRestorableBackup($disk);

    $this->mock(RestoreService::class, function ($mock) {
        $mock->shouldNotReceive('run');
    });

    // محاكاة عملية استعادة أخرى قيد التنفيذ فعلياً — نفس اسم القفل المستخدَم
    // داخل BackupResource::runRestoreAction().
    $existingLock = Cache::lock('backups:restore-lock', 3600);
    expect($existingLock->get())->toBeTrue();

    try {
        Livewire::actingAs($admin)
            ->test(ListBackups::class)
            ->callTableAction('restore', $backup, data: ['confirmation' => 'RESTORE'])
            ->assertNotified('يوجد بالفعل عملية استعادة أخرى قيد التنفيذ');
    } finally {
        $existingLock->release();
    }
});

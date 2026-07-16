<?php

use App\Filament\Resources\BackupResource;
use App\Models\Backup;
use App\Models\User;
use App\Support\Enums\BackupStatus;
use App\Support\Enums\BackupType;
use Illuminate\Support\Facades\Storage;

// makeSuperAdmin() موحّدة الآن في tests/Helpers.php (محمَّلة من tests/Pest.php)

// ==================== صلاحيات الوصول لموارد Filament ====================

test('regular user cannot view the backups resource', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    expect(BackupResource::canViewAny())->toBeFalse();
});

test('guest cannot view the backups resource', function () {
    expect(BackupResource::canViewAny())->toBeFalse();
});

test('super_admin can view the backups resource', function () {
    $admin = makeSuperAdmin();
    $this->actingAs($admin);

    expect(BackupResource::canViewAny())->toBeTrue();
});

// ==================== تنزيل النسخة — super_admin فقط ====================

test('regular user gets 403 when trying to download a system backup', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    Storage::disk($disk)->put('system-backups/x.zip.enc', 'encrypted-content');

    $backup = Backup::create([
        'name' => 'test-backup', 'type' => BackupType::Database, 'status' => BackupStatus::Completed,
        'disk' => $disk, 'path' => 'system-backups/x.zip.enc', 'checksum' => 'abc',
    ]);

    $user = User::factory()->create();
    $url  = \Illuminate\Support\Facades\URL::temporarySignedRoute(
        'admin.backups.download', now()->addMinutes(10), ['backup' => $backup->id]
    );

    $this->actingAs($user)->get($url)->assertForbidden();
});

test('super_admin can download a system backup via signed url', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    Storage::disk($disk)->put('system-backups/x.zip.enc', 'encrypted-content');

    $backup = Backup::create([
        'name' => 'test-backup', 'type' => BackupType::Database, 'status' => BackupStatus::Completed,
        'disk' => $disk, 'path' => 'system-backups/x.zip.enc', 'checksum' => 'abc',
    ]);

    $admin = makeSuperAdmin();
    $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
        'admin.backups.download', now()->addMinutes(10), ['backup' => $backup->id]
    );

    $this->actingAs($admin)->get($url)->assertOk();

    $this->assertDatabaseHas('activity_logs', [
        'event_type'  => 'backup.downloaded',
        'entity_type' => Backup::class,
        'entity_id'   => $backup->id,
    ]);
});

test('guest cannot download a system backup even with a valid signature', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    Storage::disk($disk)->put('system-backups/x.zip.enc', 'encrypted-content');

    $backup = Backup::create([
        'name' => 'test-backup', 'type' => BackupType::Database, 'status' => BackupStatus::Completed,
        'disk' => $disk, 'path' => 'system-backups/x.zip.enc', 'checksum' => 'abc',
    ]);

    $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
        'admin.backups.download', now()->addMinutes(10), ['backup' => $backup->id]
    );

    $this->get($url)->assertRedirect(route('login'));
});

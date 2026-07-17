<?php

use App\Models\Backup;
use App\Services\Backup\BackupRetentionService;
use App\Support\Enums\BackupStatus;
use App\Support\Enums\BackupType;
use Illuminate\Support\Facades\Storage;

// makeCompletedBackup() موحّدة الآن في tests/Helpers.php (محمَّلة من tests/Pest.php)

test('retention keeps only the newest N daily database backups', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    config(['backups.system_backup.retention.daily' => 3]);

    $backups = collect(range(0, 5))->map(
        fn ($i) => makeCompletedBackup($disk, BackupType::Database, now()->subDays($i))
    );

    app(BackupRetentionService::class)->apply();

    $remaining = Backup::query()->ofType(BackupType::Database)->get();
    expect($remaining)->toHaveCount(3);

    // الأحدث 3 يجب أن تبقى
    $keptIds = $remaining->pluck('id')->sort()->values();
    $expectedIds = $backups->take(3)->pluck('id')->sort()->values();
    expect($keptIds->all())->toBe($expectedIds->all());

    // الملفات المحذوفة فعلياً من التخزين
    $deleted = $backups->slice(3);
    foreach ($deleted as $b) {
        Storage::disk($disk)->assertMissing($b->path);
    }
});

test('retention deletes the backup file from storage, not just the database row', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    config(['backups.system_backup.retention.daily' => 1]);

    $old = makeCompletedBackup($disk, BackupType::Database, now()->subDays(10));
    $new = makeCompletedBackup($disk, BackupType::Database, now());

    app(BackupRetentionService::class)->apply();

    Storage::disk($disk)->assertMissing($old->path);
    Storage::disk($disk)->assertExists($new->path);
    expect(Backup::find($old->id))->toBeNull();
});

test('retention does not touch failed backups', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    config(['backups.system_backup.retention.daily' => 0]);

    $failed = Backup::create([
        'name' => 'failed-one', 'type' => BackupType::Database, 'status' => BackupStatus::Failed,
        'error_message' => 'خطأ اختباري',
    ]);

    app(BackupRetentionService::class)->apply();

    expect(Backup::find($failed->id))->not->toBeNull();
});

test('retention applies GFS policy to full backups: weekly + monthly checkpoints', function () {
    Storage::fake('backups');
    $disk = config('backups.system_backup.disk');
    config([
        'backups.system_backup.retention.weekly'  => 2,
        'backups.system_backup.retention.monthly' => 1,
    ]);

    // نسختان حديثتان (تُحفظان كأسبوعية) + نسخة قديمة جداً (شهر مختلف تماماً)
    $recent1 = makeCompletedBackup($disk, BackupType::Full, now());
    $recent2 = makeCompletedBackup($disk, BackupType::Full, now()->subDays(3));
    $veryOld = makeCompletedBackup($disk, BackupType::Full, now()->subMonths(2));

    app(BackupRetentionService::class)->apply();

    $remaining = Backup::query()->ofType(BackupType::Full)->pluck('id');

    expect($remaining)->toContain($recent1->id, $recent2->id);
    // النسخة القديمة جداً تقع خارج نطاق الشهر الحالي المحفوظ (monthly=1) وخارج weekly=2
    expect($remaining)->not->toContain($veryOld->id);
});

// ==================== backup:apply-retention: النسخ العالقة (Running Recovery) ====================
// راجع تقرير تدقيق Backup System v1.0 — إن تعطّل الخادم أثناء تنفيذ نسخة، يبقى
// السجل status=running للأبد. backup:apply-retention يكتشف هذه الحالة ويحوّلها
// إلى failed برسالة واضحة، دون لمس أي نسخة running حديثة لا تزال تعمل فعلياً.

test('apply-retention marks a long-abandoned running backup as failed', function () {
    $jobTimeout = (int) config('backups.system_backup.job_timeout', 1800);

    $abandoned = Backup::create([
        'name'       => 'stuck-running-test',
        'type'       => BackupType::Database,
        'status'     => BackupStatus::Running,
        'started_at' => now()->subSeconds(($jobTimeout * 2) + 60),
    ]);

    $this->artisan('backup:apply-retention')->assertSuccessful();

    $abandoned->refresh();
    expect($abandoned->status)->toBe(BackupStatus::Failed);
    expect($abandoned->error_message)->not->toBeEmpty();
});

test('apply-retention does not touch a running backup still within its execution window', function () {
    $recentRunning = Backup::create([
        'name'       => 'still-running-test',
        'type'       => BackupType::Database,
        'status'     => BackupStatus::Running,
        'started_at' => now()->subMinutes(2),
    ]);

    $this->artisan('backup:apply-retention')->assertSuccessful();

    $recentRunning->refresh();
    expect($recentRunning->status)->toBe(BackupStatus::Running);
});

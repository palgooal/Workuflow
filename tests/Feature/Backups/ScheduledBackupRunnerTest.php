<?php

use App\Jobs\Backup\RunSystemBackupJob;
use App\Models\Backup;
use App\Models\Setting;
use App\Services\Backup\ScheduledBackupRunner;
use App\Support\Enums\BackupStatus;
use App\Support\Enums\BackupTrigger;
use App\Support\Enums\BackupType;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

// المرحلة الخامسة — النسخ الاحتياطي التلقائي (Automatic Backup Scheduler).
// هذا الملف يختبر طبقة الجدولة (ScheduledBackupRunner) فقط: القرار
// بالإنشاء/التخطي، triggered_by، والـLogging المطلوب — وليس محرك النسخ نفسه
// (SystemBackupService/RunSystemBackupJob مغطّيان بالكامل في ملفات أخرى ضمن
// نفس المجلد ولا يُعاد اختبارهما هنا). Queue::fake() يمنع أي تنفيذ فعلي.

test('scheduler creates a database backup and dispatches the job when enabled', function () {
    Queue::fake();
    Setting::setGroup('backup_schedule', ['database_backup_enabled' => '1']);

    app(ScheduledBackupRunner::class)->run(BackupType::Database);

    $backup = Backup::query()->latest('created_at')->first();

    expect($backup)->not->toBeNull()
        ->and($backup->type)->toBe(BackupType::Database)
        ->and($backup->status)->toBe(BackupStatus::Pending);

    Queue::assertPushed(RunSystemBackupJob::class);
});

test('scheduler creates a full backup and dispatches the job when enabled', function () {
    Queue::fake();
    Setting::setGroup('backup_schedule', ['full_backup_enabled' => '1']);

    app(ScheduledBackupRunner::class)->run(BackupType::Full);

    $backup = Backup::query()->latest('created_at')->first();

    expect($backup)->not->toBeNull()
        ->and($backup->type)->toBe(BackupType::Full);

    Queue::assertPushed(RunSystemBackupJob::class);
});

test('scheduler does not run and creates nothing when database backup scheduling is disabled', function () {
    Queue::fake();
    Setting::setGroup('backup_schedule', ['database_backup_enabled' => '0']);

    app(ScheduledBackupRunner::class)->run(BackupType::Database);

    expect(Backup::query()->count())->toBe(0);
    Queue::assertNothingPushed();
});

test('scheduler does not run and creates nothing when full backup scheduling is disabled', function () {
    Queue::fake();
    Setting::setGroup('backup_schedule', ['full_backup_enabled' => '0']);

    app(ScheduledBackupRunner::class)->run(BackupType::Full);

    expect(Backup::query()->count())->toBe(0);
    Queue::assertNothingPushed();
});

test('scheduler skips execution and creates nothing when another backup is already running', function () {
    Queue::fake();
    Setting::setGroup('backup_schedule', ['database_backup_enabled' => '1']);

    Backup::create([
        'name'         => 'already-running',
        'type'         => BackupType::Full,
        'status'       => BackupStatus::Running,
        'triggered_by' => BackupTrigger::Manual,
        'started_at'   => now(),
    ]);

    app(ScheduledBackupRunner::class)->run(BackupType::Database);

    // السجل الوحيد الموجود هو ذاك الذي كان running بالفعل — لا شيء جديد أُنشئ
    expect(Backup::query()->count())->toBe(1);
    Queue::assertNothingPushed();
});

test('scheduler logs "Scheduled backup started" and stamps triggered_by=scheduled', function () {
    Queue::fake();
    Log::spy();
    Setting::setGroup('backup_schedule', ['database_backup_enabled' => '1']);

    app(ScheduledBackupRunner::class)->run(BackupType::Database);

    $backup = Backup::query()->latest('created_at')->first();
    expect($backup->triggered_by)->toBe(BackupTrigger::Scheduled);

    Log::shouldHaveReceived('info')
        ->once()
        ->with('Scheduled backup started', ['type' => 'database']);
});

test('scheduler logs a warning when skipping due to a running backup', function () {
    Queue::fake();
    Log::spy();
    Setting::setGroup('backup_schedule', ['full_backup_enabled' => '1']);

    Backup::create([
        'name'         => 'already-running',
        'type'         => BackupType::Database,
        'status'       => BackupStatus::Running,
        'triggered_by' => BackupTrigger::Scheduled,
        'started_at'   => now(),
    ]);

    app(ScheduledBackupRunner::class)->run(BackupType::Full);

    Log::shouldHaveReceived('warning')
        ->once()
        ->with('Scheduled backup skipped (another backup is already running)', ['type' => 'full']);
});

test('scheduler logs "Scheduled backup completed" via BackupObserver once the backup actually completes', function () {
    Queue::fake();
    Setting::setGroup('backup_schedule', ['database_backup_enabled' => '1']);

    // ⚠️ عمداً لا نستخدم Log::spy() هنا: Mockery Spy، عند التحقق بـ
    // shouldHaveReceived('info')->once(), يعدّ **كل** استدعاءات info() معاً
    // بصرف النظر عن with() — وهذا المسار يُنتج عمداً رسالتَي info() صحيحتين
    // ومختلفتين معاً: "started" من ScheduledBackupRunner::run()، ثم
    // "completed" من BackupObserver عند markCompleted() أدناه، فيفشل
    // ->once() دائماً (2 استدعاءات فعلية) رغم أن كل شيء يعمل بشكل صحيح.
    // البديل هنا: نلتقط سجل الاستدعاءات الحقيقي عبر Log::listen() (API رسمية
    // في Illuminate\Log\Logger — تبث حدث MessageLogged لكل استدعاء فعلي)، ثم
    // نُصفّي بـPHP/Collection حسب نص الرسالة بلا أي اعتماد على عدّ Mockery
    // الكلي للميثود.
    $logged = [];
    Log::listen(function ($event) use (&$logged) {
        $logged[] = ['level' => $event->level, 'message' => $event->message, 'context' => $event->context];
    });

    app(ScheduledBackupRunner::class)->run(BackupType::Database);
    $backup = Backup::query()->latest('created_at')->first();

    // يحاكي ما تفعله SystemBackupService::run() فعلياً عند النجاح — لا تعديل
    // على تلك الخدمة هنا، فقط استدعاء نفس دالة الحالة العامة الموجودة أصلاً.
    $backup->markCompleted('backups', 'system-backups/x.zip.enc', 10, 'checksum', true);

    // "started" مسموح بها ومُتحقَّق منها هنا أيضاً — لم تُحذف ولم تُمنع.
    $startedLogs = collect($logged)->where('message', 'Scheduled backup started')->values();
    expect($startedLogs)->toHaveCount(1);
    expect($startedLogs->first()['context'])->toBe(['type' => 'database']);

    // "completed" موجودة مرة واحدة بالضبط داخل سجل الاستدعاءات — تحقّق من
    // الوجود ضمن السجل، لا افتراض أن info() كميثود استُدعيت مرة واحدة فقط.
    $completedLogs = collect($logged)->where('message', 'Scheduled backup completed')->values();
    expect($completedLogs)->toHaveCount(1);
    expect($completedLogs->first()['context'])->toBe([
        'backup_id' => $backup->id,
        'type'      => 'database',
    ]);

    // تحقّق أقوى: تحديث لاحق لا يغيّر status (BackupObserver يعتمد على
    // wasChanged('status')) يجب ألا يُضيف تسجيلاً آخر لـ"completed" — يثبت
    // أن Observer لا يعيد التسجيل عند أي تحديث آخر على نفس السجل.
    $backup->update(['name' => $backup->name.'-renamed-after-completion']);

    $completedLogsAfterExtraUpdate = collect($logged)->where('message', 'Scheduled backup completed')->values();
    expect($completedLogsAfterExtraUpdate)->toHaveCount(1);
});

test('scheduler logs "Scheduled backup failed" via BackupObserver once the backup actually fails', function () {
    Queue::fake();
    Log::spy();
    Setting::setGroup('backup_schedule', ['full_backup_enabled' => '1']);

    app(ScheduledBackupRunner::class)->run(BackupType::Full);
    $backup = Backup::query()->latest('created_at')->first();

    $backup->markFailed('فشل تجريبي لأغراض الاختبار');

    Log::shouldHaveReceived('error')
        ->once()
        ->with('Scheduled backup failed', [
            'backup_id' => $backup->id,
            'type'      => 'full',
            'error'     => 'فشل تجريبي لأغراض الاختبار',
        ]);
});

test('scheduler never starts two scheduled backups in parallel — a running backup blocks the next scheduled run', function () {
    Queue::fake();
    Setting::setGroup('backup_schedule', [
        'database_backup_enabled' => '1',
        'full_backup_enabled'     => '1',
    ]);

    $runner = app(ScheduledBackupRunner::class);

    // أول استدعاء يُنشئ نسخة قاعدة بيانات وتصل إلى قناة الطابور (مموَّهة).
    $runner->run(BackupType::Database);

    // يحاكي انتقال SystemBackupService الفعلي للنسخة إلى running أثناء التنفيذ
    // غير المتزامن (Queue::fake() يمنع تنفيذها فعلياً هنا).
    Backup::query()->latest('created_at')->first()->update(['status' => BackupStatus::Running]);

    // استدعاء ثانٍ (النسخة الكاملة) يجب أن يُرفَض تماماً طالما الأولى running.
    $runner->run(BackupType::Full);

    expect(Backup::query()->ofType(BackupType::Full)->count())->toBe(0)
        ->and(Backup::query()->count())->toBe(1);

    Queue::assertPushed(RunSystemBackupJob::class, 1);
});

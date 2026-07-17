<?php

use App\Models\Backup;
use App\Support\Enums\BackupStatus;
use App\Support\Enums\BackupTrigger;
use App\Support\Enums\BackupType;
use Illuminate\Notifications\DatabaseNotification;
use Spatie\Permission\Models\Role;

// المرحلة السابعة — Backup Failure Notifications (Phase 1). هذا الملف يختبر
// فقط إشعار الفشل الداخلي (Filament Database Notification) الذي يُرسِله
// BackupObserver لمستخدمي super_admin عند فشل نسخة *مجدولة* — وليس محرك
// النسخ/الاستعادة/الجدولة نفسه (مغطّى بالكامل في ملفات أخرى ضمن نفس المجلد).
//
// makeSuperAdmin() موحّدة في tests/Helpers.php.

test('a failed scheduled backup creates exactly one database notification for a super admin', function () {
    $admin = makeSuperAdmin();

    $backup = Backup::create([
        'name'         => 'scheduled-database-x',
        'type'         => BackupType::Database,
        'status'       => BackupStatus::Running,
        'triggered_by' => BackupTrigger::Scheduled,
        'started_at'   => now(),
    ]);

    $backup->markFailed('فشل الاتصال بقاعدة البيانات');

    expect($admin->notifications()->count())->toBe(1);

    $notification = $admin->notifications()->first();
    expect($notification->data['title'])->toBe('فشل إنشاء النسخة الاحتياطية');
});

test('a failed manual backup does not create any notification', function () {
    $admin = makeSuperAdmin();

    $backup = Backup::create([
        'name'         => 'manual-database-x',
        'type'         => BackupType::Database,
        'status'       => BackupStatus::Running,
        'triggered_by' => BackupTrigger::Manual,
        'started_at'   => now(),
    ]);

    $backup->markFailed('خطأ ما');

    expect($admin->notifications()->count())->toBe(0);
});

test('a completed scheduled backup does not create a failure notification', function () {
    $admin = makeSuperAdmin();

    $backup = Backup::create([
        'name'         => 'scheduled-database-x',
        'type'         => BackupType::Database,
        'status'       => BackupStatus::Running,
        'triggered_by' => BackupTrigger::Scheduled,
        'started_at'   => now(),
    ]);

    $backup->markCompleted('backups', 'system-backups/x.zip.enc', 10, 'checksum', true);

    expect($admin->notifications()->count())->toBe(0);
});

test('a later update that does not change status does not create a second notification', function () {
    $admin = makeSuperAdmin();

    $backup = Backup::create([
        'name'         => 'scheduled-database-x',
        'type'         => BackupType::Database,
        'status'       => BackupStatus::Running,
        'triggered_by' => BackupTrigger::Scheduled,
        'started_at'   => now(),
    ]);

    $backup->markFailed('خطأ مبدئي');
    expect($admin->notifications()->count())->toBe(1);

    // تحديث لاحق لا يمسّ status — اسم/checksum فقط (كما يفعل أي Update عادي
    // على السجل بعد اكتماله)، يجب ألا يُنشئ إشعاراً ثانياً.
    $backup->update([
        'name'     => 'renamed-after-failure',
        'checksum' => 'new-checksum-value',
    ]);

    expect($admin->notifications()->count())->toBe(1);
});

test('an empty error message falls back to a clear unknown-reason message in the notification body', function () {
    $admin = makeSuperAdmin();

    $backup = Backup::create([
        'name'         => 'scheduled-database-x',
        'type'         => BackupType::Database,
        'status'       => BackupStatus::Running,
        'triggered_by' => BackupTrigger::Scheduled,
        'started_at'   => now(),
    ]);

    // محاكاة حالة نادرة: status ينتقل إلى failed بدون رسالة خطأ مسجَّلة —
    // تجاوز markFailed() عمداً (تفرض هي نفسها نصاً دائماً) لاختبار fallback العرض تحديداً.
    $backup->update([
        'status'        => BackupStatus::Failed,
        'error_message' => null,
        'completed_at'  => now(),
    ]);

    $notification = $admin->notifications()->first();
    expect($notification->data['body'])->toContain('سبب غير معروف.');
});

test('the notification action links directly to the backup record in the admin panel', function () {
    $admin = makeSuperAdmin();

    $backup = Backup::create([
        'name'         => 'scheduled-database-x',
        'type'         => BackupType::Database,
        'status'       => BackupStatus::Running,
        'triggered_by' => BackupTrigger::Scheduled,
        'started_at'   => now(),
    ]);

    $backup->markFailed('خطأ ما');

    $notification = $admin->notifications()->first();
    $url = $notification->data['actions'][0]['url'];

    // نتحقق من شكل الرابط مباشرة بدل إعادة استدعاء BackupResource::getUrl()
    // هنا (لن يثبت شيئاً لو نسي الإنتاج تمرير panel صراحة، طالما كلا
    // الاستدعاءين يعملان بنفس "اللوحة الحالية" الضمنية في سياق الاختبار). لا
    // نضبط Filament::setCurrentPanel() في هذا الاختبار — الهدف إثبات أن
    // الرابط يُبنى بصورة صحيحة ومستقلة عن أي "لوحة حالية".
    expect($url)->not->toBeEmpty()
        ->and($url)->toContain('/admin/')
        ->and($url)->toContain((string) $backup->id);
});

test('a failed scheduled backup does not throw when the super_admin role does not exist', function () {
    // لا نستدعي makeSuperAdmin() هنا عمداً — لا يوجد دور super_admin على
    // الإطلاق في قاعدة بيانات هذا الاختبار (RefreshDatabase، لا Seeding).
    expect(Role::where('name', 'super_admin')->exists())->toBeFalse();

    $backup = Backup::create([
        'name'         => 'scheduled-database-x',
        'type'         => BackupType::Database,
        'status'       => BackupStatus::Running,
        'triggered_by' => BackupTrigger::Scheduled,
        'started_at'   => now(),
    ]);

    // إن رمت notifySuperAdminsOfScheduledFailure() أي استثناء (مثلاً
    // RoleDoesNotExist من Spatie)، لن يصل التنفيذ إلى الأسطر التالية،
    // ويفشل هذا الاختبار تلقائياً بسبب الاستثناء غير الملتقَط.
    $backup->markFailed('خطأ ما دون وجود super_admin');

    expect($backup->fresh()->status)->toBe(BackupStatus::Failed);
    expect(DatabaseNotification::count())->toBe(0);
});

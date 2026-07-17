<?php

use App\Models\Backup;
use App\Support\Enums\BackupStatus;
use App\Support\Enums\BackupTrigger;
use App\Support\Enums\BackupType;

// المرحلة الثامنة — Backup History & Audit Timeline. هذا الملف يختبر فقط
// السجل الزمني (Timeline) نفسه — App\View\Components\BackupTimeline +
// resources/views/components/backup-timeline.blade.php — عبر renderBackupTimeline()
// (tests/Helpers.php)، التي تُصيّر <x-backup-timeline /> مباشرة عبر
// Blade::render() الرسمية.
//
// ⚠️ عمداً بلا Livewire::test(ViewBackup::class, ...) وبلا
// Filament::setCurrentPanel(): الهدف هنا هو منطق ومخرجات الـTimeline نفسها،
// وليس صفحة Filament كاملة (Breadcrumbs/Header/Layout/Resource URLs) — هذا
// المكوّن لا يعتمد على أي شيء من Filament أصلاً، فلا حاجة لتهيئة أي لوحة.
// لا علاقة لهذا الملف بمحرك النسخ/الاستعادة/الجدولة نفسه (مغطّى بالكامل في
// ملفات أخرى ضمن نفس المجلد) ولم يُعدَّل أيٌّ منها هنا.

test('a completed backup timeline shows the started, completed, and duration steps', function () {
    $backup = Backup::create([
        'name'             => 'db-completed',
        'type'             => BackupType::Database,
        'status'           => BackupStatus::Completed,
        'triggered_by'     => BackupTrigger::Manual,
        'started_at'       => now()->subSeconds(49),
        'completed_at'     => now(),
        'duration_seconds' => 49,
    ]);

    $html = renderBackupTimeline($backup);

    expect($html)->toContain('بدأ إنشاء النسخة')
        ->and($html)->toContain('بدأ التنفيذ')
        ->and($html)->toContain('اكتملت بنجاح')
        ->and($html)->toContain('المدة: 49 ثانية');
});

test('a failed backup timeline shows the failed step with its error message', function () {
    $backup = Backup::create([
        'name'          => 'db-failed',
        'type'          => BackupType::Database,
        'status'        => BackupStatus::Failed,
        'triggered_by'  => BackupTrigger::Scheduled,
        'started_at'    => now()->subSeconds(10),
        'completed_at'  => now(),
        'error_message' => 'MySQL connection refused',
    ]);

    $html = renderBackupTimeline($backup);

    expect($html)->toContain('فشلت')
        ->and($html)->toContain('MySQL connection refused');
});

test('a running backup timeline does not show a completed step', function () {
    $backup = Backup::create([
        'name'         => 'db-running',
        'type'         => BackupType::Database,
        'status'       => BackupStatus::Running,
        'triggered_by' => BackupTrigger::Manual,
        'started_at'   => now(),
    ]);

    $html = renderBackupTimeline($backup);

    expect($html)->toContain('جارٍ التنفيذ...')
        ->and($html)->not->toContain('اكتملت بنجاح');
});

test('a pending backup timeline shows the waiting-for-execution step', function () {
    $backup = Backup::create([
        'name'         => 'db-pending',
        'type'         => BackupType::Database,
        'status'       => BackupStatus::Pending,
        'triggered_by' => BackupTrigger::Manual,
    ]);

    $html = renderBackupTimeline($backup);

    expect($html)->toContain('بانتظار التنفيذ');
});

test('a failed backup with no recorded error message falls back to the unknown-reason text', function () {
    $backup = Backup::create([
        'name'          => 'db-failed-unknown',
        'type'          => BackupType::Database,
        'status'        => BackupStatus::Failed,
        'triggered_by'  => BackupTrigger::Scheduled,
        'started_at'    => now()->subSeconds(5),
        'completed_at'  => now(),
        'error_message' => null,
    ]);

    $html = renderBackupTimeline($backup);

    expect($html)->toContain('سبب غير معروف.');
});

test('a scheduled backup timeline shows the scheduled trigger badge', function () {
    $backup = Backup::create([
        'name'             => 'db-scheduled',
        'type'             => BackupType::Database,
        'status'           => BackupStatus::Completed,
        'triggered_by'     => BackupTrigger::Scheduled,
        'started_at'       => now()->subMinute(),
        'completed_at'     => now(),
        'duration_seconds' => 60,
    ]);

    $html = renderBackupTimeline($backup);

    expect($html)->toContain('مجدول');
});

test('a manual backup timeline shows the manual trigger badge', function () {
    $backup = Backup::create([
        'name'             => 'db-manual',
        'type'             => BackupType::Database,
        'status'           => BackupStatus::Completed,
        'triggered_by'     => BackupTrigger::Manual,
        'started_at'       => now()->subSeconds(15),
        'completed_at'     => now(),
        'duration_seconds' => 15,
    ]);

    $html = renderBackupTimeline($backup);

    expect($html)->toContain('يدوي');
});

<?php

namespace App\View\Components;

use App\Models\Backup;
use App\Support\Enums\BackupStatus;
use App\Support\Enums\BackupTrigger;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * BackupTimeline — يبني السجل الزمني (Timeline) لدورة حياة نسخة احتياطية
 * واحدة، اعتماداً حصراً على الحقول المحمَّلة أصلاً على $backup (created_at،
 * started_at، completed_at، status، duration_seconds، triggered_by،
 * error_message) — بدون أي استعلام إضافي أو علاقة جديدة أو Service جديدة.
 *
 * المرحلة الثامنة (Backup History & Audit Timeline) — عرض وتدقيق فقط. لا
 * علاقة لهذا المكوّن بمنطق إنشاء/جدولة/استعادة النسخ (BackupService/
 * RestoreService/ScheduledBackupRunner/BackupRetention/BackupMonitoringService/
 * BackupObserver) — لم يُلمَس أيٌّ منها. راجع docs/BACKUP-SYSTEM.md.
 *
 * يُستخدَم داخل resources/views/filament/backups/backup-timeline-section.blade.php
 * (تُستدعى عبر Infolists\Components\View من ViewBackup::infolist()) عبر:
 * <x-backup-timeline :backup="$backup" />
 */
class BackupTimeline extends Component
{
    /** @var array<int, array<string, mixed>> كل خطوة: key/icon/color/title/time/description/ariaLabel */
    public array $steps;

    public function __construct(public Backup $backup)
    {
        $this->steps = $this->buildSteps($backup);
    }

    public function render(): View
    {
        return view('components.backup-timeline');
    }

    /**
     * التصنيف يعتمد فقط على status الحالية للسجل — قواعد العرض حسب الطلب:
     * Pending: "بانتظار التنفيذ" فقط. Running: "بدأ التنفيذ" ثم "جارٍ
     * التنفيذ..." بدون خطوة اكتمال. Completed: البداية + التنفيذ + الاكتمال
     * + المدة. Failed: البداية + التنفيذ + الفشل + السبب.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildSteps(Backup $backup): array
    {
        $steps = [
            [
                'key'         => 'created',
                'icon'        => 'heroicon-o-document-plus',
                'color'       => 'gray',
                'title'       => 'بدأ إنشاء النسخة',
                'time'        => $backup->created_at,
                'description' => null,
                'ariaLabel'   => 'الخطوة الأولى: بدأ إنشاء النسخة',
            ],
        ];

        match ($backup->status) {
            BackupStatus::Pending   => $this->appendPendingStep($steps),
            BackupStatus::Running   => $this->appendRunningSteps($steps, $backup),
            BackupStatus::Completed => $this->appendCompletedSteps($steps, $backup),
            BackupStatus::Failed    => $this->appendFailedSteps($steps, $backup),
        };

        return $steps;
    }

    private function appendPendingStep(array &$steps): void
    {
        $steps[] = [
            'key'         => 'pending',
            'icon'        => 'heroicon-o-clock',
            'color'       => 'gray',
            'title'       => 'بانتظار التنفيذ',
            'time'        => null,
            'description' => null,
            'ariaLabel'   => 'الحالة الحالية: بانتظار التنفيذ',
        ];
    }

    private function appendRunningSteps(array &$steps, Backup $backup): void
    {
        $this->appendStartedStepIfPresent($steps, $backup);

        $steps[] = [
            'key'         => 'running',
            'icon'        => 'heroicon-o-arrow-path',
            'color'       => 'info',
            'title'       => 'جارٍ التنفيذ...',
            'time'        => null,
            'description' => null,
            'ariaLabel'   => 'العملية جارية الآن ولم تكتمل بعد',
            'pulse'       => true,
        ];
    }

    private function appendCompletedSteps(array &$steps, Backup $backup): void
    {
        $this->appendStartedStepIfPresent($steps, $backup);

        $steps[] = [
            'key'         => 'completed',
            'icon'        => 'heroicon-o-check-circle',
            'color'       => 'success',
            'title'       => 'اكتملت بنجاح',
            'time'        => $backup->completed_at,
            'description' => $this->durationLine($backup),
            'ariaLabel'   => 'اكتملت النسخة الاحتياطية بنجاح',
        ];
    }

    private function appendFailedSteps(array &$steps, Backup $backup): void
    {
        $this->appendStartedStepIfPresent($steps, $backup);

        $steps[] = [
            'key'         => 'failed',
            'icon'        => 'heroicon-o-x-circle',
            'color'       => 'danger',
            'title'       => 'فشلت',
            'time'        => $backup->completed_at,
            'description' => 'السبب: '.($backup->error_message ?: 'سبب غير معروف.'),
            'ariaLabel'   => 'فشلت عملية النسخ الاحتياطي',
        ];
    }

    private function appendStartedStepIfPresent(array &$steps, Backup $backup): void
    {
        if (! $backup->started_at) {
            return;
        }

        $steps[] = [
            'key'         => 'started',
            'icon'        => 'heroicon-o-clock',
            'color'       => 'info',
            'title'       => 'بدأ التنفيذ',
            'time'        => $backup->started_at,
            'description' => null,
            'ariaLabel'   => 'بدأ تنفيذ النسخة الاحتياطية',
        ];
    }

    private function durationLine(Backup $backup): ?string
    {
        $label = $this->formatDuration($backup->duration_seconds);

        return $label ? "المدة: {$label}" : null;
    }

    /**
     * يحوّل duration_seconds إلى صياغة عربية مختصرة — الأمثلة المطلوبة
     * تحديداً: "15 ثانية"، "3 دقائق"، "1 دقيقة و15 ثانية".
     */
    private function formatDuration(?int $seconds): ?string
    {
        if ($seconds === null) {
            return null;
        }

        $minutes = intdiv($seconds, 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes === 0) {
            return "{$remainingSeconds} ثانية";
        }

        $minutesLabel = $minutes === 1 ? '1 دقيقة' : "{$minutes} دقائق";

        if ($remainingSeconds === 0) {
            return $minutesLabel;
        }

        return "{$minutesLabel} و{$remainingSeconds} ثانية";
    }

    /**
     * شارة مصدر النسخة (يدوي/مجدول) — تعيد استخدام BackupTrigger::label()/
     * color() الموجودتين أصلاً (لا تكرار منطق)، بلا استعلام إضافي (triggered_by
     * عمود محمَّل أصلاً على $this->backup، وليس علاقة).
     */
    public function triggerLabel(): string
    {
        return $this->backup->triggered_by?->label() ?? '—';
    }

    public function triggerColor(): string
    {
        return $this->backup->triggered_by?->color() ?? 'gray';
    }

    public function triggerIcon(): string
    {
        return match ($this->backup->triggered_by) {
            BackupTrigger::Manual    => 'heroicon-o-user',
            BackupTrigger::Scheduled => 'heroicon-o-cog-6-tooth',
            null                     => 'heroicon-o-question-mark-circle',
        };
    }
}

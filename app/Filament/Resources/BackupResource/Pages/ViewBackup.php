<?php

namespace App\Filament\Resources\BackupResource\Pages;

use App\Filament\Resources\BackupResource;
use App\Models\ActivityLog;
use App\Models\Backup;
use App\Services\Backup\BackupInspectionService;
use App\Support\Enums\BackupStatus;
use App\Support\Enums\BackupType;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\View as InfolistView;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Throwable;

/**
 * ViewBackup — صفحة "تفاصيل النسخة" (super_admin فقط، تحت نفس حماية
 * BackupResource::canViewAny()). قراءة فقط — لا تعديل ولا استعادة من هنا.
 *
 * ⚠️ قسم "معلومات Manifest" يقرأ manifest.json عبر فك تشفير مؤقت في mount()
 * (مرة واحدة لكل تحميل صفحة، عبر BackupInspectionService::readManifest())،
 * ويُحذَف كل ملف مؤقت فوراً بعدها (راجع الخدمة). هذا مقصود وليس Cache — كل
 * فتح للصفحة يعيد فك التشفير والقراءة من جديد، ولا يُحفَظ شيء مفكوك دائماً.
 */
class ViewBackup extends ViewRecord
{
    protected static string $resource = BackupResource::class;

    protected ?array $manifestData = null;

    protected ?string $manifestError = null;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        /** @var Backup $backup */
        $backup = $this->record;

        try {
            $this->manifestData = app(BackupInspectionService::class)->readManifest($backup);
        } catch (Throwable $e) {
            $this->manifestError = $e->getMessage();
        }
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record)
            ->schema([
                Section::make('معلومات عامة')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')->label('الاسم'),

                        TextEntry::make('type')
                            ->label('النوع')
                            ->badge()
                            ->formatStateUsing(fn (BackupType $state) => $state->label())
                            ->color(fn (BackupType $state) => $state === BackupType::Full ? 'primary' : 'gray'),

                        TextEntry::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->formatStateUsing(fn (BackupStatus $state) => $state->label())
                            ->color(fn (BackupStatus $state) => $state->color()),

                        TextEntry::make('created_at')->label('تاريخ الإنشاء')->dateTime('Y-m-d H:i:s'),
                        TextEntry::make('started_at')->label('تاريخ البدء')->dateTime('Y-m-d H:i:s')->placeholder('—'),
                        TextEntry::make('completed_at')->label('تاريخ الانتهاء')->dateTime('Y-m-d H:i:s')->placeholder('—'),

                        TextEntry::make('duration_seconds')
                            ->label('مدة التنفيذ')
                            ->formatStateUsing(fn (?int $state) => $state !== null ? "{$state} ثانية" : '—'),

                        TextEntry::make('size_bytes')
                            ->label('الحجم')
                            ->formatStateUsing(fn (mixed $state, Backup $record) => $record->humanSize() ?? '—'),

                        TextEntry::make('encrypted')
                            ->label('مشفَّرة؟')
                            ->badge()
                            ->formatStateUsing(fn (bool $state) => $state ? 'نعم' : 'لا')
                            ->color(fn (bool $state) => $state ? 'success' : 'danger'),

                        TextEntry::make('integrity_verified')
                            ->label('حالة التحقق من السلامة')
                            ->badge()
                            ->formatStateUsing(fn (?bool $state) => match ($state) {
                                true    => 'Verified',
                                false   => 'Verification Failed',
                                default => 'Not Verified',
                            })
                            ->color(fn (?bool $state) => match ($state) {
                                true    => 'success',
                                false   => 'danger',
                                default => 'warning',
                            }),

                        TextEntry::make('integrity_checked_at')
                            ->label('تاريخ آخر تحقق')
                            ->dateTime('Y-m-d H:i:s')
                            ->placeholder('لم يُفحَص بعد'),

                        TextEntry::make('checksum')
                            ->label('SHA256 Checksum')
                            ->fontFamily('mono')
                            ->copyable()
                            ->copyMessage('تم نسخ الـ checksum')
                            ->placeholder('—')
                            ->columnSpan(2),

                        TextEntry::make('disk')->label('Disk')->placeholder('—'),
                        TextEntry::make('path')->label('Path')->fontFamily('mono')->placeholder('—')->columnSpan(2),
                    ]),

                // المرحلة الثامنة (Backup History & Audit Timeline) — عرض
                // وتدقيق فقط، مبني بالكامل من $record الحالي (created_at/
                // started_at/completed_at/status/duration_seconds/
                // triggered_by/error_message) بلا أي استعلام إضافي. المنطق
                // الفعلي في App\View\Components\BackupTimeline، والقالب في
                // resources/views/components/backup-timeline.blade.php —
                // لا تكرار HTML، ولا تعديل على أي محرك نسخ/جدولة/استعادة.
                Section::make('السجل الزمني للنسخة')
                    ->description('دورة حياة هذه النسخة الاحتياطية بالكامل، من الإنشاء حتى الاكتمال أو الفشل.')
                    ->schema([
                        InfolistView::make('filament.backups.backup-timeline-section'),
                    ]),

                Section::make('معلومات Manifest')
                    ->description('تُقرَأ مباشرة من الأرشيف عبر فك تشفير مؤقت عند فتح هذه الصفحة — لا يُحفَظ أي ملف مفكوك.')
                    ->schema(fn () => $this->buildManifestEntries()),
            ]);
    }

    /** @return array<int,\Filament\Infolists\Components\Entry> */
    protected function buildManifestEntries(): array
    {
        if ($this->manifestError !== null) {
            return [
                TextEntry::make('manifest_error')
                    ->label('تعذّرت قراءة تفاصيل الأرشيف')
                    ->state($this->manifestError)
                    ->color('danger'),
            ];
        }

        $manifest = $this->manifestData['manifest'] ?? [];
        $fileCount = $this->manifestData['file_count'] ?? 0;
        $totalSize = $this->manifestData['total_size'] ?? 0;

        $includedPaths = $manifest['storage_paths'] ?? [];
        $excludedPatterns = config('backups.system_backup.exclude_patterns', []);

        return [
            TextEntry::make('manifest_laravel')
                ->label('Laravel Version')
                ->state($manifest['laravel'] ?? '—'),

            TextEntry::make('manifest_php')
                ->label('PHP Version')
                ->state('غير مسجَّل في manifest هذه النسخة')
                ->color('gray'),

            TextEntry::make('manifest_db_driver')
                ->label('قاعدة البيانات (Driver)')
                ->state($manifest['database']['driver'] ?? '—'),

            TextEntry::make('manifest_db_version')
                ->label('إصدار قاعدة البيانات')
                ->state('غير مسجَّل في manifest هذه النسخة')
                ->color('gray'),

            TextEntry::make('manifest_created_at')
                ->label('تاريخ إنشاء النسخة (من Manifest)')
                ->state($manifest['created_at'] ?? '—'),

            TextEntry::make('manifest_type')
                ->label('نوع النسخة (من Manifest)')
                ->state($manifest['type'] ?? '—'),

            TextEntry::make('manifest_file_count')
                ->label('عدد الملفات داخل الأرشيف')
                ->state((string) $fileCount),

            TextEntry::make('manifest_total_size')
                ->label('الحجم الكلي (غير مضغوط)')
                ->state($this->humanBytes($totalSize)),

            TextEntry::make('manifest_included_paths')
                ->label('المجلدات المشمولة')
                ->state($includedPaths === [] ? '—' : implode('، ', $includedPaths))
                ->columnSpan(2),

            TextEntry::make('manifest_excluded_patterns')
                ->label('المجلدات/الأنماط المستبعدة (الإعداد الحالي)')
                ->state($excludedPatterns === [] ? '—' : implode('، ', $excludedPatterns))
                ->helperText('بعض القيم (مثل المجلدات المستبعدة) تُعرض من إعدادات النظام الحالية، لأنها لا تُخزَّن داخل ملف النسخة الاحتياطية. لذلك قد تختلف عن الإعدادات التي كانت سارية وقت إنشاء هذه النسخة.')
                ->columnSpan(2),
        ];
    }

    private function humanBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '—';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size  = (float) $bytes;
        $i     = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 1).' '.$units[$i];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('verifyIntegrityDetailed')
                ->label('التحقق من سلامة النسخة')
                ->icon('heroicon-o-shield-check')
                ->color('info')
                ->visible(fn (Backup $record) => $record->status === BackupStatus::Completed)
                ->requiresConfirmation()
                ->modalHeading('التحقق من سلامة النسخة')
                ->modalDescription('سيُعاد تنزيل الملف مؤقتاً، فك تشفيره، والتحقق من صلاحية الأرشيف ومحتواه بالكامل. لن يُحفَظ أي ملف مفكوك على القرص.')
                ->action(function (): void {
                    /** @var Backup $backup */
                    $backup = $this->record;

                    $result = app(BackupInspectionService::class)->verify($backup);

                    $backup->refresh();
                    $this->record = $backup;

                    ActivityLog::recordFor(
                        eventType: 'backup.integrity_checked',
                        entityType: Backup::class,
                        entityId: $backup->id,
                        metadata: ['verified' => $result['ok'], 'reason' => $result['reason']],
                    );

                    $result['ok']
                        ? Notification::make()->success()->title('✅ التحقق ناجح — الأرشيف سليم بالكامل')->send()
                        : Notification::make()->danger()->title('❌ فشل التحقق')->body($result['reason'])->persistent()->send();
                }),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Exceptions\Backup\BackupIntegrityException;
use App\Exceptions\Backup\BackupManifestException;
use App\Exceptions\Backup\BackupNotFoundException;
use App\Exceptions\Backup\BackupRestoreException;
use App\Filament\Resources\BackupResource\Pages;
use App\Models\ActivityLog;
use App\Models\Backup;
use App\Services\Backup\BackupInspectionService;
use App\Services\Backup\RestoreService;
use App\Services\Backup\SystemBackupService;
use App\Support\Enums\BackupStatus;
use App\Support\Enums\BackupTrigger;
use App\Support\Enums\BackupType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Throwable;

/**
 * BackupResource — "النسخ الاحتياطية" (super_admin فقط).
 *
 * ⚠️ منفصل تماماً عن DataExportRequest (تصدير بيانات مستخدم واحد). هذا يغطي
 * نسخ النظام الكاملة. راجع docs/BACKUP-SYSTEM.md قبل التعديل.
 *
 * ⚠️ Restore (المرحلة الرابعة): زر "استعادة النسخة" هنا هو **واجهة فقط** فوق
 * محرك الاستعادة الحالي — لا يحتوي على أي منطق استعادة بنفسه، ويستدعي حصراً
 * RestoreService::run() (نفس المحرك الذي يستخدمه backup:restore CLI). لا
 * تعديل على RestoreService/DatabaseRestoreService/FilesRestoreService هنا
 * إطلاقاً. راجع docs/RESTORE-RUNBOOK.md.
 */
class BackupResource extends Resource
{
    protected static ?string $model = Backup::class;

    protected static ?string $navigationIcon   = 'heroicon-o-circle-stack';
    protected static ?string $navigationGroup  = 'النظام';
    protected static ?string $navigationLabel  = 'النسخ الاحتياطية';
    protected static ?string $modelLabel       = 'نسخة احتياطية';
    protected static ?string $pluralModelLabel = 'النسخ الاحتياطية';
    protected static ?int    $navigationSort   = 20;

    // ── دفاع إضافي: super_admin فقط، رغم أن اللوحة كلها مقيَّدة بذلك أصلاً
    // عبر User::canAccessPanel() — راجع AdminPanelProvider. ──
    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function canAccess(): bool
    {
        return static::canViewAny();
    }

    public static function canView($record): bool
    {
        return static::canViewAny();
    }

    public static function getNavigationBadge(): ?string
    {
        $lastFailed = Backup::failedOnes()->latest('created_at')->first();
        return $lastFailed ? '!' : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    // لا نموذج إنشاء/تعديل عبر Filament Form — الإنشاء فقط عبر زر "إنشاء نسخة يدوية"
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (Backup $record) => static::getUrl('view', ['record' => $record]))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn (BackupType $state) => $state->label())
                    ->color(fn (BackupType $state) => $state === BackupType::Full ? 'primary' : 'gray'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (BackupStatus $state) => $state->label())
                    ->color(fn (BackupStatus $state) => $state->color()),

                Tables\Columns\TextColumn::make('triggered_by')
                    ->label('المصدر')
                    ->badge()
                    ->formatStateUsing(fn (?BackupTrigger $state) => $state?->label() ?? '—')
                    ->color(fn (?BackupTrigger $state) => $state?->color() ?? 'gray'),

                Tables\Columns\TextColumn::make('size_bytes')
                    ->label('الحجم')
                    ->formatStateUsing(fn (?int $state, Backup $record) => $record->humanSize() ?? '—'),

                Tables\Columns\TextColumn::make('disk')
                    ->label('مكان التخزين')
                    ->badge()
                    ->color(fn (?string $state) => $state === config('backups.system_backup.disk') && ! config('backups.system_backup.disk_is_offsite') ? 'warning' : 'success')
                    ->tooltip(fn (?string $state) => $state && ! config('backups.system_backup.disk_is_offsite')
                        ? 'تخزين محلي فقط — راجع docs/BACKUP-SYSTEM.md بخصوص تفعيل نسخة خارج الخادم'
                        : null)
                    ->default('—'),

                Tables\Columns\TextColumn::make('checksum')
                    ->label('Checksum')
                    ->limit(12)
                    ->fontFamily('mono')
                    ->copyable()
                    ->copyMessage('تم نسخ الـ checksum كاملاً')
                    ->tooltip(fn (?string $state) => $state)
                    ->default('—'),

                Tables\Columns\IconColumn::make('integrity_verified')
                    ->label('السلامة')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->default(null),

                Tables\Columns\TextColumn::make('duration_seconds')
                    ->label('المدة')
                    ->formatStateUsing(fn (?int $state) => $state !== null ? "{$state}ث" : '—'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('triggeredBy.name')
                    ->label('مُنشئ النسخة')
                    ->default('جدولة تلقائية'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('النوع')
                    ->options(['database' => 'قاعدة بيانات', 'full' => 'كاملة']),

                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending'   => 'قيد الانتظار',
                        'running'   => 'جارٍ التنفيذ',
                        'completed' => 'مكتملة',
                        'failed'    => 'فشلت',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('تنزيل')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->visible(fn (Backup $record) => $record->status === BackupStatus::Completed)
                    ->url(fn (Backup $record) => URL::temporarySignedRoute(
                        'admin.backups.download',
                        now()->addMinutes(15),
                        ['backup' => $record->id]
                    ))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('verify')
                    ->label('فحص السلامة')
                    ->icon('heroicon-o-shield-check')
                    ->color('info')
                    ->visible(fn (Backup $record) => $record->status === BackupStatus::Completed)
                    ->requiresConfirmation()
                    ->modalHeading('فحص سلامة النسخة')
                    ->modalDescription('سيُعاد تنزيل الملف مؤقتاً من disk التخزين ومقارنة checksum. قد يستغرق بعض الوقت للملفات الكبيرة.')
                    ->action(function (Backup $record): void {
                        $ok = app(SystemBackupService::class)->verifyIntegrity($record);

                        ActivityLog::recordFor(
                            eventType: 'backup.integrity_checked',
                            entityType: Backup::class,
                            entityId: $record->id,
                            metadata: ['verified' => $ok],
                        );

                        $ok
                            ? Notification::make()->success()->title('✅ سلامة النسخة مؤكَّدة — checksum مطابق')->send()
                            : Notification::make()->danger()->title('❌ فشل التحقق — checksum غير مطابق أو الملف مفقود')->persistent()->send();
                    }),

                Tables\Actions\Action::make('restore')
                    ->label('استعادة النسخة')
                    ->icon('heroicon-o-arrow-path')
                    ->color('danger')
                    ->visible(fn (Backup $record) => $record->status === BackupStatus::Completed && $record->integrity_verified === true)
                    ->modalHeading('استعادة نسخة احتياطية')
                    ->modalIcon('heroicon-o-exclamation-triangle')
                    ->modalIconColor('danger')
                    ->modalSubmitActionLabel('نعم، ابدأ الاستعادة الآن')
                    ->modalWidth('lg')
                    ->modalContent(fn (Backup $record) => view(
                        'filament.backups.restore-modal-content',
                        static::restoreModalViewData($record)
                    ))
                    ->form(fn (): array => [
                        Forms\Components\TextInput::make('confirmation')
                            ->label('اكتب RESTORE للتأكيد')
                            ->placeholder('RESTORE')
                            ->required()
                            ->autocomplete('off')
                            ->extraInputAttributes(['class' => 'font-mono'])
                            ->rule('in:RESTORE')
                            ->validationMessages([
                                'in' => 'يجب كتابة RESTORE بحروف كبيرة بالضبط للمتابعة، بدون أي فراغات إضافية.',
                            ]),
                    ])
                    ->action(fn (Backup $record) => static::runRestoreAction($record)),

                Tables\Actions\Action::make('delete')
                    ->label('حذف')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('حذف نسخة احتياطية نهائياً')
                    ->modalDescription('سيُحذف الملف من التخزين ولا يمكن التراجع. تأكد أن لديك نسخة أخرى صالحة قبل الحذف.')
                    ->modalSubmitActionLabel('نعم، احذف نهائياً')
                    ->form([
                        \Filament\Forms\Components\Checkbox::make('confirm')
                            ->label('أؤكد حذف هذه النسخة بشكل نهائي')
                            ->required()
                            ->accepted(),
                    ])
                    ->action(function (Backup $record): void {
                        if ($record->disk && $record->path) {
                            \Illuminate\Support\Facades\Storage::disk($record->disk)->delete($record->path);
                        }

                        ActivityLog::recordFor(
                            eventType: 'backup.deleted',
                            entityType: Backup::class,
                            entityId: $record->id,
                            metadata: ['name' => $record->name, 'type' => $record->type->value],
                        );

                        $record->delete();

                        Notification::make()->success()->title('تم حذف النسخة')->send();
                    }),
            ])
            ->bulkActions([])
            ->poll('15s');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBackups::route('/'),
            'view'  => Pages\ViewBackup::route('/{record}'),
        ];
    }

    /**
     * يبني بيانات view محتوى Modal الاستعادة (resources/views/filament/backups/
     * restore-modal-content.blade.php) — عرض فقط، بدون أي Filament Form
     * Component. المعلومات تُقرَأ عبر BackupInspectionService::readManifest()
     * الحالية دون أي تكرار لمنطقها.
     *
     * ⚠️ هذه الدالة تُستدعى من داخل Closure جديد في كل مرة يُفتَح فيها الـModal
     * (->modalContent(fn (Backup $record) => view(...)))، ولا تُنشئ أو تُعيد
     * استخدام أي Filament Form Component — array عادي بسيط فقط، لذلك لا علاقة
     * لها بمشكلة "$container must not be accessed before initialization"
     * (كانت ناتجة سابقاً عن بناء Placeholder/Section عبر ->form() + محاولة
     * تعطيل زر الإرسال تفاعلياً عبر ->modalSubmitAction()، وكلاهما أُزيلا).
     *
     * @return array<string,mixed>
     */
    private static function restoreModalViewData(Backup $record): array
    {
        $manifest = [];
        $fileCount = null;
        $inspectionError = null;

        try {
            $inspection = app(BackupInspectionService::class)->readManifest($record);
            $manifest = $inspection['manifest'] ?? [];
            $fileCount = $inspection['file_count'] ?? null;
        } catch (Throwable $e) {
            $inspectionError = $e->getMessage();
        }

        return [
            'record'          => $record,
            'manifest'        => $manifest,
            'fileCount'       => $fileCount,
            'inspectionError' => $inspectionError,
        ];
    }

    /**
     * ⚠️ لا منطق استعادة هنا — استدعاء وحيد لـ RestoreService::run()، محاط
     * فقط بقفل Cache (يمنع تشغيل عمليتين استعادة في آن واحد) وإشعارات
     * Filament. أي منطق فعلي (checksum/فك تشفير/استعادة DB أو الملفات) يعيش
     * حصراً داخل RestoreService وما تستدعيه.
     */
    private static function runRestoreAction(Backup $record): mixed
    {
        $lock = Cache::lock('backups:restore-lock', 3600);

        if (! $lock->get()) {
            Notification::make()
                ->warning()
                ->title('يوجد بالفعل عملية استعادة أخرى قيد التنفيذ')
                ->body('يرجى الانتظار حتى انتهائها قبل المحاولة مرة أخرى.')
                ->send();

            return null;
        }

        try {
            app(RestoreService::class)->run($record->id);
        } catch (BackupNotFoundException|BackupIntegrityException|BackupManifestException|BackupRestoreException $e) {
            Notification::make()
                ->danger()
                ->title('فشلت عملية الاستعادة')
                ->body($e->getMessage())
                ->persistent()
                ->send();

            return null;
        } finally {
            $lock->release();
        }

        ActivityLog::recordFor(
            eventType: 'backup.restored',
            entityType: Backup::class,
            entityId: $record->id,
            metadata: ['backup_id' => $record->id],
        );

        Notification::make()
            ->success()
            ->title('تمت استعادة النسخة الاحتياطية بنجاح.')
            ->send();

        return redirect(request()->header('Referer') ?? static::getUrl('index'));
    }
}

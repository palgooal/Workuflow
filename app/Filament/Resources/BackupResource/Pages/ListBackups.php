<?php

namespace App\Filament\Resources\BackupResource\Pages;

use App\Filament\Resources\BackupResource;
use App\Jobs\Backup\RunSystemBackupJob;
use App\Models\ActivityLog;
use App\Models\Backup;
use App\Support\Enums\BackupStatus;
use App\Support\Enums\BackupType;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListBackups extends ListRecords
{
    protected static string $resource = BackupResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('الكل'),

            'database' => Tab::make('قاعدة بيانات')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', BackupType::Database->value)),

            'full' => Tab::make('كاملة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', BackupType::Full->value)),

            'failed' => Tab::make('فاشلة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', BackupStatus::Failed->value))
                ->badge(Backup::failedOnes()->count())
                ->badgeColor('danger'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('createManual')
                ->label('إنشاء نسخة يدوية')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->form([
                    Forms\Components\Select::make('type')
                        ->label('نوع النسخة')
                        ->options([
                            'database' => 'قاعدة بيانات فقط (أسرع)',
                            'full'     => 'نسخة كاملة (قاعدة بيانات + ملفات المرفقات)',
                        ])
                        ->default('database')
                        ->required(),
                ])
                ->requiresConfirmation()
                ->modalHeading('إنشاء نسخة احتياطية يدوية')
                ->modalDescription('سيُشغَّل التنفيذ في الخلفية (طابور backups) ولن يُبطئ الموقع. النسخة تُشفَّر تلقائياً قبل التخزين.')
                ->modalSubmitActionLabel('إنشاء')
                ->action(function (array $data): void {
                    $type = BackupType::from($data['type']);

                    $backup = Backup::create([
                        'name'                  => 'manual-'.$type->value.'-'.now()->format('Ymd-His'),
                        'type'                  => $type,
                        'status'                => BackupStatus::Pending,
                        'triggered_by_user_id'  => auth()->id(),
                    ]);

                    RunSystemBackupJob::dispatch($backup->id);

                    ActivityLog::recordFor(
                        eventType: 'backup.requested',
                        entityType: Backup::class,
                        entityId: $backup->id,
                        metadata: ['type' => $type->value],
                    );

                    Notification::make()
                        ->success()
                        ->title('تم إطلاق النسخة الاحتياطية')
                        ->body('ستظهر في القائمة بمجرد بدء المعالجة — قد يستغرق الأمر بضع دقائق.')
                        ->send();
                }),
        ];
    }
}

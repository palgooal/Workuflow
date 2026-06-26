<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static ?string $navigationIcon  = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'النظام';
    protected static ?string $navigationLabel = 'سجل النشاط';
    protected static ?string $modelLabel      = 'نشاط';
    protected static ?string $pluralModelLabel = 'سجل النشاط';
    protected static ?int    $navigationSort  = 10;

    // عدد الأحداث الجديدة اليوم كـ badge
    public static function getNavigationBadge(): ?string
    {
        return (string) ActivityLog::whereDate('created_at', today())->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'gray';
    }

    // قراءة فقط — لا إنشاء ولا تعديل
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }
    public static function canDeleteAny(): bool { return false; }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('الوقت')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->size('sm'),

                Tables\Columns\TextColumn::make('event_type')
                    ->label('الحدث')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_starts_with($state, 'auth.')        => 'info',
                        str_starts_with($state, 'payment.')     => 'success',
                        str_starts_with($state, 'subscription.') => 'warning',
                        str_starts_with($state, 'invoice.')     => 'gray',
                        str_starts_with($state, 'project.')     => 'primary',
                        str_starts_with($state, 'client.')      => 'gray',
                        default                                  => 'gray',
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->description(fn (ActivityLog $record): ?string => $record->user?->email)
                    ->searchable(['users.name', 'users.email'])
                    ->default('—'),

                Tables\Columns\TextColumn::make('entity_type')
                    ->label('الكيان')
                    ->formatStateUsing(fn (?string $state): string => $state
                        ? class_basename($state)
                        : '—'
                    )
                    ->size('sm'),

                Tables\Columns\TextColumn::make('entity_id')
                    ->label('ID الكيان')
                    ->copyable()
                    ->size('sm')
                    ->default('—'),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->size('sm')
                    ->default('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_type')
                    ->label('نوع الحدث')
                    ->options([
                        'auth.login'             => 'تسجيل دخول',
                        'auth.logout'            => 'تسجيل خروج',
                        'payment.succeeded'      => 'دفع ناجح',
                        'payment.failed'         => 'فشل دفع',
                        'subscription.upgraded'  => 'ترقية اشتراك',
                        'subscription.downgraded'=> 'تخفيض اشتراك',
                        'subscription.expired'   => 'انتهاء اشتراك',
                        'invoice.sent'           => 'إرسال فاتورة',
                        'project.created'        => 'إنشاء مشروع',
                        'project.deleted'        => 'حذف مشروع',
                        'client.created'         => 'إضافة عميل',
                        'client.deleted'         => 'حذف عميل',
                        'admin.action'           => 'إجراء أدمن',
                    ]),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label('المستخدم')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('date_range')
                    ->label('نطاق التاريخ')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('من'),
                        \Filament\Forms\Components\DatePicker::make('until')->label('إلى'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'],  fn ($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('created_at', '<=', $data['until']));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view_metadata')
                    ->label('البيانات')
                    ->icon('heroicon-o-code-bracket')
                    ->color('gray')
                    ->modalHeading('بيانات الحدث')
                    ->modalContent(fn (ActivityLog $record) => view(
                        'filament.modals.activity-log-metadata',
                        ['log' => $record]
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('إغلاق')
                    ->visible(fn (ActivityLog $record): bool => ! empty($record->metadata)),
            ])
            ->bulkActions([])
            ->poll('60s');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }
}

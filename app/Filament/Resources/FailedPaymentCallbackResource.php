<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FailedPaymentCallbackResource\Pages;
use App\Models\FailedPaymentCallback;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FailedPaymentCallbackResource extends Resource
{
    protected static ?string $model           = FailedPaymentCallback::class;
    protected static ?string $navigationIcon  = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationGroup = 'المدفوعات';
    protected static ?string $navigationLabel = 'Callbacks فاشلة';
    protected static ?string $modelLabel      = 'Callback فاشل';
    protected static ?string $pluralModelLabel = 'Callbacks فاشلة';
    protected static ?int    $navigationSort  = 3;

    public static function getNavigationBadge(): ?string
    {
        $unresolved = static::getModel()::where('resolved', false)->count();
        return $unresolved > 0 ? (string) $unresolved : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }

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

                Tables\Columns\TextColumn::make('provider')
                    ->label('المزود')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('order_id')
                    ->label('Order ID')
                    ->fontFamily('mono')
                    ->copyable()
                    ->copyMessage('تم النسخ')
                    ->default('—')
                    ->size('sm'),

                Tables\Columns\TextColumn::make('retries')
                    ->label('المحاولات')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 3 ? 'danger' : ($state >= 1 ? 'warning' : 'gray'))
                    ->sortable(),

                Tables\Columns\IconColumn::make('resolved')
                    ->label('تمّ الحل')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('processed_at')
                    ->label('وقت المعالجة')
                    ->dateTime('Y-m-d H:i:s')
                    ->placeholder('—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('exception')
                    ->label('الخطأ')
                    ->limit(60)
                    ->tooltip(fn (FailedPaymentCallback $record): string => $record->exception ?? '')
                    ->size('sm'),
            ])
            ->filters([
                Tables\Filters\Filter::make('unresolved')
                    ->label('غير محلولة')
                    ->query(fn (Builder $query) => $query->where('resolved', false))
                    ->default(),

                Tables\Filters\SelectFilter::make('provider')
                    ->label('المزود')
                    ->options(['togo' => 'Togo.ps']),
            ])
            ->actions([
                // ── View Payload ──
                Tables\Actions\Action::make('view_payload')
                    ->label('Payload')
                    ->icon('heroicon-o-code-bracket')
                    ->color('gray')
                    ->modalHeading('بيانات الـ Callback')
                    ->modalContent(fn (FailedPaymentCallback $record) => view(
                        'filament.modals.failed-callback-payload',
                        ['callback' => $record]
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('إغلاق'),

                // ── Retry Processing ──
                Tables\Actions\Action::make('retry')
                    ->label('إعادة المحاولة')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('إعادة معالجة الـ Callback')
                    ->modalDescription('سيُزاد عداد المحاولات. المعالجة الفعلية تتم يدوياً من فريق التقنية.')
                    ->action(function (FailedPaymentCallback $record): void {
                        $record->incrementRetry();

                        Notification::make()
                            ->warning()
                            ->title('تم تسجيل إعادة المحاولة')
                            ->body("المحاولات الآن: {$record->fresh()->retries}")
                            ->send();
                    })
                    ->visible(fn (FailedPaymentCallback $record): bool => ! $record->resolved),

                // ── Mark Resolved ──
                Tables\Actions\Action::make('resolve')
                    ->label('تم الحل')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد الحل')
                    ->modalDescription('سيُعلَّم الـ callback كـ «محلول». لا يمكن التراجع.')
                    ->action(function (FailedPaymentCallback $record): void {
                        $record->markResolved();

                        Notification::make()
                            ->success()
                            ->title('تم تعليم الـ callback كمحلول')
                            ->send();
                    })
                    ->visible(fn (FailedPaymentCallback $record): bool => ! $record->resolved),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFailedPaymentCallbacks::route('/'),
        ];
    }
}

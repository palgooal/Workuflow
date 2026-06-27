<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReferralPayoutResource\Pages;
use App\Modules\Referral\Enums\PayoutStatus;
use App\Modules\Referral\Models\Affiliate;
use App\Modules\Referral\Models\ReferralPayout;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use App\Modules\Referral\Notifications\PayoutProcessedNotification;
use Illuminate\Support\Facades\Log;

class ReferralPayoutResource extends Resource
{
    protected static ?string $model            = ReferralPayout::class;
    protected static ?string $navigationIcon   = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup  = 'الإحالات';
    protected static ?string $navigationLabel  = 'طلبات الصرف';
    protected static ?string $modelLabel       = 'طلب صرف';
    protected static ?string $pluralModelLabel = 'طلبات الصرف';
    protected static ?int    $navigationSort   = 3;

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::whereIn('status', ['requested', 'processing'])->count();
        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }

    // ── Form ─────────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    // ── Table ─────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('affiliate')->latest('requested_at'))
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ULID')
                    ->fontFamily('mono')
                    ->formatStateUsing(fn (string $state): string => substr($state, 0, 10) . '…')
                    ->copyable()
                    ->copyableState(fn (ReferralPayout $r): string => $r->id)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('affiliate.name')
                    ->label('المسوّق')
                    ->searchable()
                    ->sortable()
                    ->description(fn (ReferralPayout $r): string =>
                        $r->affiliate?->email ?? '—'
                    ),

                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('usd')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('method')
                    ->label('الطريقة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match (is_object($state) ? $state->value : $state) {
                        'bank'      => 'تحويل بنكي',
                        'whatsapp'  => 'واتساب باي',
                        'credit'    => 'رصيد اشتراك',
                        default     => $state,
                    })
                    ->color('info'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state instanceof PayoutStatus ? $state->value : $state) {
                        'requested'  => 'مطلوب',
                        'processing' => 'قيد المعالجة',
                        'paid'       => 'مدفوع',
                        'rejected'   => 'مرفوض',
                        default      => $state,
                    })
                    ->color(fn ($state) => match ($state instanceof PayoutStatus ? $state->value : $state) {
                        'requested'  => 'warning',
                        'processing' => 'info',
                        'paid'       => 'success',
                        'rejected'   => 'danger',
                        default      => 'gray',
                    }),

                Tables\Columns\TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('requested_at')
                    ->label('تاريخ الطلب')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('processed_at')
                    ->label('تاريخ المعالجة')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->defaultSort('requested_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'requested'  => 'مطلوب',
                        'processing' => 'قيد المعالجة',
                        'paid'       => 'مدفوع',
                        'rejected'   => 'مرفوض',
                    ]),

                Tables\Filters\SelectFilter::make('method')
                    ->label('الطريقة')
                    ->options([
                        'bank'     => 'تحويل بنكي',
                        'whatsapp' => 'واتساب باي',
                        'credit'   => 'رصيد اشتراك',
                    ]),

                Tables\Filters\SelectFilter::make('affiliate_id')
                    ->label('المسوّق')
                    ->options(Affiliate::active()->pluck('name', 'id'))
                    ->searchable(),
            ])
            ->actions([
                // ── Mark as Processing ──
                Tables\Actions\Action::make('processing')
                    ->label('قيد المعالجة')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('تعليم الطلب كـ "قيد المعالجة"')
                    ->modalDescription(fn (ReferralPayout $r): string =>
                        "سيُعلَّم طلب صرف {$r->affiliate?->name} (${$r->amount}) كـ «قيد المعالجة»."
                    )
                    ->action(function (ReferralPayout $record): void {
                        $record->update(['status' => 'processing']);

                        Notification::make()->info()
                            ->title('الطلب قيد المعالجة')
                            ->send();
                    })
                    ->visible(fn (ReferralPayout $r): bool =>
                        ($r->status instanceof PayoutStatus ? $r->status->value : $r->status) === 'requested'
                    ),

                // ── Mark as Paid ──
                Tables\Actions\Action::make('mark_paid')
                    ->label('تأكيد الصرف')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد صرف المبلغ')
                    ->modalDescription(fn (ReferralPayout $r): string =>
                        "ستُعلَّم العملية كمنجزة وسيُزاد total_paid لـ {$r->affiliate?->name} بمقدار \${$r->amount}."
                    )
                    ->modalSubmitActionLabel('تأكيد الصرف')
                    ->action(function (ReferralPayout $record): void {
                        DB::transaction(function () use ($record): void {
                            $record->update([
                                'status'       => 'paid',
                                'processed_at' => now(),
                            ]);

                            // تحديث total_paid في جدول affiliates
                            $record->affiliate?->increment('total_paid', $record->amount);
                        });

                        Log::info('Admin confirmed payout paid', [
                            'payout_id'    => $record->id,
                            'affiliate_id' => $record->affiliate_id,
                            'amount'       => $record->amount,
                            'admin'        => auth()->id(),
                        ]);

                        // إشعار المسوّق بتأكيد الصرف
                        $record->affiliate?->user?->notify(
                            new PayoutProcessedNotification($record, approved: true)
                        );

                        Notification::make()->success()
                            ->title('تم تأكيد الصرف')
                            ->body("تم صرف \${$record->amount} لـ {$record->affiliate?->name}.")
                            ->send();
                    })
                    ->visible(fn (ReferralPayout $r): bool =>
                        in_array(
                            $r->status instanceof PayoutStatus ? $r->status->value : $r->status,
                            ['requested', 'processing']
                        )
                    ),

                // ── Reject Payout ──
                Tables\Actions\Action::make('reject')
                    ->label('رفض')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('رفض طلب الصرف')
                    ->modalDescription('سيُرفض الطلب ويعود الرصيد للمسوّق تلقائياً عبر Reconciliation اليومي.')
                    ->modalSubmitActionLabel('رفض الطلب')
                    ->action(function (ReferralPayout $record): void {
                        $record->update([
                            'status'       => 'rejected',
                            'processed_at' => now(),
                        ]);

                        Log::warning('Admin rejected payout', [
                            'payout_id'    => $record->id,
                            'affiliate_id' => $record->affiliate_id,
                            'admin'        => auth()->id(),
                        ]);

                        // إشعار المسوّق برفض طلب الصرف
                        $record->affiliate?->user?->notify(
                            new PayoutProcessedNotification($record, approved: false)
                        );

                        Notification::make()->warning()
                            ->title('تم رفض طلب الصرف')
                            ->send();
                    })
                    ->visible(fn (ReferralPayout $r): bool =>
                        in_array(
                            $r->status instanceof PayoutStatus ? $r->status->value : $r->status,
                            ['requested', 'processing']
                        )
                    ),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReferralPayouts::route('/'),
        ];
    }
}

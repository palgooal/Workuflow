<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReferralCommissionResource\Pages;
use App\Modules\Referral\Enums\CommissionStatus;
use App\Modules\Referral\Models\Affiliate;
use App\Modules\Referral\Models\ReferralCommission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReferralCommissionResource extends Resource
{
    protected static ?string $model            = ReferralCommission::class;
    protected static ?string $navigationIcon   = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup  = 'الإحالات';
    protected static ?string $navigationLabel  = 'العمولات';
    protected static ?string $modelLabel       = 'عمولة';
    protected static ?string $pluralModelLabel = 'العمولات';
    protected static ?int    $navigationSort   = 2;

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::where('status', 'pending')
            ->where('fraud_flagged', false)
            ->count();
        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
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
            ->modifyQueryUsing(fn ($query) => $query->with(['affiliate', 'referredUser'])->latest())
            ->columns([
                // fraud_flagged — أول عمود لسهولة المسح البصري
                Tables\Columns\IconColumn::make('fraud_flagged')
                    ->label('احتيال')
                    ->boolean()
                    ->trueIcon('heroicon-s-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->tooltip(fn (ReferralCommission $r): string =>
                        $r->fraud_flagged ? 'مشتبه بعمولة احتيالية' : 'عمولة نظيفة'
                    )
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('id')
                    ->label('ULID')
                    ->fontFamily('mono')
                    ->formatStateUsing(fn (string $state): string => substr($state, 0, 10) . '…')
                    ->copyable()
                    ->copyableState(fn (ReferralCommission $r): string => $r->id)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('affiliate.name')
                    ->label('المسوّق')
                    ->searchable()
                    ->sortable()
                    ->description(fn (ReferralCommission $r): string =>
                        $r->affiliate?->display_code ?? $r->affiliate_id
                    ),

                Tables\Columns\TextColumn::make('referredUser.name')
                    ->label('المُحال')
                    ->searchable()
                    ->description(fn (ReferralCommission $r): string =>
                        $r->referredUser?->email ?? '—'
                    ),

                Tables\Columns\TextColumn::make('subscription_plan')
                    ->label('الخطة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => strtoupper($state ?? '—'))
                    ->color('primary'),

                Tables\Columns\TextColumn::make('subscription_cycle')
                    ->label('الدورة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'monthly' => 'شهري',
                        'annual'  => 'سنوي',
                        default   => $state ?? '—',
                    })
                    ->color('gray'),

                Tables\Columns\TextColumn::make('subscription_amount')
                    ->label('الاشتراك')
                    ->money('usd')
                    ->sortable(),

                Tables\Columns\TextColumn::make('rate')
                    ->label('النسبة')
                    ->suffix('٪')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('العمولة')
                    ->money('usd')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state instanceof CommissionStatus ? $state->value : $state) {
                        'pending'  => 'معلّقة',
                        'approved' => 'معتمدة',
                        'paid'     => 'مدفوعة',
                        'rejected' => 'مرفوضة',
                        'on_hold'  => 'موقوفة',
                        default    => $state,
                    })
                    ->color(fn ($state) => match ($state instanceof CommissionStatus ? $state->value : $state) {
                        'pending'  => 'warning',
                        'approved' => 'success',
                        'paid'     => 'info',
                        'rejected' => 'danger',
                        'on_hold'  => 'gray',
                        default    => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending'  => 'معلّقة',
                        'approved' => 'معتمدة',
                        'paid'     => 'مدفوعة',
                        'rejected' => 'مرفوضة',
                        'on_hold'  => 'موقوفة',
                    ]),

                Tables\Filters\SelectFilter::make('affiliate_id')
                    ->label('المسوّق')
                    ->options(Affiliate::active()->pluck('name', 'id'))
                    ->searchable(),

                Tables\Filters\Filter::make('fraud_only')
                    ->label('المشتبه بها فقط')
                    ->query(fn ($query) => $query->where('fraud_flagged', true))
                    ->toggle(),

                Tables\Filters\SelectFilter::make('subscription_plan')
                    ->label('الخطة')
                    ->options(['pro' => 'Pro', 'business' => 'Business']),

                Tables\Filters\Filter::make('created_range')
                    ->label('نطاق التاريخ')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('من'),
                        Forms\Components\DatePicker::make('until')->label('حتى'),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'],  fn ($q) => $q->whereDate('created_at', '>=', $data['from']))
                        ->when($data['until'], fn ($q) => $q->whereDate('created_at', '<=', $data['until']))
                    ),
            ])
            ->actions([
                // ── Approve Commission ──
                Tables\Actions\Action::make('approve')
                    ->label('اعتماد')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('اعتماد العمولة')
                    ->modalDescription(fn (ReferralCommission $r): string =>
                        "ستُعتمد عمولة \${$r->amount} وتُضاف لرصيد {$r->affiliate?->name}."
                    )
                    ->modalSubmitActionLabel('اعتماد')
                    ->action(function (ReferralCommission $record): void {
                        DB::transaction(function () use ($record): void {
                            $record->update(['status' => 'approved']);

                            // زيادة total_earned عند الاعتماد
                            $record->affiliate?->increment('total_earned', $record->amount);
                        });

                        Log::info('Admin approved commission', [
                            'commission_id' => $record->id,
                            'amount'        => $record->amount,
                            'admin'         => auth()->id(),
                        ]);

                        Notification::make()->success()
                            ->title('تمت الموافقة على العمولة')
                            ->body("تمت إضافة \${$record->amount} لرصيد {$record->affiliate?->name}.")
                            ->send();
                    })
                    ->visible(fn (ReferralCommission $r): bool =>
                        in_array($r->status instanceof CommissionStatus ? $r->status->value : $r->status,
                            ['pending', 'on_hold'])
                    ),

                // ── Reject Commission ──
                Tables\Actions\Action::make('reject')
                    ->label('رفض')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('رفض العمولة')
                    ->modalDescription('ستُرفض العمولة ولن تُحتسب في رصيد المسوّق.')
                    ->modalSubmitActionLabel('رفض')
                    ->action(function (ReferralCommission $record): void {
                        $record->update(['status' => 'rejected']);

                        Log::warning('Admin rejected commission', [
                            'commission_id' => $record->id,
                            'admin'         => auth()->id(),
                        ]);

                        Notification::make()->warning()
                            ->title('تم رفض العمولة')
                            ->send();
                    })
                    ->visible(fn (ReferralCommission $r): bool =>
                        in_array($r->status instanceof CommissionStatus ? $r->status->value : $r->status,
                            ['pending', 'on_hold'])
                    ),

                // ── Hold / Unhold ──
                Tables\Actions\Action::make('hold')
                    ->label('تعليق')
                    ->icon('heroicon-o-pause-circle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(function (ReferralCommission $record): void {
                        $record->update(['status' => 'on_hold']);
                        Notification::make()->info()->title('تم تعليق العمولة')->send();
                    })
                    ->visible(fn (ReferralCommission $r): bool =>
                        ($r->status instanceof CommissionStatus ? $r->status->value : $r->status) === 'pending'
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('bulk_approve')
                    ->label('اعتماد المحدد')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($records): void {
                        foreach ($records as $record) {
                            $statusVal = $record->status instanceof CommissionStatus
                                ? $record->status->value : $record->status;

                            if (in_array($statusVal, ['pending', 'on_hold'])) {
                                DB::transaction(function () use ($record): void {
                                    $record->update(['status' => 'approved']);
                                    $record->affiliate?->increment('total_earned', $record->amount);
                                });
                            }
                        }

                        Notification::make()->success()->title('تم الاعتماد الجماعي')->send();
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReferralCommissions::route('/'),
        ];
    }
}

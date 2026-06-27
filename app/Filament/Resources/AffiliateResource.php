<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AffiliateResource\Pages;
use App\Modules\Referral\Enums\AffiliateStatus;
use App\Modules\Referral\Enums\AffiliateTier;
use App\Modules\Referral\Models\Affiliate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Modules\Referral\Notifications\AffiliateApprovedNotification;
use Illuminate\Support\Facades\Log;

class AffiliateResource extends Resource
{
    protected static ?string $model            = Affiliate::class;
    protected static ?string $navigationIcon   = 'heroicon-o-user-group';
    protected static ?string $navigationGroup  = 'الإحالات';
    protected static ?string $navigationLabel  = 'المسوّقون';
    protected static ?string $modelLabel       = 'مسوّق';
    protected static ?string $pluralModelLabel = 'المسوّقون';
    protected static ?int    $navigationSort   = 1;

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::where('status', 'pending')->count();
        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    // ── Form ─────────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('معلومات المسوّق')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('الاسم')
                    ->disabled(),

                Forms\Components\TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->disabled(),

                Forms\Components\TextInput::make('whatsapp')
                    ->label('واتساب')
                    ->disabled(),

                Forms\Components\TextInput::make('user.name')
                    ->label('حساب دراهم')
                    ->disabled(),
            ])->columns(2),

            Forms\Components\Section::make('إعدادات الشراكة')->schema([
                Forms\Components\TextInput::make('display_code')
                    ->label('كود الإحالة')
                    ->placeholder('مثل: AHMED2026')
                    ->maxLength(50)
                    ->unique(ignoreRecord: true)
                    ->helperText('الكود الذي يشاركه المسوّق — فريد في النظام'),

                Forms\Components\TextInput::make('commission_rate')
                    ->label('نسبة العمولة ٪')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->step(0.5)
                    ->suffix('٪')
                    ->helperText('تُعيَّن تلقائياً عند ترقية Tier — يمكن للأدمن تعديلها يدوياً'),

                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending'   => 'قيد المراجعة',
                        'active'    => 'نشط',
                        'suspended' => 'موقوف',
                    ])
                    ->required(),

                Forms\Components\Select::make('tier')
                    ->label('المستوى')
                    ->options([
                        'standard' => 'Standard (30٪)',
                        'silver'   => 'Silver (35٪)',
                        'gold'     => 'Gold (40٪)',
                        'platinum' => 'Platinum (45٪)',
                    ])
                    ->required(),

                Forms\Components\Select::make('payout_method')
                    ->label('طريقة الصرف')
                    ->options([
                        'bank'      => 'تحويل بنكي',
                        'whatsapp'  => 'واتساب باي',
                        'credit'    => 'رصيد اشتراك',
                    ])
                    ->nullable(),

                Forms\Components\Textarea::make('notes')
                    ->label('ملاحظات داخلية')
                    ->rows(2),
            ])->columns(2),

            Forms\Components\Section::make('الإجماليات (للقراءة فقط)')->schema([
                Forms\Components\TextInput::make('total_referrals')->label('مُحالون')->disabled(),
                Forms\Components\TextInput::make('total_converted')->label('مُشتركون')->disabled(),
                Forms\Components\TextInput::make('total_earned')->label('مكتسَب ($)')->disabled(),
                Forms\Components\TextInput::make('total_paid')->label('مصروف ($)')->disabled(),
            ])->columns(4),
        ]);
    }

    // ── Table ─────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('user')->latest())
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ULID')
                    ->fontFamily('mono')
                    ->formatStateUsing(fn (string $state): string => substr($state, 0, 10) . '…')
                    ->copyable()
                    ->copyableState(fn (Affiliate $r): string => $r->id)
                    ->copyMessage('تم نسخ ULID')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Affiliate $r): string => $r->email),

                Tables\Columns\TextColumn::make('display_code')
                    ->label('كود الإحالة')
                    ->copyable()
                    ->copyMessage('تم نسخ الكود')
                    ->placeholder('—')
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state instanceof AffiliateStatus ? $state->value : $state) {
                        'pending'   => 'قيد المراجعة',
                        'active'    => 'نشط',
                        'suspended' => 'موقوف',
                        default     => $state,
                    })
                    ->color(fn ($state) => match ($state instanceof AffiliateStatus ? $state->value : $state) {
                        'pending'   => 'warning',
                        'active'    => 'success',
                        'suspended' => 'danger',
                        default     => 'gray',
                    }),

                Tables\Columns\TextColumn::make('tier')
                    ->label('Tier')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state instanceof AffiliateTier ? $state->value : $state) {
                        'standard' => 'Standard',
                        'silver'   => 'Silver',
                        'gold'     => 'Gold',
                        'platinum' => 'Platinum',
                        default    => $state,
                    })
                    ->color(fn ($state) => match ($state instanceof AffiliateTier ? $state->value : $state) {
                        'standard' => 'gray',
                        'silver'   => 'info',
                        'gold'     => 'warning',
                        'platinum' => 'primary',
                        default    => 'gray',
                    }),

                Tables\Columns\TextColumn::make('commission_rate')
                    ->label('العمولة')
                    ->suffix('٪')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_referrals')
                    ->label('مُحالون')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_converted')
                    ->label('مُشتركون')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_earned')
                    ->label('مكتسَب')
                    ->money('usd')
                    ->sortable(),

                Tables\Columns\TextColumn::make('balance')
                    ->label('الرصيد')
                    ->money('usd')
                    ->state(fn (Affiliate $r): float => $r->balance)
                    ->color(fn (Affiliate $r): string => $r->balance > 0 ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ التقديم')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending'   => 'قيد المراجعة',
                        'active'    => 'نشط',
                        'suspended' => 'موقوف',
                    ]),

                Tables\Filters\SelectFilter::make('tier')
                    ->label('المستوى')
                    ->options([
                        'standard' => 'Standard',
                        'silver'   => 'Silver',
                        'gold'     => 'Gold',
                        'platinum' => 'Platinum',
                    ]),
            ])
            ->actions([
                // ── Approve ──
                Tables\Actions\Action::make('approve')
                    ->label('اعتماد')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('اعتماد حساب المسوّق')
                    ->modalDescription(fn (Affiliate $r): string =>
                        "ستُفعَّل حساب {$r->name} ويبدأ في كسب العمولات."
                    )
                    ->modalSubmitActionLabel('اعتماد')
                    ->action(function (Affiliate $record): void {
                        $record->update([
                            'status'      => 'active',
                            'approved_at' => now(),
                        ]);

                        Log::info('Admin approved affiliate', [
                            'affiliate_id' => $record->id,
                            'admin'        => auth()->id(),
                        ]);

                        // إشعار المسوّق باعتماد حسابه
                        $record->user?->notify(new AffiliateApprovedNotification($record));

                        Notification::make()->success()
                            ->title('تم اعتماد المسوّق')
                            ->body("{$record->name} أصبح نشطاً.")
                            ->send();
                    })
                    ->visible(fn (Affiliate $r): bool => $r->status->value === 'pending'),

                // ── Reject ──
                Tables\Actions\Action::make('reject')
                    ->label('رفض')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('رفض طلب الانضمام')
                    ->modalDescription(fn (Affiliate $r): string =>
                        "سيُحذف حساب {$r->name} من قاعدة البيانات. لا يمكن التراجع."
                    )
                    ->modalSubmitActionLabel('رفض وحذف')
                    ->action(function (Affiliate $record): void {
                        Log::info('Admin rejected affiliate application', [
                            'affiliate_id' => $record->id,
                            'name'         => $record->name,
                            'admin'        => auth()->id(),
                        ]);

                        $record->delete();

                        Notification::make()->warning()
                            ->title('تم رفض الطلب وحذفه')
                            ->send();
                    })
                    ->visible(fn (Affiliate $r): bool => $r->status->value === 'pending'),

                // ── Suspend ──
                Tables\Actions\Action::make('suspend')
                    ->label('إيقاف')
                    ->icon('heroicon-o-no-symbol')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('إيقاف حساب المسوّق')
                    ->modalDescription(fn (Affiliate $r): string =>
                        "سيُوقف حساب {$r->name} — لن يتمكن من كسب عمولات جديدة."
                    )
                    ->modalSubmitActionLabel('إيقاف')
                    ->action(function (Affiliate $record): void {
                        $record->update([
                            'status'       => 'suspended',
                            'suspended_at' => now(),
                        ]);

                        Log::warning('Admin suspended affiliate', [
                            'affiliate_id' => $record->id,
                            'admin'        => auth()->id(),
                        ]);

                        Notification::make()->warning()
                            ->title('تم إيقاف المسوّق')
                            ->send();
                    })
                    ->visible(fn (Affiliate $r): bool => $r->status->value === 'active'),

                // ── Reactivate ──
                Tables\Actions\Action::make('reactivate')
                    ->label('إعادة تفعيل')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function (Affiliate $record): void {
                        $record->update([
                            'status'       => 'active',
                            'suspended_at' => null,
                        ]);

                        Notification::make()->success()
                            ->title('تم إعادة تفعيل المسوّق')
                            ->send();
                    })
                    ->visible(fn (Affiliate $r): bool => $r->status->value === 'suspended'),

                Tables\Actions\EditAction::make()->label('تعديل'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAffiliates::route('/'),
            'edit'  => Pages\EditAffiliate::route('/{record}/edit'),
        ];
    }
}

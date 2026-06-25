<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Models\Subscription;
use App\Models\User;
use App\Modules\Billing\Services\SubscriptionService;
use App\Support\Enums\SubscriptionPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;
    protected static ?string $navigationIcon  = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'إدارة المستخدمين';
    protected static ?string $navigationLabel = 'الاشتراكات';
    protected static ?string $modelLabel      = 'اشتراك';
    protected static ?string $pluralModelLabel = 'الاشتراكات';
    protected static ?int    $navigationSort  = 2;

    // =====================================================
    // Form — لا يُستخدم للإنشاء (managed via Actions فقط)
    // =====================================================
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('تفاصيل الاشتراك')->schema([
                Forms\Components\Select::make('user_id')
                    ->label('المستخدم')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('plan')
                    ->label('الخطة')
                    ->options([
                        SubscriptionPlan::Free->value     => 'مجاني',
                        SubscriptionPlan::Pro->value      => 'Pro ⚡',
                        SubscriptionPlan::Business->value => 'Business 🚀',
                    ])
                    ->required(),

                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'active'    => 'نشط',
                        'cancelled' => 'ملغى',
                        'expired'   => 'منتهي',
                    ])
                    ->required(),

                Forms\Components\Select::make('payment_provider')
                    ->label('مزود الدفع')
                    ->options([
                        'manual' => 'يدوي (Manual)',
                        'tap'    => 'Tap',
                        'paddle' => 'Paddle',
                        'stripe' => 'Stripe',
                    ])
                    ->default('manual'),

                Forms\Components\DateTimePicker::make('starts_at')
                    ->label('تاريخ البدء')
                    ->default(now()),

                Forms\Components\DateTimePicker::make('ends_at')
                    ->label('تاريخ الانتهاء'),
            ])->columns(2),
        ]);
    }

    // =====================================================
    // Table
    // =====================================================
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('user')->latest())
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable(['users.name', 'users.email'])
                    ->sortable()
                    ->description(fn (Subscription $record): string => $record->user?->email ?? ''),

                Tables\Columns\BadgeColumn::make('plan')
                    ->label('الخطة')
                    ->formatStateUsing(fn ($state) => match (true) {
                        $state instanceof SubscriptionPlan => $state->label(),
                        is_string($state) => SubscriptionPlan::tryFrom($state)?->label() ?? $state,
                        default => (string) $state,
                    })
                    ->colors([
                        'gray'    => fn ($state) => $state === SubscriptionPlan::Free     || $state === 'free',
                        'primary' => fn ($state) => $state === SubscriptionPlan::Pro      || $state === 'pro',
                        'success' => fn ($state) => $state === SubscriptionPlan::Business || $state === 'business',
                    ]),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'active'    => 'نشط',
                        'cancelled' => 'ملغى',
                        'expired'   => 'منتهي',
                        default     => $state,
                    })
                    ->colors([
                        'success' => 'active',
                        'danger'  => 'cancelled',
                        'warning' => 'expired',
                    ]),

                Tables\Columns\TextColumn::make('payment_provider')
                    ->label('المزود')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'manual' => 'يدوي',
                        default  => ucfirst($state ?? '—'),
                    }),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('بدء')
                    ->dateTime('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('ends_at')
                    ->label('انتهاء')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->color(fn ($state) => $state && \Carbon\Carbon::parse($state)->isPast() ? 'danger' : null)
                    ->description(fn (Subscription $record): string => $record->ends_at
                        ? ($record->ends_at->isPast()
                            ? 'منتهي منذ ' . $record->ends_at->diffForHumans()
                            : 'ينتهي ' . $record->ends_at->diffForHumans())
                        : ''),

                Tables\Columns\TextColumn::make('remaining_days')
                    ->label('الأيام المتبقية')
                    ->getStateUsing(function (Subscription $record): string {
                        if (! $record->ends_at) {
                            return '∞';
                        }
                        if ($record->ends_at->isPast()) {
                            return '0';
                        }
                        return (string) (int) now()->diffInDays($record->ends_at, false);
                    })
                    ->color(function (Subscription $record): ?string {
                        if (! $record->ends_at) return null;
                        if ($record->ends_at->isPast()) return 'danger';
                        if ($record->ends_at->diffInDays(now()) <= 7) return 'warning';
                        return 'success';
                    })
                    ->badge()
                    ->suffix(fn (Subscription $record): string => $record->ends_at && ! $record->ends_at->isPast() ? ' يوم' : ''),

                Tables\Columns\TextColumn::make('provider_subscription_id')
                    ->label('Subscription ID')
                    ->copyable()
                    ->copyMessage('تم النسخ')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('plan')
                    ->label('الخطة')
                    ->options([
                        'free'     => 'مجاني',
                        'pro'      => 'Pro',
                        'business' => 'Business',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'active'    => 'نشط',
                        'cancelled' => 'ملغى',
                        'expired'   => 'منتهي',
                    ]),

                Tables\Filters\Filter::make('expiring_soon')
                    ->label('تنتهي قريباً (7 أيام)')
                    ->query(fn ($query) => $query
                        ->where('status', 'active')
                        ->whereBetween('ends_at', [now(), now()->addDays(7)])
                    ),

                Tables\Filters\Filter::make('expired_active')
                    ->label('منتهية وما زالت نشطة')
                    ->query(fn ($query) => $query
                        ->where('status', 'active')
                        ->whereNotNull('ends_at')
                        ->where('ends_at', '<', now())
                    )
                    ->indicateUsing(fn () => 'منتهية وما زالت نشطة'),
            ])
            ->actions([
                // ── تفعيل خطة ──
                Tables\Actions\Action::make('activate')
                    ->label('تفعيل خطة')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('plan')
                            ->label('الخطة الجديدة')
                            ->options([
                                SubscriptionPlan::Pro->value      => 'Pro ⚡ — 99 ر.س',
                                SubscriptionPlan::Business->value => 'Business 🚀 — 299 ر.س',
                            ])
                            ->required(),
                    ])
                    ->action(function (Subscription $record, array $data): void {
                        app(SubscriptionService::class)->activatePlan(
                            $record->user,
                            $data['plan']
                        );

                        Notification::make()
                            ->title('تم تفعيل الخطة بنجاح')
                            ->body("تم ترقية {$record->user->name} إلى خطة {$data['plan']}")
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(false)
                    ->visible(fn (Subscription $record) => $record->status !== 'active'),

                // ── تمديد شهر ──
                Tables\Actions\Action::make('extend')
                    ->label('تمديد شهر')
                    ->icon('heroicon-o-calendar-days')
                    ->color('info')
                    ->form([
                        Forms\Components\Select::make('months')
                            ->label('عدد الأشهر')
                            ->options([1 => 'شهر', 2 => 'شهرين', 3 => '3 أشهر', 6 => '6 أشهر', 12 => 'سنة'])
                            ->default(1)
                            ->required(),
                    ])
                    ->action(function (Subscription $record, array $data): void {
                        app(SubscriptionService::class)->extendPlan(
                            $record->user,
                            (int) $data['months']
                        );

                        Notification::make()
                            ->title('تم التمديد بنجاح')
                            ->body("تم تمديد اشتراك {$record->user->name} {$data['months']} شهر/أشهر")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Subscription $record) => $record->status === 'active'),

                // ── إلغاء ──
                Tables\Actions\Action::make('cancel')
                    ->label('إلغاء الاشتراك')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد إلغاء الاشتراك')
                    ->modalDescription(fn (Subscription $record): string =>
                        "ستُلغي اشتراك {$record->user->name} ({$record->user->email}) فوراً. "
                        . "سيفقد المستخدم صلاحيات خطة {$record->plan?->label()} ويُرجَع للخطة المجانية. "
                        . 'لا يمكن التراجع عن هذا الإجراء تلقائياً.'
                    )
                    ->modalSubmitActionLabel('نعم، ألغِ الاشتراك')
                    ->modalCancelActionLabel('تراجع')
                    ->action(function (Subscription $record): void {
                        app(SubscriptionService::class)->cancelPlan($record->user);

                        Notification::make()
                            ->title('تم إلغاء الاشتراك')
                            ->body("تم إلغاء اشتراك {$record->user->name} وإرجاعه للخطة المجانية.")
                            ->warning()
                            ->send();
                    })
                    ->visible(fn (Subscription $record) => $record->status === 'active'),

                // ── إعادة تفعيل ──
                Tables\Actions\Action::make('reactivate')
                    ->label('إعادة تفعيل')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('months')
                            ->label('مدة التجديد')
                            ->options([
                                1  => 'شهر واحد',
                                2  => 'شهرين',
                                3  => '3 أشهر',
                                6  => '6 أشهر',
                                12 => 'سنة كاملة',
                            ])
                            ->default(1)
                            ->required(),
                    ])
                    ->action(function (Subscription $record, array $data): void {
                        app(SubscriptionService::class)->reactivatePlan(
                            $record->user,
                            (int) $data['months'],
                        );

                        Notification::make()
                            ->title('تم إعادة التفعيل')
                            ->body("تم إعادة تفعيل اشتراك {$record->user->name} لمدة {$data['months']} شهر/أشهر.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Subscription $record) => in_array($record->status, ['cancelled', 'expired'])),

                // ── تخفيض للمجاني ──
                Tables\Actions\Action::make('downgrade')
                    ->label('تخفيض للمجاني')
                    ->icon('heroicon-o-arrow-down-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('تخفيض للخطة المجانية')
                    ->modalDescription(fn (Subscription $record): string =>
                        "سيُخفَّض {$record->user->name} ({$record->user->email}) من خطة {$record->plan?->label()} "
                        . 'إلى الخطة المجانية فوراً وتُلغى جلسته المدفوعة النشطة.'
                    )
                    ->modalSubmitActionLabel('نعم، خفِّض للمجاني')
                    ->modalCancelActionLabel('تراجع')
                    ->action(function (Subscription $record): void {
                        app(SubscriptionService::class)->downgradePlan($record->user);

                        Notification::make()
                            ->title('تم التخفيض للمجاني')
                            ->body("تم تخفيض {$record->user->name} للخطة المجانية.")
                            ->warning()
                            ->send();
                    })
                    ->visible(fn (Subscription $record) =>
                        $record->status === 'active'
                        && $record->plan !== \App\Support\Enums\SubscriptionPlan::Free
                    ),

                Tables\Actions\EditAction::make()->label('تعديل'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'edit'   => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::active()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentOrderResource\Pages;
use App\Models\PaymentOrder;
use App\Modules\Billing\Services\SubscriptionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class PaymentOrderResource extends Resource
{
    protected static ?string $model           = PaymentOrder::class;
    protected static ?string $navigationIcon  = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'المدفوعات';
    protected static ?string $navigationLabel = 'طلبات الدفع';
    protected static ?string $modelLabel      = 'طلب دفع';
    protected static ?string $pluralModelLabel = 'طلبات الدفع';
    protected static ?int    $navigationSort  = 1;

    // =====================================================
    // Form — read-only view only (no creation via admin)
    // =====================================================
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('تفاصيل الطلب')->schema([
                Forms\Components\TextInput::make('id')
                    ->label('معرف الطلب (ULID)')
                    ->disabled(),

                Forms\Components\TextInput::make('user.name')
                    ->label('المستخدم')
                    ->disabled(),

                Forms\Components\TextInput::make('plan')
                    ->label('الخطة')
                    ->disabled(),

                Forms\Components\TextInput::make('cycle')
                    ->label('دورة الفوترة')
                    ->disabled(),

                Forms\Components\TextInput::make('amount')
                    ->label('المبلغ')
                    ->disabled(),

                Forms\Components\TextInput::make('currency')
                    ->label('العملة')
                    ->disabled(),

                Forms\Components\TextInput::make('provider')
                    ->label('مزود الدفع')
                    ->disabled(),

                Forms\Components\TextInput::make('provider_order_id')
                    ->label('رقم الطلب الخارجي')
                    ->disabled(),

                Forms\Components\TextInput::make('status')
                    ->label('الحالة')
                    ->disabled(),
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
                // ULID (truncated + copyable)
                Tables\Columns\TextColumn::make('id')
                    ->label('ULID')
                    ->fontFamily('mono')
                    ->formatStateUsing(fn (string $state): string => substr($state, 0, 14) . '…')
                    ->copyable()
                    ->copyableState(fn (PaymentOrder $record): string => $record->id)
                    ->copyMessage('تم نسخ ULID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),

                // User name + email
                Tables\Columns\TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable(['users.name', 'users.email'])
                    ->sortable()
                    ->description(fn (PaymentOrder $record): string => $record->user?->email ?? ''),

                // Plan badge
                Tables\Columns\BadgeColumn::make('plan')
                    ->label('الخطة')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pro'      => 'Pro ⚡',
                        'business' => 'Business 🚀',
                        default    => ucfirst($state ?? '—'),
                    })
                    ->colors([
                        'primary' => 'pro',
                        'success' => 'business',
                    ]),

                // Billing cycle badge
                Tables\Columns\BadgeColumn::make('cycle')
                    ->label('الدورة')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'monthly' => 'شهري',
                        'annual'  => 'سنوي',
                        default   => $state ?? '—',
                    })
                    ->colors([
                        'gray'    => 'monthly',
                        'warning' => 'annual',
                    ]),

                // Amount
                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money(fn (PaymentOrder $record) => strtolower($record->currency ?? 'usd'))
                    ->sortable(),

                // Currency
                Tables\Columns\TextColumn::make('currency')
                    ->label('العملة')
                    ->badge()
                    ->color('gray'),

                // Provider
                Tables\Columns\TextColumn::make('provider')
                    ->label('المزود')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'togo'   => 'Togo.ps',
                        'manual' => 'يدوي',
                        default  => ucfirst($state ?? '—'),
                    })
                    ->color('info'),

                // External provider order ID
                Tables\Columns\TextColumn::make('provider_order_id')
                    ->label('رقم الطلب الخارجي')
                    ->fontFamily('mono')
                    ->copyable()
                    ->copyMessage('تم النسخ')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                // Status badge
                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending'   => 'قيد الانتظار',
                        'paid'      => 'مدفوع',
                        'failed'    => 'فشل',
                        'cancelled' => 'ملغى',
                        default     => $state,
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger'  => 'failed',
                        'gray'    => 'cancelled',
                    ]),

                // Created At
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                // Paid At
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('تاريخ الدفع')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('—')
                    ->color('success'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // Status filter
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending'   => 'قيد الانتظار',
                        'paid'      => 'مدفوع',
                        'failed'    => 'فشل',
                        'cancelled' => 'ملغى',
                    ]),

                // Plan filter
                Tables\Filters\SelectFilter::make('plan')
                    ->label('الخطة')
                    ->options([
                        'pro'      => 'Pro ⚡',
                        'business' => 'Business 🚀',
                    ]),

                // Cycle filter
                Tables\Filters\SelectFilter::make('cycle')
                    ->label('دورة الفوترة')
                    ->options([
                        'monthly' => 'شهري',
                        'annual'  => 'سنوي',
                    ]),

                // Provider filter
                Tables\Filters\SelectFilter::make('provider')
                    ->label('مزود الدفع')
                    ->options([
                        'togo'   => 'Togo.ps',
                        'manual' => 'يدوي',
                    ]),

                // Date range — created_at
                Tables\Filters\Filter::make('created_at_range')
                    ->label('نطاق تاريخ الإنشاء')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('من'),
                        Forms\Components\DatePicker::make('until')->label('حتى'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'],  fn ($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('created_at', '<=', $data['until']));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'])  $indicators[] = 'من: ' . $data['from'];
                        if ($data['until']) $indicators[] = 'حتى: ' . $data['until'];
                        return $indicators;
                    }),

                // Paid / Unpaid quick filter
                Tables\Filters\Filter::make('paid_only')
                    ->label('المدفوعة فقط')
                    ->query(fn (Builder $query) => $query->where('status', 'paid')),

                Tables\Filters\Filter::make('unpaid_only')
                    ->label('غير المدفوعة')
                    ->query(fn (Builder $query) => $query->whereIn('status', ['pending', 'failed', 'cancelled'])),
            ])
            ->actions([
                // ── Payment Timeline ──
                Tables\Actions\Action::make('view_timeline')
                    ->label('Timeline')
                    ->icon('heroicon-o-clock')
                    ->color('primary')
                    ->modalHeading(fn (PaymentOrder $record) => 'مسار الدفع: ' . substr($record->id, 0, 14) . '…')
                    ->modalContent(fn (PaymentOrder $record) => view(
                        'filament.modals.payment-order-timeline',
                        ['order' => $record]
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('إغلاق')
                    ->modalWidth('2xl'),

                // ── View Details (read-only modal) ──
                Tables\Actions\Action::make('view_details')
                    ->label('التفاصيل')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn (PaymentOrder $record) => 'تفاصيل الطلب: ' . substr($record->id, 0, 14) . '…')
                    ->modalContent(fn (PaymentOrder $record) => view(
                        'filament.modals.payment-order-details',
                        ['order' => $record]
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('إغلاق'),

                // ── Open Checkout URL ──
                Tables\Actions\Action::make('open_checkout')
                    ->label('رابط الدفع')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('info')
                    ->url(fn (PaymentOrder $record): ?string => $record->metadata['checkout_url'] ?? null)
                    ->openUrlInNewTab()
                    ->visible(fn (PaymentOrder $record): bool =>
                        ! empty($record->metadata['checkout_url'])
                    ),

                // ── View Metadata JSON ──
                Tables\Actions\Action::make('view_metadata')
                    ->label('Metadata')
                    ->icon('heroicon-o-code-bracket')
                    ->color('gray')
                    ->modalHeading('Metadata JSON')
                    ->modalContent(fn (PaymentOrder $record) => view(
                        'filament.modals.payment-order-metadata',
                        ['metadata' => $record->metadata ?? []]
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('إغلاق'),

                // ── Mark as Paid ──
                Tables\Actions\Action::make('mark_paid')
                    ->label('تأكيد الدفع')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد الدفع يدوياً')
                    ->modalDescription(fn (PaymentOrder $record): string =>
                        "ستُعلِّم الطلب [{$record->id}] كـ «مدفوع» وتُفعِّل اشتراك المستخدم {$record->user?->name}."
                        . ' هذا الإجراء يُنشئ اشتراكاً نشطاً للمستخدم.'
                    )
                    ->modalSubmitActionLabel('نعم، أكِّد الدفع')
                    ->modalCancelActionLabel('تراجع')
                    ->action(function (PaymentOrder $record): void {
                        if ($record->isPaid()) {
                            Notification::make()->warning()->title('الطلب مدفوع بالفعل')->send();
                            return;
                        }

                        $record->markAsPaid();

                        app(SubscriptionService::class)->activatePlan(
                            user: $record->user,
                            planValue: $record->plan,
                            providerSubscriptionId: $record->provider_order_id,
                            cycle: $record->cycle ?? 'monthly',
                        );

                        Log::info('Admin manually marked PaymentOrder as paid', [
                            'order_id' => $record->id,
                            'admin'    => auth()->id(),
                        ]);

                        Notification::make()
                            ->success()
                            ->title('تم تأكيد الدفع')
                            ->body("تم تفعيل اشتراك {$record->user?->name} خطة {$record->plan}.")
                            ->send();
                    })
                    ->visible(fn (PaymentOrder $record): bool =>
                        in_array($record->status, ['pending', 'failed'])
                    ),

                // ── Mark as Failed ──
                Tables\Actions\Action::make('mark_failed')
                    ->label('تأكيد الفشل')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('تعليم الطلب كفاشل')
                    ->modalDescription('سيُعلَّم الطلب كـ «فشل». لن يُفعَّل أي اشتراك.')
                    ->modalSubmitActionLabel('نعم، فشل')
                    ->modalCancelActionLabel('تراجع')
                    ->action(function (PaymentOrder $record): void {
                        $record->markAsFailed();

                        Log::warning('Admin manually marked PaymentOrder as failed', [
                            'order_id' => $record->id,
                            'admin'    => auth()->id(),
                        ]);

                        Notification::make()
                            ->warning()
                            ->title('تم تعليم الطلب كفاشل')
                            ->send();
                    })
                    ->visible(fn (PaymentOrder $record): bool =>
                        $record->status === 'pending'
                    ),

                // ── Cancel Order ──
                Tables\Actions\Action::make('cancel_order')
                    ->label('إلغاء الطلب')
                    ->icon('heroicon-o-no-symbol')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('إلغاء طلب الدفع')
                    ->modalDescription('سيُعلَّم الطلب كـ «ملغى». لا يمكن التراجع.')
                    ->modalSubmitActionLabel('نعم، ألغِ الطلب')
                    ->modalCancelActionLabel('تراجع')
                    ->action(function (PaymentOrder $record): void {
                        $record->markAsCancelled();

                        Log::info('Admin cancelled PaymentOrder', [
                            'order_id' => $record->id,
                            'admin'    => auth()->id(),
                        ]);

                        Notification::make()
                            ->info()
                            ->title('تم إلغاء الطلب')
                            ->send();
                    })
                    ->visible(fn (PaymentOrder $record): bool =>
                        $record->status === 'pending'
                    ),

                // ── Retry Payment (redirect user back to pending page) ──
                Tables\Actions\Action::make('retry_payment')
                    ->label('إعادة المحاولة')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('إعادة تفعيل رابط الدفع')
                    ->modalDescription(fn (PaymentOrder $record): string =>
                        'سيُعيَّن الطلب إلى «قيد الانتظار» وستُرسَل رابط الدفع لـ ' . ($record->user?->name ?? 'المستخدم') . ' عبر إشعار داخلي.'
                    )
                    ->modalSubmitActionLabel('نعم، أعِد المحاولة')
                    ->modalCancelActionLabel('تراجع')
                    ->action(function (PaymentOrder $record): void {
                        // أعِد الطلب إلى pending
                        $record->update([
                            'status'    => 'pending',
                            'failed_at' => null,
                        ]);

                        // أرسل إشعاراً داخلياً للمستخدم برابط الدفع
                        if ($record->user && ($checkoutUrl = $record->metadata['checkout_url'] ?? null)) {
                            $record->user->notify(
                                new \App\Notifications\PaymentRetryNotification($record, $checkoutUrl)
                            );
                        }

                        Log::info('Admin retried PaymentOrder', [
                            'order_id' => $record->id,
                            'admin'    => auth()->id(),
                        ]);

                        Notification::make()
                            ->success()
                            ->title('تم إعادة تفعيل الطلب')
                            ->body('الطلب الآن في حالة «قيد الانتظار» وأُرسل إشعار للمستخدم.')
                            ->send();
                    })
                    ->visible(fn (PaymentOrder $record): bool =>
                        in_array($record->status, ['failed', 'cancelled'])
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد')
                        ->requiresConfirmation(),
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
            'index' => Pages\ListPaymentOrders::route('/'),
        ];
    }

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
        return false; // لا يُنشأ طلب دفع من Admin — يُنشأ فقط عبر checkout flow
    }
}

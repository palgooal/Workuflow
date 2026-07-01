<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentCollectionResource\Pages;
use App\Models\PaymentCollection;
use App\Support\Enums\PaymentCollectionStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;

/**
 * PaymentCollectionResource — مراجعة تحصيلات الفواتير عبر بوابة الدفع
 * (التحصيل عبر دراهم نيابة عن المشتركين) وتسويتها يدوياً معهم.
 *
 * لا يوجد إنشاء/تعديل يدوي — السجلات تُنشأ فقط عبر InvoicePaymentController.
 * راجع docs/PAYMENT-COLLECTION.md للتوثيق الكامل للميزة.
 */
class PaymentCollectionResource extends Resource
{
    protected static ?string $model            = PaymentCollection::class;
    protected static ?string $navigationIcon   = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup  = 'المدفوعات';
    protected static ?string $navigationLabel  = 'تحصيلات الفواتير';
    protected static ?string $modelLabel       = 'تحصيل';
    protected static ?string $pluralModelLabel = 'تحصيلات الفواتير';
    protected static ?int    $navigationSort   = 2;

    // =====================================================
    // Form — عرض للقراءة فقط (لا إنشاء/تعديل يدوي من الأدمن)
    // =====================================================
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('تفاصيل التحصيل')->schema([
                Forms\Components\TextInput::make('invoice.number')
                    ->label('رقم الفاتورة')
                    ->disabled(),

                Forms\Components\TextInput::make('user.name')
                    ->label('المشترك')
                    ->disabled(),

                Forms\Components\TextInput::make('client.name')
                    ->label('العميل الدافع')
                    ->disabled(),

                Forms\Components\TextInput::make('provider')
                    ->label('مزود الدفع')
                    ->disabled(),

                Forms\Components\TextInput::make('amount')
                    ->label('مبلغ الفاتورة')
                    ->disabled(),

                Forms\Components\TextInput::make('currency')
                    ->label('عملة الفاتورة')
                    ->disabled(),

                Forms\Components\TextInput::make('settlement_amount')
                    ->label('مبلغ التسوية (ILS)')
                    ->disabled(),

                Forms\Components\TextInput::make('settlement_platform_fee')
                    ->label('عمولة التسوية (ILS)')
                    ->disabled(),

                Forms\Components\TextInput::make('settlement_net_amount')
                    ->label('صافي التسوية (ILS)')
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
            ->modifyQueryUsing(fn ($query) => $query->with(['invoice', 'user', 'client'])->latest())
            ->columns([
                Tables\Columns\TextColumn::make('invoice.number')
                    ->label('رقم الفاتورة')
                    ->searchable()
                    ->sortable()
                    ->url(fn (PaymentCollection $record): ?string =>
                        $record->invoice ? route('invoices.show', $record->invoice->ulid) : null
                    )
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('المشترك')
                    ->searchable(['users.name', 'users.email'])
                    ->sortable()
                    ->description(fn (PaymentCollection $record): string => $record->user?->email ?? ''),

                Tables\Columns\TextColumn::make('client.name')
                    ->label('العميل الدافع')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('provider')
                    ->label('المزود')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'togo'  => 'Togo.ps',
                        default => ucfirst($state ?? '—'),
                    })
                    ->color('info'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('مبلغ الفاتورة')
                    ->money(fn (PaymentCollection $record) => strtolower($record->currency ?? 'ils'))
                    ->description(fn (PaymentCollection $record) => $record->currency)
                    ->sortable(),

                Tables\Columns\TextColumn::make('currency')
                    ->label('عملة الفاتورة')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                // ── التسوية بالشيكل (settlement_*) — القيم الفعلية التي ستُحوَّل ──
                // للمشترك؛ منفصلة تماماً عن عملة الفاتورة أعلاه لأن Togo تُسوِّي
                // دائماً بالشيكل. راجع docs/PAYMENT-COLLECTION.md.
                Tables\Columns\TextColumn::make('settlement_amount')
                    ->label('مبلغ التسوية')
                    ->money(fn (PaymentCollection $record) => strtolower($record->settlement_currency ?? 'ils'))
                    ->placeholder('غير معروف بعد')
                    ->sortable(),

                Tables\Columns\TextColumn::make('settlement_currency')
                    ->label('عملة التسوية')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('settlement_platform_fee')
                    ->label('عمولة التسوية')
                    ->money(fn (PaymentCollection $record) => strtolower($record->settlement_currency ?? 'ils'))
                    ->toggleable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('settlement_net_amount')
                    ->label('صافي التسوية')
                    ->money(fn (PaymentCollection $record) => strtolower($record->settlement_currency ?? 'ils'))
                    ->weight('bold')
                    ->placeholder('⚠️ بانتظار تحديد مبلغ التسوية')
                    ->color(fn (PaymentCollection $record) => $record->settlement_net_amount === null ? 'warning' : 'success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (PaymentCollectionStatus $state): string => $state->label())
                    ->color(fn (PaymentCollectionStatus $state): string => match ($state) {
                        PaymentCollectionStatus::Pending   => 'warning',
                        PaymentCollectionStatus::Collected => 'success',
                        PaymentCollectionStatus::Settled   => 'info',
                        PaymentCollectionStatus::Failed    => 'danger',
                        PaymentCollectionStatus::Refunded  => 'gray',
                    }),

                Tables\Columns\TextColumn::make('collected_at')
                    ->label('تاريخ التحصيل')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('settled_at')
                    ->label('تاريخ التسوية')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('—')
                    ->color('success'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(collect(PaymentCollectionStatus::cases())
                        ->mapWithKeys(fn (PaymentCollectionStatus $case) => [$case->value => $case->label()])
                        ->all()),
            ])
            ->actions([
                // ── تحديد مبلغ التسوية يدوياً ── تظهر فقط عندما التحصيل تم
                // (collected) لكن مبلغ التسوية بالشيكل غير معروف بعد (فاتورة
                // بعملة أجنبية ولم تُرجِع Togo مبلغاً/سعر صرف). خطوة لازمة قبل
                // السماح بزر "تسوية مع المشترك" — راجع docs/PAYMENT-COLLECTION.md.
                Tables\Actions\Action::make('confirm_settlement_amount')
                    ->label('تحديد مبلغ التسوية يدوياً')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('settlement_amount')
                            ->label('مبلغ التسوية بالشيكل (ILS)')
                            ->helperText('المبلغ الفعلي الذي حوَّلته/سيُحوِّله Togo — تحقق منه في لوحة Togo قبل الإدخال.')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->required(),
                        Forms\Components\TextInput::make('exchange_rate')
                            ->label('سعر الصرف المُستخدَم (اختياري)')
                            ->helperText(fn (PaymentCollection $record): string =>
                                'مثال: 3.65 يعني 1 ' . $record->currency . ' = 3.65 شيكل — للتوثيق فقط.'
                            )
                            ->numeric()
                            ->minValue(0)
                            ->step(0.000001),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('تحديد مبلغ التسوية يدوياً')
                    ->modalDescription(fn (PaymentCollection $record): string =>
                        'هذه الفاتورة بعملة ' . $record->currency . ' (مبلغها ' . number_format((float) $record->amount, 2) . ' ' . $record->currency . '). '
                        . 'أدخل المبلغ الذي حصَّلته/ستُسوّيه Togo فعلياً بالشيكل بعد التحقق من لوحة Togo. سيُحسَب صافي التسوية تلقائياً بعد خصم عمولة تحصيل الفواتير الحالية.'
                    )
                    ->modalSubmitActionLabel('حفظ المبلغ')
                    ->action(function (PaymentCollection $record, array $data): void {
                        $settlementAmount = round((float) $data['settlement_amount'], 2);
                        $exchangeRate     = isset($data['exchange_rate']) && $data['exchange_rate'] !== null
                            ? round((float) $data['exchange_rate'], 6)
                            : null;

                        $feeEnabled = filter_var(\App\Models\Setting::get('invoice_collection_fee_enabled', true), FILTER_VALIDATE_BOOLEAN);
                        $feeRate    = null;
                        $fixedFee   = null;

                        if (! $feeEnabled) {
                            $platformFee = 0.0;
                        } else {
                            $feeRate     = (float) \App\Models\Setting::get('invoice_collection_fee_rate', 2.5);
                            $fixedFee    = (float) \App\Models\Setting::get('invoice_collection_fixed_fee', 0);
                            $platformFee = ($settlementAmount * $feeRate / 100) + $fixedFee;
                        }

                        $platformFee = max(0, round($platformFee, 2));
                        $netAmount   = max(0, round($settlementAmount - $platformFee, 2));

                        $record->update([
                            'settlement_amount'       => $settlementAmount,
                            'settlement_platform_fee' => $platformFee,
                            'settlement_net_amount'   => $netAmount,
                            'exchange_rate'           => $exchangeRate,
                            'metadata'                => array_merge($record->metadata ?? [], [
                                'settlement_source'    => 'admin_manual',
                                'settlement_fee_rate'   => $feeRate,
                                'settlement_fixed_fee'  => $fixedFee,
                                'settlement_confirmed_by' => auth()->id(),
                                'settlement_confirmed_at' => now()->toIso8601String(),
                            ]),
                        ]);

                        Log::info('Admin manually confirmed settlement amount for PaymentCollection', [
                            'payment_collection_id'  => $record->id,
                            'invoice_id'              => $record->invoice_id,
                            'settlement_amount'       => $settlementAmount,
                            'settlement_net_amount'   => $netAmount,
                            'admin'                   => auth()->id(),
                        ]);

                        Notification::make()
                            ->success()
                            ->title('تم حفظ مبلغ التسوية')
                            ->body('يمكنك الآن تسوية هذا التحصيل مع ' . ($record->user?->name ?? 'المشترك') . '.')
                            ->send();
                    })
                    ->visible(fn (PaymentCollection $record): bool =>
                        $record->status === PaymentCollectionStatus::Collected && ! $record->isSettlementAmountKnown()
                    ),

                // ── تسوية مع المشترك ── تظهر فقط إذا status = collected
                // ومبلغ التسوية بالشيكل معروف (settlement_net_amount ليس null).
                Tables\Actions\Action::make('settle')
                    ->label('تسوية مع المشترك')
                    ->icon('heroicon-o-banknotes')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('تسوية التحصيل مع المشترك')
                    ->modalDescription(function (PaymentCollection $record): string {
                        $netAmount   = number_format((float) $record->settlement_net_amount, 2) . ' ' . $record->settlement_currency;
                        $platformFee = number_format((float) $record->settlement_platform_fee, 2) . ' ' . $record->settlement_currency;

                        $description = "سيتم تحويل صافي المبلغ {$netAmount} بعد خصم عمولة بوابة الدفع {$platformFee}"
                            . ' يدوياً إلى ' . ($record->user?->name ?? 'المشترك') . ' خارج النظام.';

                        if ($record->currency !== $record->settlement_currency) {
                            $description .= ' (فاتورة العميل بعملة ' . $record->currency . '، لكن التسوية الفعلية عبر بوابة الدفع دائماً بالشيكل.)';
                        }

                        // إن احتوت metadata تفاصيل احتساب العمولة (نسبة/ثابتة) نعرضها للشفافية
                        $feeRate  = $record->metadata['settlement_fee_rate']  ?? null;
                        $fixedFee = $record->metadata['settlement_fixed_fee'] ?? null;

                        if ($feeRate !== null || $fixedFee !== null) {
                            $breakdown = [];
                            if ($feeRate !== null) {
                                $breakdown[] = 'نسبة ' . number_format((float) $feeRate, 2) . '%';
                            }
                            if ($fixedFee !== null && (float) $fixedFee > 0) {
                                $breakdown[] = 'عمولة ثابتة ' . number_format((float) $fixedFee, 2) . ' ' . $record->settlement_currency;
                            }
                            if (! empty($breakdown)) {
                                $description .= ' (' . implode(' + ', $breakdown) . ')';
                            }
                        } elseif (($record->metadata['settlement_source'] ?? null) === 'togo_response') {
                            $description .= ' (مبلغ تسوية فعلي من بوابة الدفع)';
                        } elseif (($record->metadata['settlement_source'] ?? null) === 'admin_manual') {
                            $description .= ' (مبلغ تسوية أُدخل يدوياً من الأدمن)';
                        }

                        return $description
                            . ' لن يُعدَّل هذا الإجراء الفاتورة ولن يُنشئ أي معاملة جديدة، ولن يُغيِّر settlement_amount أو settlement_platform_fee أو settlement_net_amount — فقط يُسجِّل أن التسوية تمت.';
                    })
                    ->modalSubmitActionLabel('نعم، تمت التسوية')
                    ->modalCancelActionLabel('تراجع')
                    ->action(function (PaymentCollection $record): void {
                        // تحديث الحالة فقط — لا نلمس invoice ولا ننشئ Transaction
                        $record->update([
                            'status'     => PaymentCollectionStatus::Settled,
                            'settled_at' => now(),
                        ]);

                        Log::info('Admin settled PaymentCollection with subscriber', [
                            'payment_collection_id'  => $record->id,
                            'invoice_id'              => $record->invoice_id,
                            'user_id'                 => $record->user_id,
                            'settlement_net_amount'   => $record->settlement_net_amount,
                            'settlement_currency'     => $record->settlement_currency,
                            'admin'                   => auth()->id(),
                        ]);

                        Notification::make()
                            ->success()
                            ->title('تمت التسوية')
                            ->body('تم تعليم التحصيل كمُسوّى مع ' . ($record->user?->name ?? 'المشترك') . '.')
                            ->send();
                    })
                    ->visible(fn (PaymentCollection $record): bool => $record->isReadyForSettlement())
                    // ⚠️ إن كان هذا التحصيل مرتبطاً بطلب تسوية مفتوح (pending/approved)
                    // نُعطِّل الزر بدلاً من إخفائه، مع توضيح السبب — لمنع تسوية التحصيل
                    // من هنا مباشرة بطريقة تُفلِت من مسار SettlementRequest وتُبقيه عالقاً
                    // على حالة قديمة رغم أن تحصيلاته أصبحت settled. استخدم "تعليم كمدفوع"
                    // من طلبات التسوية بدلاً من ذلك.
                    ->disabled(fn (PaymentCollection $record): bool => $record->hasOpenSettlementRequest())
                    ->tooltip(fn (PaymentCollection $record): ?string => $record->hasOpenSettlementRequest()
                        ? 'هذا التحصيل مرتبط بطلب تسوية قيد المراجعة أو معتمد — أكمل التسوية عبر "تعليم كمدفوع" في طلبات التسوية بدلاً من هذا الزر.'
                        : null
                    ),
            ]);
            // لا bulkActions هنا عمداً — لا حذف جماعي، راجع canDelete()/canDeleteAny() أدناه
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentCollections::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $awaitingSettlement = static::getModel()::where('status', PaymentCollectionStatus::Collected)->count();
        return $awaitingSettlement > 0 ? (string) $awaitingSettlement : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    // ── لا إنشاء/تعديل/حذف من الواجهة — التحصيلات تُدار عبر الدفع الفعلي فقط ──
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }
    public static function canDeleteAny(): bool { return false; }
}

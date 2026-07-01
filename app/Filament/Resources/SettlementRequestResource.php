<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettlementRequestResource\Pages;
use App\Filament\Resources\SettlementRequestResource\RelationManagers;
use App\Models\PaymentCollection;
use App\Models\SettlementRequest;
use App\Support\Enums\PaymentCollectionStatus;
use App\Support\Enums\SettlementRequestStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SettlementRequestResource — مراجعة طلبات التسوية التي يُنشئها المشتركون من
 * صفحة "تحصيلاتي" (/collections). المشترك يطلب فقط — لا تحويل مال تلقائي.
 *
 * الأدمن يعتمد/يرفض الطلب، ثم عند التحويل الفعلي خارج النظام يُعلِّمه "مدفوع"
 * — عندها فقط تتحول كل PaymentCollection المرتبطة إلى status=settled.
 * راجع docs/PAYMENT-COLLECTION.md قسم "طلبات التسوية".
 *
 * لا يوجد إنشاء/تعديل يدوي — السجلات تُنشأ فقط عبر SettlementRequestController.
 */
class SettlementRequestResource extends Resource
{
    protected static ?string $model            = SettlementRequest::class;
    protected static ?string $navigationIcon   = 'heroicon-o-document-currency-dollar';
    protected static ?string $navigationGroup  = 'المدفوعات';
    protected static ?string $navigationLabel  = 'طلبات التسوية';
    protected static ?string $modelLabel       = 'طلب تسوية';
    protected static ?string $pluralModelLabel = 'طلبات التسوية';
    protected static ?int    $navigationSort   = 3;

    // =====================================================
    // Form — عرض للقراءة فقط (لا إنشاء/تعديل يدوي من الأدمن)
    // =====================================================
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('تفاصيل الطلب')->schema([
                Forms\Components\TextInput::make('user.name')
                    ->label('المشترك')
                    ->disabled(),

                Forms\Components\TextInput::make('total_amount')
                    ->label('المبلغ الإجمالي')
                    ->disabled(),

                Forms\Components\TextInput::make('currency')
                    ->label('العملة')
                    ->disabled(),

                Forms\Components\TextInput::make('status')
                    ->label('الحالة')
                    ->disabled(),

                Forms\Components\Textarea::make('admin_notes')
                    ->label('ملاحظات الأدمن')
                    ->disabled()
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    // =====================================================
    // Table
    // =====================================================
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('user')->withCount('paymentCollections')->latest('requested_at'))
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('الرقم')
                    ->formatStateUsing(fn (string $state): string => '#' . $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('المشترك')
                    ->searchable(['users.name', 'users.email'])
                    ->sortable()
                    ->description(fn (SettlementRequest $record): string => $record->user?->email ?? ''),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('المبلغ الإجمالي')
                    ->money(fn (SettlementRequest $record) => strtolower($record->currency ?? 'ils'))
                    ->weight('bold')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_collections_count')
                    ->label('عدد التحصيلات')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (SettlementRequestStatus $state): string => $state->label())
                    ->color(fn (SettlementRequestStatus $state): string => match ($state) {
                        SettlementRequestStatus::Pending  => 'warning',
                        SettlementRequestStatus::Approved => 'info',
                        SettlementRequestStatus::Rejected => 'danger',
                        SettlementRequestStatus::Paid      => 'success',
                    }),

                Tables\Columns\TextColumn::make('requested_at')
                    ->label('تاريخ الطلب')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('reviewed_at')
                    ->label('تاريخ المراجعة')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('تاريخ الدفع')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('—')
                    ->color('success'),

                Tables\Columns\TextColumn::make('admin_notes')
                    ->label('ملاحظات')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('requested_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(collect(SettlementRequestStatus::cases())
                        ->mapWithKeys(fn (SettlementRequestStatus $case) => [$case->value => $case->label()])
                        ->all()),
            ])
            ->actions([
                // ── اعتماد الطلب ── تظهر فقط إذا status = pending
                Tables\Actions\Action::make('approve')
                    ->label('اعتماد الطلب')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('اعتماد طلب التسوية')
                    ->modalDescription(fn (SettlementRequest $record): string =>
                        'سيُعتمَد طلب ' . ($record->user?->name ?? 'المشترك') . ' بمبلغ '
                        . number_format((float) $record->total_amount, 2) . ' ' . $record->currency
                        . '. هذا لا يُحوِّل أي مال بعد — فقط يُسجِّل الموافقة، ثم يظهر زر "تعليم كمدفوع" بعد إتمام التحويل الفعلي خارج النظام.'
                    )
                    ->modalSubmitActionLabel('اعتماد')
                    ->action(function (SettlementRequest $record): void {
                        $record->update([
                            'status'      => SettlementRequestStatus::Approved,
                            'reviewed_at' => now(),
                        ]);

                        Log::info('Admin approved SettlementRequest', [
                            'settlement_request_id' => $record->id,
                            'user_id'                => $record->user_id,
                            'total_amount'           => $record->total_amount,
                            'admin'                  => auth()->id(),
                        ]);

                        Notification::make()->success()
                            ->title('تم اعتماد الطلب')
                            ->send();
                    })
                    ->visible(fn (SettlementRequest $record): bool => $record->status === SettlementRequestStatus::Pending),

                // ── رفض الطلب ── تظهر فقط إذا status = pending، admin_notes إلزامي
                Tables\Actions\Action::make('reject')
                    ->label('رفض الطلب')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('سبب الرفض')
                            ->required()
                            ->rows(3),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('رفض طلب التسوية')
                    ->modalDescription('سيُرفض الطلب ويبقى بإمكان المشترك تقديم طلب جديد لاحقاً — التحصيلات المرتبطة تبقى قابلة للإدراج في طلب لاحق.')
                    ->modalSubmitActionLabel('رفض الطلب')
                    ->action(function (SettlementRequest $record, array $data): void {
                        $record->update([
                            'status'      => SettlementRequestStatus::Rejected,
                            'reviewed_at' => now(),
                            'admin_notes' => $data['admin_notes'],
                        ]);

                        Log::warning('Admin rejected SettlementRequest', [
                            'settlement_request_id' => $record->id,
                            'user_id'                => $record->user_id,
                            'admin'                  => auth()->id(),
                            'reason'                 => $data['admin_notes'],
                        ]);

                        Notification::make()->warning()
                            ->title('تم رفض الطلب')
                            ->send();
                    })
                    ->visible(fn (SettlementRequest $record): bool => $record->status === SettlementRequestStatus::Pending),

                // ── تعليم كمدفوع ── تظهر فقط إذا status = approved
                Tables\Actions\Action::make('mark_paid')
                    ->label('تعليم كمدفوع')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('تعليم طلب التسوية كمدفوع')
                    ->modalDescription(fn (SettlementRequest $record): string =>
                        'يُستخدَم فقط بعد تحويل ' . number_format((float) $record->total_amount, 2) . ' ' . $record->currency
                        . ' فعلياً إلى ' . ($record->user?->name ?? 'المشترك') . ' خارج النظام. سيتحوّل الطلب وكل التحصيلات المرتبطة به (' . $record->paymentCollections()->count() . ') إلى "تمت التسويتها" معاً. لن يُنشئ أي Transaction ولن يُعدِّل أي Invoice.'
                    )
                    ->modalSubmitActionLabel('نعم، تم الدفع')
                    ->action(function (SettlementRequest $record): void {
                        DB::transaction(function () use ($record): void {
                            $record->update([
                                'status'  => SettlementRequestStatus::Paid,
                                'paid_at' => now(),
                            ]);

                            // فقط التحصيلات التي لا تزال collected فعلياً — حماية إضافية
                            // في حال تغيّرت حالة أحدها بطريقة ما بين الاعتماد والتعليم كمدفوع.
                            $record->paymentCollections()
                                ->where('status', PaymentCollectionStatus::Collected)
                                ->get()
                                ->each(function (PaymentCollection $collection): void {
                                    $collection->update([
                                        'status'     => PaymentCollectionStatus::Settled,
                                        'settled_at' => now(),
                                    ]);
                                });
                        });

                        // ⚠️ إعادة تحميل السجل وعلاقته بعد المعاملة مباشرة — يضمن أن أي
                        // استخدام لاحق لـ $record ضمن نفس الطلب (مثل modalDescription أو
                        // Notification) يعكس الحالة الجديدة فوراً وليس نسخة محفوظة في الذاكرة
                        // قبل التحديث. جدول Filament نفسه يُعيد الاستعلام من القاعدة تلقائياً
                        // عند كل تحديث للصفحة، لكن هذا يحمي من أي استخدام إضافي لـ $record.
                        $record->refresh();
                        $record->load('paymentCollections');

                        Log::info('Admin marked SettlementRequest as paid', [
                            'settlement_request_id' => $record->id,
                            'user_id'                => $record->user_id,
                            'total_amount'           => $record->total_amount,
                            'admin'                  => auth()->id(),
                        ]);

                        Notification::make()->success()
                            ->title('تم تعليم الطلب كمدفوع')
                            ->body('تحوَّلت كل التحصيلات المرتبطة إلى "تمت تسويتها".')
                            ->send();
                    })
                    ->visible(fn (SettlementRequest $record): bool => $record->status === SettlementRequestStatus::Approved),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaymentCollectionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettlementRequests::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::where('status', SettlementRequestStatus::Pending)->count();
        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    // ── لا إنشاء/تعديل/حذف من الواجهة — الطلبات تُنشأ فقط عبر SettlementRequestController ──
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }
    public static function canDeleteAny(): bool { return false; }
}

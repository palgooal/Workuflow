<?php

namespace App\Filament\Resources\SettlementRequestResource\RelationManagers;

use App\Models\PaymentCollection;
use App\Support\Enums\PaymentCollectionStatus;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * يعرض للأدمن — قبل اعتماد/رفض طلب التسوية — أي تحصيلات (PaymentCollection)
 * بالضبط شكّلت total_amount لهذا الطلب. للقراءة فقط بالكامل: لا إضافة/إزالة/
 * تعديل من هنا — الربط يتم فقط تلقائياً عند إنشاء الطلب (SettlementRequestController).
 */
class PaymentCollectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'paymentCollections';

    protected static ?string $title = 'التحصيلات المرتبطة';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->modifyQueryUsing(fn ($query) => $query->with(['invoice', 'client']))
            ->columns([
                Tables\Columns\TextColumn::make('invoice.number')
                    ->label('رقم الفاتورة'),

                Tables\Columns\TextColumn::make('client.name')
                    ->label('العميل الدافع'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('مبلغ الفاتورة')
                    ->money(fn (PaymentCollection $record) => strtolower($record->currency ?? 'ils')),

                Tables\Columns\TextColumn::make('settlement_net_amount')
                    ->label('صافي التسوية')
                    ->money(fn (PaymentCollection $record) => strtolower($record->settlement_currency ?? 'ils'))
                    ->weight('bold'),

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
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }

    // ── للقراءة فقط بالكامل — لا إنشاء/إرفاق/فصل/تعديل/حذف من هذه الشاشة ──
    protected function canCreate(): bool { return false; }
    protected function canAttach(): bool { return false; }
}

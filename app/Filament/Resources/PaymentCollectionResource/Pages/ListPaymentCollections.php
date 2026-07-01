<?php

namespace App\Filament\Resources\PaymentCollectionResource\Pages;

use App\Filament\Resources\PaymentCollectionResource;
use App\Filament\Resources\PaymentCollectionResource\Widgets\AwaitingSettlementAmountWidget;
use App\Models\PaymentCollection;
use App\Support\Enums\PaymentCollectionStatus;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPaymentCollections extends ListRecords
{
    protected static string $resource = PaymentCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // لا يوجد زر "إنشاء" — التحصيلات تُنشأ فقط عبر InvoicePaymentController
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('الكل'),

            'collected' => Tab::make('محصَّلة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', PaymentCollectionStatus::Collected))
                ->badge(PaymentCollection::where('status', PaymentCollectionStatus::Collected)->count()),

            'settled' => Tab::make('تمت تسويتها')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', PaymentCollectionStatus::Settled))
                ->badge(PaymentCollection::where('status', PaymentCollectionStatus::Settled)->count()),

            'failed' => Tab::make('فاشلة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', PaymentCollectionStatus::Failed))
                ->badge(PaymentCollection::where('status', PaymentCollectionStatus::Failed)->count()),

            'refunded' => Tab::make('مستردة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', PaymentCollectionStatus::Refunded))
                ->badge(PaymentCollection::where('status', PaymentCollectionStatus::Refunded)->count()),

            // ── بانتظار تحديد مبلغ التسوية — collected لكن settlement_amount = NULL ──
            // (فاتورة بعملة أجنبية ولم تُرجِع Togo مبلغاً/سعر صرف). راجع
            // docs/PAYMENT-COLLECTION.md قسم "عملة الفاتورة مقابل عملة التسوية".
            'awaiting_settlement' => Tab::make('بانتظار تحديد مبلغ التسوية')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('status', PaymentCollectionStatus::Collected)
                    ->whereNull('settlement_amount'))
                ->badge(static::awaitingSettlementAmountCount())
                ->badgeColor('warning'),
        ];
    }

    /**
     * عدد التحصيلات المحصَّلة فعلياً لكن مبلغ تسويتها بالشيكل غير معروف بعد —
     * تُستخدم هنا وفي AwaitingSettlementAmountWidget (نفس الشرط بالضبط).
     */
    public static function awaitingSettlementAmountCount(): int
    {
        return PaymentCollection::where('status', PaymentCollectionStatus::Collected)
            ->whereNull('settlement_amount')
            ->count();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AwaitingSettlementAmountWidget::class,
        ];
    }
}

<?php

namespace App\Filament\Resources\PaymentCollectionResource\Widgets;

use App\Filament\Resources\PaymentCollectionResource;
use App\Models\PaymentCollection;
use App\Support\Enums\PaymentCollectionStatus;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * AwaitingSettlementAmountWidget — تنبيه للأدمن داخل PaymentCollectionResource
 * بعدد التحصيلات (status=collected) التي لم يُحدَّد لها بعد مبلغ تسوية بالشيكل
 * (فواتير بعملة أجنبية لم تُرجِع Togo مبلغاً/سعر صرف لها). النقر على البطاقة
 * يفتح /admin/payment-collections على تبويب "بانتظار تحديد مبلغ التسوية".
 *
 * ⚠️ عمداً داخل مجلد Resources/PaymentCollectionResource/Widgets وليس داخل
 * app/Filament/Widgets — الأخير يخضع لـ discoverWidgets() في
 * AdminPanelProvider ويظهر تلقائياً على لوحة التحكم الرئيسية، بينما هذا
 * الـ widget يجب أن يظهر فقط داخل صفحة قائمة PaymentCollectionResource
 * (مُسجَّل يدوياً عبر getHeaderWidgets() في ListPaymentCollections).
 */
class AwaitingSettlementAmountWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $count = PaymentCollection::where('status', PaymentCollectionStatus::Collected)
            ->whereNull('settlement_amount')
            ->count();

        return [
            Stat::make('تحصيلات تحتاج تحديد مبلغ التسوية', (string) $count)
                ->description($count > 0
                    ? 'فواتير بعملة أجنبية لم تُرجِع بوابة الدفع مبلغاً بالشيكل لها — اضغط للمراجعة'
                    : 'لا توجد تحصيلات بحاجة لمراجعة حالياً')
                ->descriptionIcon($count > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($count > 0 ? 'warning' : 'success')
                ->url(PaymentCollectionResource::getUrl('index', ['activeTab' => 'awaiting_settlement'])),
        ];
    }
}

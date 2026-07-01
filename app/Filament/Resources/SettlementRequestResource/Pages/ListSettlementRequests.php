<?php

namespace App\Filament\Resources\SettlementRequestResource\Pages;

use App\Filament\Resources\SettlementRequestResource;
use App\Models\SettlementRequest;
use App\Support\Enums\SettlementRequestStatus;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListSettlementRequests extends ListRecords
{
    protected static string $resource = SettlementRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // لا يوجد زر "إنشاء" — الطلبات تُنشأ فقط عبر SettlementRequestController
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('الكل'),

            'pending' => Tab::make('قيد المراجعة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', SettlementRequestStatus::Pending))
                ->badge(SettlementRequest::where('status', SettlementRequestStatus::Pending)->count())
                ->badgeColor('warning'),

            'approved' => Tab::make('مُعتمَدة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', SettlementRequestStatus::Approved))
                ->badge(SettlementRequest::where('status', SettlementRequestStatus::Approved)->count())
                ->badgeColor('info'),

            'paid' => Tab::make('مدفوعة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', SettlementRequestStatus::Paid))
                ->badge(SettlementRequest::where('status', SettlementRequestStatus::Paid)->count())
                ->badgeColor('success'),

            'rejected' => Tab::make('مرفوضة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', SettlementRequestStatus::Rejected))
                ->badge(SettlementRequest::where('status', SettlementRequestStatus::Rejected)->count())
                ->badgeColor('danger'),
        ];
    }
}

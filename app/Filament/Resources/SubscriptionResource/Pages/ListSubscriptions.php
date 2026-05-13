<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Resources\SubscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSubscriptions extends ListRecords
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('اشتراك يدوي جديد'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('الكل')
                ->badge(fn () => \App\Models\Subscription::count()),

            'active' => Tab::make('النشطة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'active'))
                ->badge(fn () => \App\Models\Subscription::where('status', 'active')->count())
                ->badgeColor('success'),

            'expiring' => Tab::make('تنتهي قريباً')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('status', 'active')
                    ->whereBetween('ends_at', [now(), now()->addDays(7)])
                )
                ->badge(fn () => \App\Models\Subscription::where('status', 'active')
                    ->whereBetween('ends_at', [now(), now()->addDays(7)])->count()
                )
                ->badgeColor('warning'),

            'cancelled' => Tab::make('الملغاة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'cancelled'))
                ->badge(fn () => \App\Models\Subscription::where('status', 'cancelled')->count())
                ->badgeColor('danger'),
        ];
    }
}

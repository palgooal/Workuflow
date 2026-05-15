<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Support\Enums\UserStatus;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('مستخدم جديد'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('الكل')
                ->badge(fn () => \App\Models\User::count()),

            'active' => Tab::make('النشطون')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', UserStatus::Active))
                ->badge(fn () => \App\Models\User::where('status', UserStatus::Active)->count())
                ->badgeColor('success'),

            'suspended' => Tab::make('الموقوفون')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', UserStatus::Suspended))
                ->badge(fn () => \App\Models\User::where('status', UserStatus::Suspended)->count())
                ->badgeColor('danger'),
        ];
    }
}

<?php

namespace App\Filament\Resources\FailedPaymentCallbackResource\Pages;

use App\Filament\Resources\FailedPaymentCallbackResource;
use Filament\Resources\Pages\ListRecords;

class ListFailedPaymentCallbacks extends ListRecords
{
    protected static string $resource = FailedPaymentCallbackResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

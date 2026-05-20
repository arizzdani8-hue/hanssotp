<?php

namespace App\Filament\Resources\OtpOrderResource\Pages;

use App\Filament\Resources\OtpOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOtpOrders extends ListRecords
{
    protected static string $resource = OtpOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}

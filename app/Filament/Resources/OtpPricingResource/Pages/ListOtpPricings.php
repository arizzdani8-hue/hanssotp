<?php

namespace App\Filament\Resources\OtpPricingResource\Pages;

use App\Filament\Resources\OtpPricingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOtpPricings extends ListRecords
{
    protected static string $resource = OtpPricingResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}

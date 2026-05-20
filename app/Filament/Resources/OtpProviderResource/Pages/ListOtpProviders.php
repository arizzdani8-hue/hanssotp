<?php

namespace App\Filament\Resources\OtpProviderResource\Pages;

use App\Filament\Resources\OtpProviderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOtpProviders extends ListRecords
{
    protected static string $resource = OtpProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}

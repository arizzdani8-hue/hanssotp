<?php

namespace App\Filament\Resources\OtpServiceResource\Pages;

use App\Filament\Resources\OtpServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOtpServices extends ListRecords
{
    protected static string $resource = OtpServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}

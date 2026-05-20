<?php

namespace App\Filament\Resources\OtpProviderResource\Pages;

use App\Filament\Resources\OtpProviderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOtpProvider extends EditRecord
{
    protected static string $resource = OtpProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}

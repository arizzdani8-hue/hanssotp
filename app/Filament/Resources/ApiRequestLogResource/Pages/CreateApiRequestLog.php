<?php

namespace App\Filament\Resources\ApiRequestLogResource\Pages;

use App\Filament\Resources\ApiRequestLogResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateApiRequestLog extends CreateRecord
{
    protected static string $resource = ApiRequestLogResource::class;
}

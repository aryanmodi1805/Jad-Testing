<?php

namespace App\Filament\Resources\EstimateBaseResource\Pages;

use App\Filament\Resources\EstimateBaseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEstimateBase extends CreateRecord
{
    protected static string $resource = EstimateBaseResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}

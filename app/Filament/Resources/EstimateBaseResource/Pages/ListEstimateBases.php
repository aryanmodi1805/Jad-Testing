<?php

namespace App\Filament\Resources\EstimateBaseResource\Pages;

use App\Filament\Resources\EstimateBaseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEstimateBases extends ListRecords
{
    protected static string $resource = EstimateBaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

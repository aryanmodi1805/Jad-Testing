<?php

namespace App\Filament\Resources\EstimateBaseResource\Pages;

use App\Filament\Resources\EstimateBaseResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditEstimateBase extends EditRecord
{
    protected static string $resource = EstimateBaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}

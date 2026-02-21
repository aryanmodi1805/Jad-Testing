<?php

namespace App\Filament\Resources\BlockReasonResource\Pages;

use App\Filament\Resources\BlockReasonResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageBlockReasons extends ManageRecords
{
    protected static string $resource = BlockReasonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

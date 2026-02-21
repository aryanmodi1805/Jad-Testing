<?php

namespace App\Filament\Resources\HowItWorkResource\Pages;

use App\Filament\Resources\HowItWorkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHowItWorks extends ListRecords
{
    protected static string $resource = HowItWorkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\QAResource\Pages;

use App\Filament\Resources\QAResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListQAs extends ListRecords
{
    protected static string $resource = QAResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

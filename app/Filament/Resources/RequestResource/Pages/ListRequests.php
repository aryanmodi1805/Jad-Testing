<?php

namespace App\Filament\Resources\RequestResource\Pages;

use App\Filament\Resources\RequestResource;
use Archilex\AdvancedTables\AdvancedTables;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRequests extends ListRecords
{
    use AdvancedTables;
    protected static string $resource = RequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}

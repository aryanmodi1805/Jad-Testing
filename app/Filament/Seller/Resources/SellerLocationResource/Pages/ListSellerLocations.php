<?php

namespace App\Filament\Seller\Resources\SellerLocationResource\Pages;

use App\Filament\Seller\Resources\SellerLocationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSellerLocations extends ListRecords
{
    protected static string $resource = SellerLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\SellerResource\Pages;

use App\Filament\Resources\SellerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSellers extends ListRecords
{
    protected static string $resource = SellerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Seller\Clusters\Settings\Resources\SellerQAResource\Pages;

use App\Filament\Actions\PageSellerProfileAction;
use App\Filament\Seller\Clusters\Settings\Resources\SellerQAResource;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ManageRecords;

class ManageSellerQAS extends ManageRecords
{
    protected static string $resource = SellerQAResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            PageSellerProfileAction::make()->record(Filament::auth()->user()),

        ];
    }
}

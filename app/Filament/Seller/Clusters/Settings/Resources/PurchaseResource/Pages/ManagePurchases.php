<?php

namespace App\Filament\Seller\Clusters\Settings\Resources\PurchaseResource\Pages;

use App\Filament\Seller\Clusters\Settings\Resources\PurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePurchases extends ManageRecords
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
         ];
    }
}

<?php

namespace App\Filament\Seller\Clusters\Settings\Resources\SellerQAResource\Pages;

use App\Filament\Seller\Clusters\Settings\Resources\SellerQAResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSellerQA extends EditRecord
{
    protected static string $resource = SellerQAResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

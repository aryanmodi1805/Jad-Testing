<?php

namespace App\Filament\Seller\Resources\RatingResource\Pages;

use App\Filament\Seller\Resources\RatingResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageRatings extends ManageRecords
{
    protected static string $resource = RatingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

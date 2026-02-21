<?php

namespace App\Filament\Customer\Resources\MyRatingResource\Pages;

use App\Filament\Customer\Resources\MyRatingResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageRatings extends ManageRecords
{
    protected static string $resource = MyRatingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

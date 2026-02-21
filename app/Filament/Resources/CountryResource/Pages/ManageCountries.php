<?php

namespace App\Filament\Resources\CountryResource\Pages;

use App\Filament\Resources\CountryResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Log;
use Stevebauman\Location\Facades\Location;

class ManageCountries extends ManageRecords
{
 //   use Translatable;
    protected static string $resource = CountryResource::class;



    protected function getHeaderActions(): array
    {
        if ($position = Location::get()) {
            // Successfully retrieved position.
            Log::info($position->ip);
            Log::info($position->countryCode);
        } else {
            // Failed retrieving position.
        }
        return [
            Actions\CreateAction::make(),
        ];
    }
}

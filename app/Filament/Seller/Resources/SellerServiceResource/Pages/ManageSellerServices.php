<?php

namespace App\Filament\Seller\Resources\SellerServiceResource\Pages;

use App\Filament\Seller\Resources\SellerServiceResource;
use App\Models\SellerLocation;
use App\Models\SellerServiceLocation;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ManageRecords;

class ManageSellerServices extends ManageRecords
{
    protected static string $resource = SellerServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->after(function ($record){
                $sellerLocations = SellerLocation::where('seller_id', auth('seller')->id())->get();

                foreach ($sellerLocations as $location) {
                    SellerServiceLocation::create([
                        'seller_service_id' => $record->id,
                        'seller_location_id' => $location->id,
                        'is_nationwide' => $location->is_nationwide,
                        'location_range' => $location->location_range
                    ]);
                }
            }),
        ];
    }


}

<?php

namespace App\Filament\Seller\Resources\SellerLocationResource\Pages;

use App\Filament\Seller\Resources\SellerLocationResource;
use App\Models\SellerLocation;
use App\Models\SellerService;
use Cheesegrits\FilamentGoogleMaps\Concerns\InteractsWithMaps;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateSellerLocation extends CreateRecord
{
    use InteractsWithMaps;

    protected static string $resource = SellerLocationResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        /** @var SellerLocation $location */
        $location = parent::handleRecordCreation($data);

        $sellerServices = SellerService::where('seller_id', auth('seller')->id())->get();

        $location->services()->attach(
            $sellerServices->pluck('id'),
            [
                'is_nationwide' => $data['is_nationwide'] ?? false,
                'location_range' => $data['location_range'] ?? null
            ]
        );
        return $location;
    }

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}

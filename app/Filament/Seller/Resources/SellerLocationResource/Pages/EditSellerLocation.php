<?php

namespace App\Filament\Seller\Resources\SellerLocationResource\Pages;

use App\Filament\Seller\Resources\SellerLocationResource;
use Cheesegrits\FilamentGoogleMaps\Concerns\InteractsWithMaps;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditSellerLocation extends EditRecord
{
    use InteractsWithMaps;


    protected static string $resource = SellerLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

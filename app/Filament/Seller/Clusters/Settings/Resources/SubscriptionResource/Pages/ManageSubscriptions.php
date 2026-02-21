<?php

namespace App\Filament\Seller\Clusters\Settings\Resources\SubscriptionResource\Pages;

use App\Filament\Seller\Clusters\Settings\Resources\SubscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSubscriptions extends ManageRecords
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}

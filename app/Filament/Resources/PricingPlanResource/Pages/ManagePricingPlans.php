<?php

namespace App\Filament\Resources\PricingPlanResource\Pages;

use App\Filament\Resources\PricingPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePricingPlans extends ManageRecords
{
    protected static string $resource = PricingPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->steps(self::$resource::getSteps()),
        ];
    }
}

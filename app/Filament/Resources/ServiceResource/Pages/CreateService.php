<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use Filament\Facades\Filament;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Automatically set the country_id (tenant) when creating a service
        $tenant = getCurrentTenant();
        if ($tenant) {
            $data['country_id'] = $tenant->id;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        // In production with tenant domains, Filament automatically detects tenant from domain
        // So we don't need to pass it explicitly
        return $this->getResource()::getUrl('edit', [
            'record' => $this->record
        ]);
    }
}

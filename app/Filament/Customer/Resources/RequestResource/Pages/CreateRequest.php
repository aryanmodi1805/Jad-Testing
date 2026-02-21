<?php

namespace App\Filament\Customer\Resources\RequestResource\Pages;

use App\Filament\Customer\Resources\RequestResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRequest extends CreateRecord
{
    protected static string $resource = RequestResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['customer_id'] = auth('customer')->id();

        return $data;
    }
}

<?php

namespace App\Filament\Resources\QAResource\Pages;

use App\Filament\Resources\QAResource;
use Filament\Resources\Pages\CreateRecord;

class CreateQA extends CreateRecord
{
    protected static string $resource = QAResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}

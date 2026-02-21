<?php

namespace App\Filament\Resources\FailedInvoiceResource\Pages;

use App\Filament\Resources\FailedInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageFailedInvoices extends ManageRecords
{
    protected static string $resource = FailedInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action since failed invoices are auto-generated
        ];
    }
}

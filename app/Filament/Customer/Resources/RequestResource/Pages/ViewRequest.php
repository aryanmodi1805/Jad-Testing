<?php

namespace App\Filament\Customer\Resources\RequestResource\Pages;

use App\Enums\RequestStatus;
use App\Enums\ResponseStatus;
use App\Filament\Actions\RatingAction;
use App\Filament\Actions\TableSellerProfileAction;
use App\Filament\Customer\Resources\RequestResource;
use App\Models\Request;
use App\Models\Seller;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\On;

#[On('refreshViewRequest')]
class ViewRequest extends ViewRecord
{

    protected static string $resource = RequestResource::class;

    public function getRecordTitle(): string|Htmlable
    {
        return __('string.view_service', ['service' => $this->record->service->name ,  'status' => $this->record->status->getLabel()]);
    }

    protected function getHeaderActions(): array
    {
        return [
            RatingAction::make()->rater(fn() => Filament::auth()->user())
                ->modalHeading(__('seller.rate.rate_seller'))
                ->label(__('seller.rate.rate_seller'))
                ->rateable(fn(Request $record) => $record->responses()->where('status', ResponseStatus::Hired)->first())
                ->extraAttributes(fn($record) => [
                    'style' => 'display:none'
                ]),
        ];
    }


}

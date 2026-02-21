<?php

namespace App\Filament\Customer\Resources\RequestResource\Pages;

use App\Filament\Actions\RatingAction;
use App\Filament\Customer\Resources\RequestResource;
use App\Interfaces\HasWizard;
use App\Models\Country;
use App\Models\Response;
use App\Traits\InteractsWithServiceWizard;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\Url;

class ListRequests extends ListRecords
{
    use InteractsWithServiceWizard;

    #[Url]
    public $action;

    protected static string $resource = RequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('place-new-request')
                ->label(__('services.requests.place_new_request'))
                ->color('primary')
                ->form([
                    Select::make('service_id')
                        ->relationship('services', 'name' , fn($query) => $query->customerMostRequested(auth('customer')->user()))
                        ->preload()
                        ->searchDebounce(200)
                        ->searchable()
                        ->required()
                        ->label(__('columns.service')),
                ])
                ->closeModalByClickingAway(false)
                ->action(fn($data, $livewire) => $livewire->dispatch('open-wizard', serviceId: $data['service_id'])
                ),

            RatingAction::make()
                ->label(__('localize.site_reviews.single'))
                ->rateable(Country::find(getCountryId()))
                ->rater(auth('customer')->user()),

            RatingAction::make()
                ->name('rating-service-action')
                ->visible($this->action === 'rating-service-action' && session()->exists('response_id'))
                ->label(__('localize.service_rating.single'))
                ->rateable(Response::find(session('response_id')))
                ->rater(auth('customer')->user()),


                                                                                                                                                                                                                    ];
    }

}

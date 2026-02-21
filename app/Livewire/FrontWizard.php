<?php

namespace App\Livewire;

use App\Filament\Actions\WizardAction;
use App\Filament\Components\Map;
use App\Interfaces\HasWizard;
use App\Rules\LatLngInCountry;
use App\Traits\InteractsWithServiceWizard;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\ToggleButtons;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Wallo\FilamentSelectify\Components\ButtonGroup;

class FrontWizard extends WizardComponent implements HasWizard
{
    use InteractsWithServiceWizard;
    public $serviceId;

    #[On('open-wizard')]
    public function openWizard($serviceId)
    {
        $this->setCurrentWizardData([]);
        $this->serviceId = $serviceId;
        $this->replaceMountedAction('location');
    }

    public function locationAction(): Action
    {

        $this->mountedActionsData[0]['location'] ??= [];

        return Action::make('location-action')
            ->modalSubmitActionLabel(__('string.continue'))
            ->mountUsing(fn($livewire) => $livewire->dispatch('request-location'))
            ->modalHeading(__('string.wizard.select_service_location'))
            ->extraModalWindowAttributes([
                'style'=>'max-height: 95vh;'
            ])
            ->form([
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('current_location')
                        ->label(__('string.wizard.current_location'))
                        ->link()
                        ->icon('tabler-current-location')
                        ->action(function (FrontWizard $livewire) {
                            $messages = [
                                1 => __('requests.geolocation.permission_denied'),
                                2 => __('requests.geolocation.position_unavailable'),
                                3 => __('requests.geolocation.timeout'),
                                0 => __('requests.geolocation.unknown_error'),
                            ];
                            $livewire->js(<<<JS
                                    if (navigator.geolocation) {
                                        navigator.geolocation.getCurrentPosition(position => {
                                            \$wire.set('mountedActionsData.0.location',{lat: position.coords.latitude, lng: position.coords.longitude});
                                        }, error => {
                                            switch (error.code) {
                                                case error.PERMISSION_DENIED:
                                                    window.showAlert('{$messages[1]}');
                                                    break;
                                                case error.POSITION_UNAVAILABLE:
                                                    window.showAlert('{$messages[2]}');
                                                    break;
                                                case error.TIMEOUT:
                                                    window.showAlert('{$messages[3]}');
                                                    break;
                                                default:
                                                    window.showAlert('{$messages[0]}');
                                                    break;
                                            }
                                        });
                                    } else {
                                        window.showAlert('{$messages[0]}');
                                    }
                            JS);
                        }),
                ])->alignEnd(),

                Forms\Components\Grid::make(1)->schema([
                    Map::make('location')
                        ->label(__('string.wizard.map_location'))
                        ->columnSpanFull()
                        ->autocomplete(
                            fieldName: 'location_name',
                            placeField: 'name',
                            countries: [getCountryCode()]
                        )
                        ->defaultLocation(getTenant()?->location ?? ['lat' => 24.7136, 'lng' => 46.6753])
                        ->draggable() // allow dragging to move marker
                        ->clickable(true) // allow clicking to move marker
                        ->geolocate() // adds a button to request device location and set map marker accordingly
                        ->geolocateLabel(__('string.current_location'))
                        ->autocompleteReverse(true)
                        ->defaultZoom(6)
                        ->geolocateOnLoad(true, true)
                        ->height('40vh')
                        ->geolocate(),
                    Forms\Components\TextInput::make('location_name')
                        ->required()
                        ->rule(fn($get) => new LatLngInCountry($get('location.lat'), $get('location.lng')))
                        ->label(__('string.wizard.descriptive_location'))
                        ->prefix(__('localize.Choose') . ':')
                        ->placeholder(__('services.requests.start_type')),
                ]),



            ])

            ->action(function (FrontWizard $livewire, $data) {
                $livewire->replaceMountedAction('wizard', [
                    'service_id' => $this->serviceId,
                    'lat' => $data['location']['lat'],
                    'lng' => $data['location']['lng'],
                    'location_name' => $data['location_name']
                ]);
            });
    }


    public function wizardAction(): Action
    {
        return WizardAction::make();
    }

    public function redirectClientAction() : Action
    {
        return Action::make('redirect-client-action')->requiresConfirmation()
            ->modalContent(new HtmlString(__('string.wizard.confirm_redirect')))
            ->modalDescription(null)
            ->modalHeading(__('string.wizard.continue_request'))
            ->modalCancelAction(false)
            ->modalCloseButton(false)
            ->closeModalByClickingAway(false)
            ->closeModalByEscaping(false)
            ->modalFooterActions([
                Action::make('cancel')
                    ->requiresConfirmation()
                    ->label(__('string.cancel'))
                    ->color('warning')
                    ->modalSubmitActionLabel(__('string.yes'))
                    ->modalCancelActionLabel(__('string.no'))
                    ->modalContent(new HtmlString(__('string.cancel_warning')))
                    ->modalDescription(null)
                    ->modalHeading(__('string.warning'))
                    ->action(fn() => $this->closeActionModal()),
                Action::make('confirm')
                    ->label(__('string.continue'))
                    ->color('primary')
                    ->action(fn() =>  redirect(route('filament.customer.auth.login'))),
            ]);
    }

    public function render()
    {
        return view('livewire.front-wizard');
    }
}

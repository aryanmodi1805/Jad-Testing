<?php

namespace App\Filament\Actions;

use App\Filament\Customer\Resources\RequestResource\Pages\ListRequests;
use App\Interfaces\HasWizard;
use App\Services\RequestService;
use App\Services\WizardService;
use Filament\Actions\Action;
use Filament\Actions\StaticAction;
use Filament\Forms\Components\Concerns\ListensToEvents;
use Filament\Forms\Components\Wizard;
use Filament\Notifications\Notification;


class WizardAction extends Action
{
    public mixed $service_id = null;
    public mixed $lat= null;
    public mixed $lng= null;
    public mixed $location_name= null;

    public static function getDefaultName(): ?string
    {
        return 'wizard-action';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->icon('heroicon-o-banknotes');


        $this->successNotificationTitle(__('string.estimate.success_note'));

        $this->steps(fn(HasWizard $livewire) => (new WizardService())->getWizardSteps($livewire->getCurrentWizardData()));

        $this->modalHeading(function(HasWizard $livewire) {
            $service = $livewire->getCurrentWizardData()['service'];

            return __('string.wizard.new_request', ['service_name' =>  $service->name]);
        });

        $this->closeModalByClickingAway(false);

        $this->mountUsing(function($arguments , WizardAction $action , HasWizard $livewire){
            $service_id = $arguments['service_id'] ?? null;
            $lat = $arguments['lat'] ?? null;
            $lng = $arguments['lng'] ?? null;
            $location_name = $arguments['location_name'] ?? null;

            if($service_id == null){
                return;
            }

            $wizardService = new WizardService(
                service_id: $service_id,
                lat: $lat,
                lng: $lng,
                location_name: $location_name,
            );

            $livewire->setCurrentWizardData($wizardService->getWizardData());

            $action->steps($wizardService->getWizardSteps($livewire->getCurrentWizardData()));
        });

        $this->action(function (array $data, $arguments, HasWizard $livewire): void {
            $questionData = $livewire->getCurrentWizardData();

            $requestService = new RequestService(
                service_id: $arguments['service_id'],
                countryId: getCountryId(),
                customer: auth('customer')->user(),
                lat: $arguments['lat'],
                lng: $arguments['lng'],
                location_name: $arguments['location_name'],
                questions: $questionData['questions'],
                questionAnswers: $questionData['questionAnswers'],
                answersData: $data,

            );
            if (auth('customer')->check()) {
                try {
                    $requestService->createRequest();

                } catch (\Exception $e) {
                    Notification::make()
                        ->title(__('requests.unknown_error'))
                        ->danger()
                        ->send();
                }
            } else {

                \Session::put('pending_request', $requestService);
                $livewire->replaceMountedAction('redirectClient');

            }
        });
        $this->modifyWizardUsing(fn(Wizard $wizard) =>
            $wizard->extraAttributes([
                'x-effect' => <<<JS
                    const selector = 'li:nth-child('+(parseInt(step)+1)+')';
                    setTimeout(() => {
                        window.scrollToElementIfNotVisible(\$el.querySelector('.fi-fo-wizard-header').querySelector(selector),
                            \$el.closest('.fi-fo-wizard'))
                    }, 50);
                JS,
            ])


        );


    }
}

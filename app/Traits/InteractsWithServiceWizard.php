<?php

namespace App\Traits;

use Cheesegrits\FilamentGoogleMaps\Concerns\InteractsWithMaps;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;

trait InteractsWithServiceWizard
{
    use InteractsWithMaps;

    public array $currentWizardData = [];

    public function getCurrentWizardData(): array
    {
        return $this->currentWizardData;
    }

    public function setCurrentWizardData(array $data): void
    {
        $this->currentWizardData = $data;
    }

    public function validate($rules = null, $messages = [], $attributes = []): array
    {
        $this->js(<<<JS
                window.dispatchEvent(new CustomEvent('validated'))
        JS);

        return parent::validate($rules, $messages, $attributes);
    }

    protected function onValidationError(ValidationException $exception): void {
        foreach ($exception->validator->failed() as $field => $errors) {
            $errors = json_encode($errors);
            $this->js(<<<JS
                                window.dispatchEvent(new CustomEvent('validation-error', {
                                    detail: {
                                        field: '{$field}',
                                        errors: {$errors}
                                    }
                                }))
            JS);
        }
        $this->js(<<<JS
                            window.showAlert('{$exception->getMessage()}');
        JS);

        parent::onValidationError($exception);

    }


}

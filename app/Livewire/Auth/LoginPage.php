<?php

namespace App\Livewire\Auth;

use App\Extensions\Login;
use Filament\Actions\Action;
use Filament\Forms\Components\Component;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Exception ;

class LoginPage extends Login
{
    protected static string $view = 'livewire.auth.login-page';
    protected static string $layout = 'components.layouts.auth';


    /**
     * @throws Exception
     */
    public function getScope(): string
    {
        return  filament()->getCurrentPanel()->getId()==='seller' ? 'seller' : 'customer';
      //  return str_contains(Route::currentRouteName(), 'seller') ? 'seller' : 'customer';

    }
//    protected function throwFailureValidationException(): never
//    {
//        throw ValidationException::withMessages([
//            'data.email' => __('login.messages.failed'),
//        ]);
//    }
    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()
            ->extraInputAttributes(['tabindex' => 1, 'class' => 'block w-full py-4 pr-20  placeholder:text-gray-400 sm:text-sm sm:leading-6']);
    }

    protected function getPasswordFormComponent(): Component
    {
        return parent::getPasswordFormComponent()
            ->extraInputAttributes(['tabindex' => 2, 'class' => 'block w-full py-4 pr-20  placeholder:text-gray-400 sm:text-sm sm:leading-6']);
    }

    protected function getAuthenticateFormAction(): Action
    {
        return parent::getAuthenticateFormAction()->extraAttributes([
            'class' => 'mt-2 rounded-md bg-primary-500 px-8 py-4 font-semibold leading-5 hover:bg-secondary-500'
        ]);
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.phone' => __('filament-panels::pages/auth/login.messages.failed'),

        ]);
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        getPhoneInput(),
                        $this->getPasswordFormComponent(),
                        $this->getRememberFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }


    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'phone' => $data['phone'],
            'password' => $data['password'],
        ];
    }


}

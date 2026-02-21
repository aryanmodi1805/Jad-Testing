<?php

namespace App\Livewire\Auth;

use App\Livewire\CustomerAgreement;
use App\Livewire\SellerAgreement;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Component;
use Filament\Pages\Auth\Register;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\HtmlString;


class RegisterPage extends Register
{
    protected static string $view = 'livewire.auth.register-page';
    protected static string $layout = 'components.layouts.auth';

    public function getScope()
    {
        return str_contains(Route::currentRouteName(), 'seller') ? 'seller' : 'customer';
    }

    public function getRegisterFormAction(): Action
    {
        return parent::getRegisterFormAction()
            ->extraAttributes([
                'class' => 'mt-2 rounded-md bg-primary px-8 py-4 font-semibold leading-5 hover:bg-secondary-500'
            ]);
    }

    protected function mutateFormDataBeforeRegister(array $data): array
    {
        $tenant = getCurrentTenant();
        $data['country_id'] = $tenant?->id ?? \App\Models\Country::first()?->id ?? 1;
        return $data;
    }

    protected function getForms(): array
    {
        $current_tenant = getSubdomain() ?? 'sa';
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent(),
                        getPhoneInput('phone', $this->getUserModel()),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                        Checkbox::make('terms_of_service')
                            ->validationAttribute(__('nav.terms-conditions'))
                            ->label(function () {
                                $url = filament()->getCurrentPanel()->getPath() == "seller" ? SellerAgreement::getSlug() : CustomerAgreement::getSlug();
                                $terms = '<a href="' . url($url) . '" target="_blank" class="text-indigo-500 hover:underline ">' . __('nav.terms-conditions') . '</a>';
                                $policy = '<a href="' . url('/privacy-policy') . '" target="_blank" class="text-indigo-500  hover:underline "  >' . __('nav.privacy-policy') . '</a>';

                                return new HtmlString(__('string.I agree to the terms of service', ['terms' => $terms, 'policy' => $policy]));
                            })
                            ->accepted()
                    ])
                    ->statePath('data')
            ),
        ];
    }

    protected function getNameFormComponent(): Component
    {
        return parent::getNameFormComponent()
            ->maxLength(30)
            ->extraInputAttributes(['tabindex' => 1, 'class' => 'block w-full py-4 pr-20  placeholder:text-gray-400 sm:text-sm sm:leading-6']);
    }

    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()
            ->required(false)
            ->nullable()
            ->dehydrateStateUsing(fn ($state) => filled($state) ? $state : null)
            ->helperText(__('auth.email_optional_helper'))
            ->autocomplete()
            ->extraInputAttributes(['tabindex' => 1, 'class' => 'block w-full py-4 pr-20  placeholder:text-gray-400 sm:text-sm sm:leading-6']);

    }

    protected function getPasswordFormComponent(): Component
    {
        return parent::getPasswordFormComponent()
            ->autocomplete('new-password')
            ->validationMessages([
                'min' => ['string' => __('passwords.validation.min')],
            ])
            ->extraInputAttributes(['tabindex' => 2, 'class' => 'block w-full py-4 pr-20  placeholder:text-gray-400 sm:text-sm sm:leading-6']);

    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return parent::getPasswordConfirmationFormComponent()
            ->autocomplete('new-password')
            ->extraInputAttributes(['tabindex' => 2, 'class' => 'block w-full py-4 pr-20  placeholder:text-gray-400 sm:text-sm sm:leading-6']);
    }

}

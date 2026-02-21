<?php


namespace App\Filament\Seller\Pages\Auth;

use App\Models\Country;
use Filament\Forms\Components\Component;
use Filament\Pages\Auth\Register as BaseLogin;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class Register extends BaseLogin
{

    protected function mutateFormDataBeforeRegister(array $data): array
    {
        $tenant = getCurrentTenant();
        $data['country_id'] = $tenant?->id ?? Country::first()?->id ?? 1;
        return $data;
    }

    protected function getForms(): array
    {
        // Use country code (e.g., 'SA') instead of subdomain slug
        $countryCode = getCountryCode() ?? 'SA';
        $current_tenant = strtolower($countryCode); // Phone input expects lowercase
        
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent(),
                        PhoneInput::make('phone')->required()
                            ->label(__('cv.phone_number'))
                            ->initialCountry($current_tenant)
                            ->defaultCountry($current_tenant)
                            ->onlyCountries([$current_tenant])
                            ->unique($this->getUserModel()),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->statePath('data')
                    ->inlineLabel(),
            ),
        ];
    }

    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()
            ->required(false)
            ->nullable()
            ->dehydrateStateUsing(fn ($state) => filled($state) ? $state : null)
            ->helperText(__('auth.email_optional_helper'));
    }
}

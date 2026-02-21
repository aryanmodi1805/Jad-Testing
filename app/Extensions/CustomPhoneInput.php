<?php

namespace App\Extensions;


use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class CustomPhoneInput extends PhoneInput
{

    public function getJsonPhoneInputConfiguration(): string
    {
        return json_encode([
            'strictMode' => true,

            'allowDropdown' => $this->allowDropdown,

            'autoInsertDialCode' => $this->autoInsertDialCode,

            'countrySearch' => $this->countrySearch,

            'formatAsYouType' => $this->formatAsYouType,

            'showFlags' => $this->showFlags,

            'useFullscreenPopup' => $this->useFullscreenPopup,

            'autoPlaceholder' => $this->autoPlaceholder,

            'customContainer' => $this->customContainer,

            'dropdownContainer' => $this->dropdownContainer,

            'excludeCountries' => $this->excludeCountries ?? [],

            'formatOnDisplay' => $this->formatOnDisplay,

            'performIpLookup' => $this->canPerformIpLookup,

            'initialCountry' => strtolower($this->initialCountry ?? 'sa'),

            'i18n' => $this->i18n,

            'showSelectedDialCode' => $this->showSelectedDialCode,

            'nationalMode' => $this->nationalMode,

            'onlyCountries' => $this->onlyCountries ?? ['sa'],

            'placeholderNumberType' => $this->placeholderNumberType,

            'preferredCountries' => $this->preferredCountries ?? [],

            'displayNumberFormat' => $this->displayNumberFormat,

            'focusNumberFormat' => $this->focusNumberFormat,

            'inputNumberFormat' => $this->inputNumberFormat,
        ]);
    }
}

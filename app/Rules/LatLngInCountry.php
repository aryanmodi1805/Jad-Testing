<?php

namespace App\Rules;

use App\Services\GeocodeService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LatLngInCountry implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */

    public $lat;
    public $lng;
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $currentCountry = getCurrentTenant();

        $geocodeService = new GeocodeService();
        $countryCode = $geocodeService->getCountryCode($this->lat, $this->lng);

        if ($countryCode !== $currentCountry->code) {
            $fail(__('validation.validate_country', ['attribute' => __('string.wizard.map_location')]));
        }
    }

    public function __construct($lat , $lng)
    {
        $this->lat = $lat;
        $this->lng = $lng;
    }


}

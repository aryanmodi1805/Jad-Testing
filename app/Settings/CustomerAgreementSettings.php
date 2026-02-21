<?php

namespace App\Settings;

use Illuminate\Support\Facades\Storage;
use Spatie\LaravelSettings\Settings;

class CustomerAgreementSettings extends Settings
{
    public ?string $customer_agreement_en;
    public ?string $customer_agreement_ar;

    public function getCustomerAgreement($lang): string | null
    {
        return $this->{"customer_agreement_$lang"};
    }
    public static function group(): string
    {
        return 'customer_agreement';
    }
}

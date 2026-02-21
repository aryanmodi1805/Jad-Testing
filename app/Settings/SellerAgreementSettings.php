<?php

namespace App\Settings;

use Illuminate\Support\Facades\Storage;
use Spatie\LaravelSettings\Settings;

class SellerAgreementSettings extends Settings
{

    public ?string $seller_agreement_en;
    public ?string $seller_agreement_ar;

    public function getSellerAgreement($lang): string | null
    {
        return $this->{"seller_agreement_$lang"};
    }

    public static function group(): string
    {
        return 'seller_agreement';
    }
}

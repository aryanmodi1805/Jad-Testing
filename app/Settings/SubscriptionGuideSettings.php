<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SubscriptionGuideSettings extends Settings
{
    public ?string $premium_guide_ar;
    public ?string $premium_guide_en;
    public ?string $credit_guide_ar;
    public ?string $credit_guide_en;


    public function getPremiumSubscriptionGuide($lang): string | null
    {
        return $this->{"premium_guide_$lang"};
    }
    public function getCreditSubscriptionGuide($lang): string | null
    {
        return $this->{"credit_guide_$lang"};
    }
    public static function group(): string
    {
        return 'subscription_guide';
    }
}

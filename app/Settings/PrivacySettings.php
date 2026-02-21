<?php

namespace App\Settings;

use Illuminate\Support\Facades\Storage;
use Spatie\LaravelSettings\Settings;

class PrivacySettings extends Settings
{
    public ?string $privacy_policy_en;
    public ?string $privacy_policy_ar;

    public function getPrivacyPolicy($lang): string | null
    {
        return $this->{"privacy_policy_$lang"};
    }

    public static function group(): string
    {
        return 'privacy_policy';
    }
}

<?php

namespace App\Settings;

use App\Traits\HasTranslations;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelSettings\Settings;

class HowCustomerSettings extends Settings
{
    public ?string $step_1_title_en;
    public ?string $step_1_title_ar;
    public ?string $step_1_description_en;
    public ?string $step_1_description_ar;
    public ?string $step_1_image;

    public ?string $step_2_title_en;
    public ?string $step_2_title_ar;
    public ?string $step_2_description_en;
    public ?string $step_2_description_ar;
    public ?string $step_2_image;

    public ?string $step_3_title_en;
    public ?string $step_3_title_ar;
    public ?string $step_3_description_en;
    public ?string $step_3_description_ar;
    public ?string $step_3_image;

    public ?string $step_4_title_en;
    public ?string $step_4_title_ar;
    public ?string $step_4_description_en;
    public ?string $step_4_description_ar;
    public ?string $step_4_image;

    public function getStepTitle($stepNumber, $lang): string | null
    {
        return $this->{"step_{$stepNumber}_title_$lang"};
    }

    public function getStepDescription($stepNumber, $lang): string | null
    {
        return $this->{"step_{$stepNumber}_description_$lang"};
    }

    public function getStepImage($stepNumber): string | null
    {
        return Storage::url($this->{"step_{$stepNumber}_image"});
    }

    public static function group(): string
    {
        return 'howCustomer';
    }
}

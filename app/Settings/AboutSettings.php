<?php

namespace App\Settings;

use Illuminate\Support\Facades\Storage;
use Spatie\LaravelSettings\Settings;

class AboutSettings extends Settings
{
    public ?string $about_title_en;

    public ?string $about_title_ar;

    public ?string $about_sub_title_en;

    public ?string $about_sub_title_ar;

    public ?string $about_en;

    public ?string $about_ar;

    public ?string $about_image;

    public ?string $location_en;

    public ?string $location_ar;

    public ?array $location_map;

    public ?string $phone;

    public ?string $email;

    public function getLocationContent($lang) :string | null
    {
        return $this->{"location_$lang"};
    }

    public function getAboutTitle($lang): string | null
    {
        return $this->{"about_title_$lang"};
    }

    public function getAboutSubTitle($lang): string | null
    {
        return $this->{"about_sub_title_$lang"};
    }

    public function getAbout($lang): string | null
    {
        return $this->{"about_$lang"};
    }

    public function getAboutImage(): string | null
    {
        return Storage::url($this->{"about_image"});

    }
    public static function group(): string
    {
        return 'about';
    }
}

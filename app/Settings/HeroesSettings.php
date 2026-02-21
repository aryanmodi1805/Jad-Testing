<?php

namespace App\Settings;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Spatie\LaravelSettings\Settings;

class HeroesSettings extends Settings
{

    public ?string $main_hero;
    public ?string $sub_hero;
    public ?string $text_ar;
    public ?string $text_en;

    public function getMainHero(): string | null
    {
        return $this->main_hero ? Storage::url($this->{"main_hero"}) : "/assets/photos/hero.jpg";
    }

    public function getSubHero(): string | null
    {
        return $this->sub_hero ? Storage::url($this->{"sub_hero"}) : '/assets/photos/hero.jpg';
    }

    public static function group(): string
    {
        return 'heroes';
    }
}

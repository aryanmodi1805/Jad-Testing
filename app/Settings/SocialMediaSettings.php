<?php

namespace App\Settings;

use Illuminate\Support\Facades\Storage;
use Spatie\LaravelSettings\Settings;

class SocialMediaSettings extends Settings
{
    public ?string $instagram;

    public ?string $facebook;

    public ?string $x;

    public ?string $linkedin;
    
    public ?string $youtube;
    
    public ?string $tiktok;

    public function getSocialMediaLinks() : array
    {
        return array_filter([
            'instagram' => $this->instagram,
            'facebook' => $this->facebook,
            'x' => $this->x,
            'linkedin' => $this->linkedin,
            'youtube' => $this->youtube,
            'tiktok' => $this->tiktok,
        ], function($value) {
            return !is_null($value);
        });

    }
    public static function group(): string
    {
        return 'social_media';
    }
}

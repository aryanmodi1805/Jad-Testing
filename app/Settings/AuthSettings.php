<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;
use Illuminate\Support\Facades\Storage;

class AuthSettings extends Settings
{

    public ?string $login_page_image;
    public ?string $register_page_image;

    public function getLoginPageImage(): string | null
    {
        return Storage::url($this->login_page_image);
    }

    public function getRegisterPageImage(): string | null
    {
        return Storage::url($this->register_page_image);
    }

    public static function group(): string
    {
        return 'auth';
    }
}

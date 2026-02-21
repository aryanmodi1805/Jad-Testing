<?php

namespace App\Extensions;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Pages\Auth\Login as BaseLogin;


class UserResolver implements \OwenIt\Auditing\Contracts\UserResolver {
    public static function resolve()
    {
        return Filament::auth()->user();
    }
}

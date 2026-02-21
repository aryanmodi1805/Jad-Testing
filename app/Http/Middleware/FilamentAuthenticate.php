<?php

namespace App\Http\Middleware;

use App\Events\LoggedIn;
use Filament\Events\Auth\Registered;
use Filament\Facades\Filament;
use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Model;

class FilamentAuthenticate extends \Filament\Http\Middleware\Authenticate
{
    protected function authenticate($request, array $guards): void
    {
        parent::authenticate($request, $guards);

        $guard = Filament::auth();

        $this->auth->shouldUse(Filament::getAuthGuard());

        /** @var Model $user */
        $user = $guard->user();

        //Check if user belongs to a tenant
        if ($user->country_id == null) {
            abort(403, 'You are not allowed to access this tenant');
        }
    }
    protected function redirectTo($request): ?string
    {
        return $request->expectsJson() ? null : Filament::getCurrentPanel()->route('auth.login');
    }
}

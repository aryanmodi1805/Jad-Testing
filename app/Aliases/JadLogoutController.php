<?php

namespace App\Aliases;

use Filament\Facades\Filament;
use Filament\Http\Controllers\Auth\LogoutController;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadLogoutController
{
    public function __invoke(Request $request): LogoutResponse
    {
        Filament::auth()->logout();

        $loggedIn = false;

        foreach (Filament::getPanels() as $panel) {
            if($panel->auth()->check()){
                $loggedIn = true;
                break;
            }
        }

        if(!$loggedIn){
            session()->invalidate();
            session()->regenerateToken();
        }

        return app(LogoutResponse::class);
    }
}

<?php

namespace App\Extensions;

use App\Events\LoggedIn;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Validation\ValidationException;


class Login extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        if (! Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();

        if (
            ($user instanceof FilamentUser) &&
            (! $user->canAccessPanel(Filament::getCurrentPanel()))
        ) {
            Filament::auth()->logout();

            throw ValidationException::withMessages([
                'data.email' => __('login.messages.blocked'),
            ]);
//            $this->throwFailureValidationException();
        }

        $loggedIn = false;

        foreach (Filament::getPanels() as $panel) {
            if($panel->auth()->check()){
                $loggedIn = true;
                break;
            }
        }

        if (!$loggedIn){
            session()->regenerate();
        }

        event(new LoggedIn($user));


        return app(LoginResponse::class);
    }



}

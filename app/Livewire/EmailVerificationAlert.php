<?php

namespace App\Livewire;

use App\Enums\Wallet\SubscriptionStatus;
use App\Models\Subscription;
use Carbon\Carbon;
use DB;
use Exception;
use Filament\Facades\Filament;
use Filament\Notifications\Auth\VerifyEmail;
use Livewire\Component;

class EmailVerificationAlert extends Component
{
    public string $guard = "";
    public string $path = "";
    public string $url = "/";
    public int $expiredSubscriptions = 0;

    public function mount()
    {
        $this->guard = filament()->getCurrentPanel()->getAuthGuard();
        $this->path = filament()->getCurrentPanel()->getPath();
        $this->url = filament()->getCurrentPanel()->getEmailVerificationPromptUrl() ?? filament()->getCurrentPanel()->getUrl();
        $this->expiredSubscriptions= $this->guard === 'seller' ? $this->getExpiredSubscriptions():0;
    }

    public function resend()
    {
        $user = auth($this->guard)->user();

        if ($user->hasVerifiedEmail()) {
            return;
        }

        if (!method_exists($user, 'notify')) {
            $userClass = $user::class;
            throw new Exception("Model [{$userClass}] does not have a [notify()] method.");
        }

        $notification = app(VerifyEmail::class);
        $notification->url = Filament::getVerifyEmailUrl($user);
        $user->notify($notification);
    }

    public function render()
    {
        return view('livewire.email-verification-alert');
    }

    public function getExpiredSubscriptions(): int
    {
        $this->expiredSubscriptions =Subscription::where('seller_id', auth($this->guard)->user()->id)
            ->expired()
            ->count();

        return $this->expiredSubscriptions;
    }
}

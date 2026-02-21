<?php

namespace App\Livewire;


use App\Filament\Seller\Pages\SellerDashboard;
use App\Models\Seller;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Auth\VerifyEmail;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class SwitchSeller extends SwitchAccount
{
    public $destinationClass = Seller::class;
    public $destinationPage = SellerDashboard::class;

    public $source = 'customer';

    public $destination = 'seller';

}

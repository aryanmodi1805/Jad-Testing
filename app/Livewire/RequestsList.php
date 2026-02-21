<?php

namespace App\Livewire;

use App\Filament\Actions\PageChatAction;
use App\Filament\Actions\InfoListChatAction;
use App\Filament\Wallet\Actions\ChargeCreditAction;
use App\Filament\Wallet\Actions\PayRequestAction;
use App\Models\CustomerAnswer;
use App\Models\Request;
use App\Models\Seller;
use App\Services\SellerServiceHandler;
use App\Settings\GeneralSettings;
use App\Settings\RequestSettings;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RequestsList extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    public function render()
    {
        return view('livewire.requests-list');
    }
}

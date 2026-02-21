<?php

namespace App\Livewire;

use App\Filament\Wallet\Actions\PayRequestAction;
use App\Models\Request;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Component;

class PayLead extends Component implements  HasForms,HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

   public $record  ;

   public function mount($record): void
   {
       $this->record = $record ;
//       $this->record = Request::find($record) ;

   }
    public function payRequestAction(): Action
    {
        return PayRequestAction::make('payRequest')
            ->requiresConfirmation()
            ->action(fn () => dd($this->record));
    }

    public function render()
    {
        return view('livewire.pay-lead');
    }
}

<?php

namespace App\Livewire\HowItWorks;

use Livewire\Component;
use App\Settings\HowCustomerSettings;

class CustomerPage extends Component
{
    public function render()
    {
        return view('livewire.how-it-works.customer-page',[
            'howItWorksCustomer' => app(HowCustomerSettings::class)
        ]);
    }
}

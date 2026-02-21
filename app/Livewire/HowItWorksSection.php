<?php

namespace App\Livewire;

use App\Settings\HowCustomerSettings;
use Livewire\Component;

class HowItWorksSection extends Component
{


    public function render()
    {
        return view('livewire.how-it-works-section', [
            'howItWorksCustomer' => app(HowCustomerSettings::class)
        ]);
    }
}

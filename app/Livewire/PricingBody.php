<?php

namespace App\Livewire;

use Livewire\Component;
use App\Settings\PricingSettings;

class PricingBody extends Component
{
    public function render()
    {
        return view('livewire.pricing-body',[
            'pricing' => app(PricingSettings::class)
        ]);
    }
}

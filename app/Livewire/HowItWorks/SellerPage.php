<?php

namespace App\Livewire\HowItWorks;

use Livewire\Component;
use App\Settings\HowSellerSettings;

class SellerPage extends Component
{
    public function render()
    {
        return view('livewire.how-it-works.seller-page',[
            'howItWorksSeller' => app(HowSellerSettings::class)
        ]);
    }
}

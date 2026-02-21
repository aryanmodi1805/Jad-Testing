<?php

namespace App\Livewire;
use Livewire\Component;
use App\Models\Country;

use Illuminate\Support\Facades\Redirect;




class CountrySelector extends Component
{



    public $countries;
    public $currentCountry;

    public function mount()
    {
        $this->countries = Country::where('active', 1)->get();
        $this->currentCountry= getCurrentTenant();
    }



    public function render()
    {
        return view('livewire.country-selector');
    }
}

<?php

namespace App\Livewire;

use App\Models\Service;
use Livewire\Component;

class ServicesSwiper extends Component
{
    public $services;

    public function mount(): void
    {
        $this->services = Service::mostRequested()->currentCountry()->whereNotNull("image")->ShowInHome()->get();
    }


//    public function placeholder(array $params = [])
//    {
//        return view('livewire.placeholders.services-section-skeletons', $params);
//    }


    public function render()
    {
        return view('livewire.services-swiper');
    }

}

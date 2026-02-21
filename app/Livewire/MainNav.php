<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Service;
use Livewire\Component;

class MainNav extends Component
{

    public $categories;
    public $popularServices;


     public function mount(): void
     {
         $this->categories = Category::whereNull("parent_id")->get() ;
         $this->popularServices = Service::take(8)->get();
     }
    public function render()
    {
        return view('livewire.main-nav');
    }
}

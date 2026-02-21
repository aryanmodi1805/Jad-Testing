<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Request;
use App\Models\Seller;
use Livewire\Component;

class SummerySection extends Component
{
 public $customersCount;
 public $projectsCompleted;
 public $teamsCount;


    public function mount()
    {
        $this->customersCount = Customer::count();
        $this->projectsCompleted = Request::where('status', 'completed')->count();
        $this->teamsCount = Seller::count();
    }


    public function render()
    {
        return view('livewire.summery-section');
    }
}

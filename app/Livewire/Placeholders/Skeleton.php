<?php

namespace App\Livewire\Placeholders;

use Livewire\Component;




class Skeleton extends Component
{

    public $class = '';

    public function mount( $class = null )
    {
        $this->class = $class;
    }

    public function render()
    {
        return view('livewire.placeholders.skeleton');
    }
}

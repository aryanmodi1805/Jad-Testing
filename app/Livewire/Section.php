<?php

namespace App\Livewire;

use Livewire\Component;

class Section extends Component
{

    public $hidden;
    public $title;
    public $content;
    public $image;
    public $imagePosition;

    public function mount($hidden, $title, $content, $image, $imagePosition)
    {
        $this->hidden = $hidden;
        $this->title = $title;
        $this->content = $content;
        $this->image = $image;
        $this->imagePosition = $imagePosition;

    }



    public function render()
    {
        return view('livewire.section');
    }
}

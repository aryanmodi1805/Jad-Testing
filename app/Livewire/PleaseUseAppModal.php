<?php

namespace App\Livewire;

use Livewire\Component;

class PleaseUseAppModal extends Component
{
    public bool $show = false;

    public function mount(): void
    {
        if (session('please_use_app')) {
            $this->show = true;
            session()->forget('please_use_app');
        }
    }

    public function close(): void
    {
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.please-use-app-modal');
    }
}

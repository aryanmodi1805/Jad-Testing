<?php

namespace App\Livewire;

use App\Models\Partner;
use Livewire\Component;

class PartnersSection extends Component
{
    public $partners;
    public function mount()
    {
        $this->partners = Partner::where('show_on_homepage', true)->get();
    }
    public function render()
    {
        if ($this->partners->isEmpty()) {
            return <<<'HTML'
        <div>

        </div>
        HTML;
        }
        return view('livewire.partners-section', [
            'partners' => $this->partners,
        ]);
    }
}

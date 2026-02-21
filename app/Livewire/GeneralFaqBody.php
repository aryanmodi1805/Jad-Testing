<?php

namespace App\Livewire;

use App\Enums\FAQLocationType;
use LaraZeus\Sky\SkyPlugin;
use Livewire\Component;

class GeneralFaqBody extends Component
{
    public $search;


    public function mount()
    {
        $this->search = request('search');
    }


    public function render()
    {
        return view('livewire.general-faq-body', [
            'faqs' => SkyPlugin::get()->getModel('Faq')::when($this->search != null, fn($query) => $query->where('question', 'like', '%' . $this->search . '%'))
                ->paginate(10)
        ]);
    }
}

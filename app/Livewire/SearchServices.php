<?php

namespace App\Livewire;

use App\Models\Service;
use Livewire\Component;

class SearchServices extends Component
{
    public $search = '';

    public function openWizard($serviceId)
    {

        $this->dispatch('open-wizard', serviceId: (string)$serviceId);
    }

    public function render()
    {
        $services = Service::query()
            ->where('id', $this->search ?? null)
            ->orWhere('name', 'like', "%{$this->search}%")
            ->orWhereRaw('LOWER(keywords) like ?', ["%".strtolower($this->search)."%"])
            ->get();

        return view('livewire.search-services',[
            'services' => $services
        ]);
    }
}

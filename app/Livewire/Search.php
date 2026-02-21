<?php

namespace App\Livewire;

use App\Models\Service;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Collection;
use Livewire\Component;

class Search extends Component implements HasForms
{
    use InteractsWithForms;

    public $searchTerm = '';

    public $data;

    public Collection $services;
    public $selectedServiceId;
    public $serviceName;

    public function mount($serviceId = null, $serviceName = null)
    {
        $this->selectedServiceId = $serviceId;
        $this->serviceName = $serviceName;
        $this->services = collect();
    }

    public function updatedSearchTerm()
    {
        $this->searchServices();
    }


    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        Select::make('searchTerm')
                            ->searchable()
                            ->placeholder('Search for a service'),
                    ])
                    ->statePath('data')
                    ->inlineLabel(),
            ),
        ];
    }

    public function searchServices()
    {
        $query = Service::CurrentCountry();

        if ($this->searchTerm || $this->category) {
            $query->when($this->searchTerm, function ($query) {
                $query->where('name', 'like', '%' . $this->searchTerm . '%');
            });

            $query->when($this->category, function ($query) {
                $query->whereHas('categories', function ($query) {
                    $query->where('id', $this->category);
                });
            });

            $this->services = $query->get();
        } else {
            $this->services = collect();
        }
    }

    public function selectService($serviceId)
    {
        $this->selectedServiceId = (string)$serviceId;
    }

    public function openWizard($serviceId)
    {
        $this->dispatch('open-wizard', serviceId: (string)$serviceId);
    }

//
//    public function render()
//    {
//        return view('livewire.search', [
//            'services' => $this->services,
//        ]);
//    }
}

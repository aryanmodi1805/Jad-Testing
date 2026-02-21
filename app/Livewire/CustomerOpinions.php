<?php

namespace App\Livewire;

use App\Models\Country;
use App\Models\Rating;
use Livewire\Component;

class CustomerOpinions extends Component
{
    public $siteReviews;

    public function mount()
    {
        $this->siteReviews = Rating::where('show_on_homepage', 1)
            ->where('language', app()->currentLocale())
            ->where('rateable_type', Country::class)
            ->take(13)
            ->get();
    }


    public function render()
    {

        if ($this->siteReviews->isEmpty()) {
            return <<<'HTML'
        <div>

        </div>
        HTML;
        }

        return view('livewire.customer-opinions', [
            'siteReviews' => $this->siteReviews,
        ]);
    }
}

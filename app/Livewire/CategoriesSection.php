<?php

namespace App\Livewire;

use App\Models\Category;
use Livewire\Component;

class CategoriesSection extends Component
{
    public $categories;

    public function mount(): void
    {
        $this->categories = Category::active()
            ->whereNull('parent_id')
            ->withCount('services')
            ->orderBy('services_count', 'desc')
            ->get();
    }

    public function render()
    {
        return view('livewire.categories-section', [
            'categories' => $this->categories
        ]);
    }
}

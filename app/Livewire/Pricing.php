<?php

namespace App\Livewire;

use App\Enums\FAQLocationType;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use LaraZeus\Sky\Models\Faq;
use Livewire\Component;

class Pricing extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'livewire.pricing';

    public static function getNavigationLabel(): string
    {
        return __('string.pricing');
    }

    public function getTitle(): string|Htmlable
    {
        return __('string.pricing');
    }

    public static function getRoutePath(): string
    {
        return '/seller/pricing';
    }

    public static function getRouteName(?string $panel = null): string
    {
        return 'pricing';
    }


    public $question;
    public $faqs;
    public $is_seller_longed_in =false;

    public function mount()
    {
        $this->faqs = Faq::where('location', FAQLocationType::Seller)->get();
        $this->is_seller_longed_in = auth('seller')->check();
    }


    protected static ?string $slug = 'pricing';

//    public function render()
//    {
//        return view('livewire.pricing');
//    }
}

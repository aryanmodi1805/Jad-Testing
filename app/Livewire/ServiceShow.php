<?php

namespace App\Livewire;

use App\Models\Service;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Component;

class ServiceShow extends Page implements HasInfolists
{
    use InteractsWithInfolists;

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'livewire.service-show';

    /**
     * @return string|null
     */


    public static function getNavigationLabel(): string
    {
        return __('services.services.plural');
    }

    public function getTitle(): string|Htmlable
    {
        return $this->service->name ?? __('services.services.plural');
    }

    public static function getRoutePath(): string
    {
        return '/services/{service}';
    }

    protected static ?string $slug = 'service';

    public ?Service $service;
    public $relatedServices;
    public $reviews;
    public $averageRating;
    public $totalReviews;

    public function mount(Service $service): void
    {
        $this->service = $service;

        $this->loadRelatedServices();
        $this->loadReviews();
    }

    protected function loadRelatedServices(): void
    {
        $this->relatedServices  = Service::query()->where('category_id', $this->service->category_id)
            ->where('id', '!=', $this->service->id)
            ->take(20)
            ->get();

    }

    protected function loadReviews()
    {
        $queryReviews = $this->service->ratings();
        $this->averageRating = $queryReviews->avg('rating');
        $this->totalReviews = $queryReviews->count();

        $this->reviews = $queryReviews
            ->where('language', app()->currentLocale())
            ->where('show_on_homepage', true)
            ->take(9)
            ->get();
    }

    public function placeOrder()
    {
        $this->dispatch('open-wizard', serviceId: $this->service->id);
    }
}

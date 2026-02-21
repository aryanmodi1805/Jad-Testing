<?php
namespace App\Livewire;

use App\Models\Category;
use App\Models\Service;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Component;

class CategoriesPage extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'livewire.categories-page';
    public static function getNavigationLabel(): string
    {
        return __('string.category.singular');
    }

    public function getTitle(): string|Htmlable
    {
        return __('string.category.plural');
    }

    public static function getRoutePath(): string
    {
        return '/categories/{category}';
    }

    public static function getRouteName(?string $panel = null): string
    {
        return 'categories';
    }

    protected static ?string $slug = 'categories';
    public $category;
    public $subcategories;

    public $popularServices;

    public function mount(Category $category): void
    {
        $this->category = $category;
        $this->subcategories = Category::query()->where('parent_id' , $category->id)
            ->with('services')->has('services')
            ->withCount(['requests','services'])
            ->orderBy('requests_count','desc')->get();

        $this->popularServices = Service::query()->whereIn('category_id', $this->subcategories->pluck('id'))->mostRequested(8)->get();
    }
}

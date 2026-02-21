<?php

namespace App\Livewire\Blog;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;
use LaraZeus\Sky\Models\Tag;
use LaraZeus\Sky\SkyPlugin;
use Livewire\Component;
use LaraZeus\Sky\Http\Livewire\SearchHelpers;

class BlogPage extends Page
{
    use SearchHelpers;

    public $search;
    public $searchTags = null;
    public $category;
    public $posts;
    public $pages;
    public $recent;
    /*  @var Collection tags*/

    public $tags;

    public function filterByTag($id)
    {
        $this->redirectRoute('filament.guest.pages.blog', ['searchTags' => $id]);

    }

    public function mount()
    {
        $this->search = request('search');
        $this->searchTags = request('searchTags');
        $this->category = request('category');


        $this->tags = SkyPlugin::get()->getModel('Tag')::withCount('postsPublished')
            ->where('type', 'category')
            ->get();




        seo()
            ->site(config('zeus.site_title', 'Laravel'))
            ->title(__('Posts') . ' - ' . config('zeus.site_title'))
            ->description(__('Posts') . ' - ' . config('zeus.site_description') . ' ' . config('zeus.site_title'))
            ->rawTag('favicon', '<link rel="icon" type="image/x-icon" href="' . asset('favicon/favicon.ico') . '">')
            ->rawTag('<meta name="theme-color" content="' . config('zeus.site_color') . '" />')
            ->withUrl()
            ->twitter();
    }


    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'livewire.blog.blog-page';

    public static function getNavigationLabel(): string
    {
        return __('auth.blog');
    }

    public function getTitle(): string|Htmlable
    {
        return __('auth.blog');
    }

    protected static ?string $slug = 'blog';

}

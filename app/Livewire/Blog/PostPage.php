<?php

namespace App\Livewire\Blog;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use LaraZeus\Sky\SkyPlugin;
use LaraZeus\Sky\Models\Post;
use Livewire\Component;

class PostPage extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'livewire.blog.post-page';
    public static function getNavigationLabel(): string
    {
        return __('blogs.post.singular');
    }

    public function getTitle(): string|Htmlable
    {
        return __('blogs.post.singular');
    }

    public static function getRoutePath(): string
    {
        return '/blog/post/{postSlug}';
    }

    protected static ?string $slug = 'post';

    public $postSlug;
    public Post $post;
    public $related;

    public function setSeo(): void
    {
        seo()
            ->site(config('zeus.site_title', 'Laravel'))
            ->title($this->post->title . ' - ' . config('zeus.site_title'))
            ->description(($this->post->description ?? '') . ' ' . config('zeus.site_description', 'Laravel') . ' ' . config('zeus.site_title'))
            ->rawTag('favicon', '<link rel="icon" type="image/x-icon" href="' . asset('favicon/favicon.ico') . '">')
            ->rawTag('<meta name="theme-color" content="' . config('zeus.site_color') . '" />')
            ->withUrl()
            ->twitter();

        if (! $this->post->getMedia('posts')->isEmpty()) {
            seo()->image($this->post->getFirstMediaUrl('posts'));
        }
    }

    public function mount($postSlug)
    {

        $this->postSlug = $postSlug;
        $this->post = SkyPlugin::get()->getModel('Post')::where('slug', $postSlug)->firstOrFail();
        $this->related = SkyPlugin::get()->getModel('Post')::where('tag_id',$this->post->tag?->id)
            ->where('id', '!=', $this->post->id)
            ->published()
            ->latest()
            ->limit(3)
            ->get();

//        if ($this->post->status !== 'publish' && ! $this->post->require_password) {
//            abort_if(! auth()->check(), 404);
//            abort_if($this->post->user_id !== auth()->user()->id, 401);
//        }
//
//        if ($this->post->require_password && ! session()->has($this->post->slug . '-' . $this->post->password)) {
//            return view(app('skyTheme') . '.partial.password-form')
//                ->layout(config('zeus.layout'));
//        }
        $this->setSeo();


    }


}

<?php

namespace App\Livewire\Blog;

use LaraZeus\Sky\SkyPlugin;
use Livewire\Component;

class BlogPosts extends Component
{
    public $search;
    public $searchTags;
    public $category;


    public function mount()
    {
        $this->search = request('search');
        $this->searchTags = request('searchTags');
        $this->category = request('category');
    }


    public function render()
    {
        return view('livewire.blog.blog-posts',[
            'posts' => SkyPlugin::get()->getModel('Post')::NotSticky()
                ->search($this->search)
                ->with(['tags', 'author', 'media'])
                ->published()
                ->where("is_static", false)
                ->when($this->searchTags != null , fn($query) => $query->where("tag_id", $this->searchTags))
                ->orderBy('published_at', 'desc')
                ->paginate(20)
        ]);
    }
}

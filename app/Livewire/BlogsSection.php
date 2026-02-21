<?php

namespace App\Livewire;

use LaraZeus\Sky\Models\Post;
use Livewire\Component;

class BlogsSection extends Component
{
    public $posts;

    public function mount(): void
    {
        $this->posts = Post::with('tag')->latest()->where('status', 'publish')->take(3)->get();
    }
    public function render()
    {

        if ($this->posts->isEmpty()) {
            return <<<'HTML'
        <div>

        </div>
        HTML;
        }

        return view('livewire.blogs-section',[
            'posts' => $this->posts
        ]);
    }
}

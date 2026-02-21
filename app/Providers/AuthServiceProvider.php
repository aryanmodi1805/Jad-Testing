<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Policies\FaqPolicy;
use App\Policies\PostPolicy;
use App\Policies\QueueMonitorPolicy;
use App\Policies\TagPolicy;
use App\Policies\UserViewPolicy;
use Archilex\AdvancedTables\Models\UserView;
use Croustibat\FilamentJobsMonitor\Models\QueueMonitor;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use LaraZeus\Sky\Models\Faq;
use LaraZeus\Sky\Models\Post;
use LaraZeus\Sky\Models\Tag;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //        'App\Models\Blog\Author' => 'App\Policies\Blog\AuthorPolicy',

        Post::class => PostPolicy::class,
        Tag::class => TagPolicy::class,
        Faq::class=>FaqPolicy::class,
        QueueMonitor::class=>QueueMonitorPolicy::class,
        UserView::class=>UserViewPolicy::class

    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}

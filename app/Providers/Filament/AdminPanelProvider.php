<?php

namespace App\Providers\Filament;

use App\Extensions\Login;
use App\Filament\Pages\AdminDashboard;
use App\Filament\Widgets\CountryPaymentsWidget;
use App\Filament\Widgets\PurchaseChart;
use App\Filament\Widgets\ResponseChart;
use App\Filament\Widgets\Stats;
use App\Filament\Widgets\SubscriptionsChart;
use App\Http\Middleware\UserLocalization;
use App\Http\Middleware\VerifyTenant;
use App\Models\Category;
use App\Models\Country;
use Archilex\AdvancedTables\Plugin\AdvancedTablesPlugin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Carbon\CarbonInterval;
use Croustibat\FilamentJobsMonitor\FilamentJobsMonitorPlugin;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\SpatieLaravelTranslatablePlugin;
use Filament\Support\Assets\Js;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Widgets;
use Filament\Widgets\AccountWidget;
use Hasnayeen\Themes\Http\Middleware\SetTheme;
use Hasnayeen\Themes\ThemesPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use LaraZeus\Sky\Classes\BoltParser;
use LaraZeus\Sky\Models\Faq;
use LaraZeus\Sky\Models\Post;
use LaraZeus\Sky\Models\PostStatus;
use LaraZeus\Sky\Models\Tag;
use LaraZeus\Sky\SkyPlugin;
use Swis\Filament\Backgrounds\FilamentBackgroundsPlugin;
use Swis\Filament\Backgrounds\ImageProviders\MyImages;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('cp-admin')
            ->authGuard('admin')
            ->brandLogo(fn() => app()->getLocale() == 'ar' ? getArLogo():getEnLogo())
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login(Login::class)
            ->assets([
                Js::make('notify', Vite::asset('resources/js/notification.js'))->module(),
            ])
            ->maxContentWidth(MaxWidth::Full)
            ->plugins([
                FilamentShieldPlugin::make(),


                AdvancedTablesPlugin::make(),
                FilamentJobsMonitorPlugin::make()->pluralLabel(fn()=>__('string.jobs')),
                ThemesPlugin::make(),
                SpatieLaravelTranslatablePlugin::make()
                    ->defaultLocales(['ar', 'en']),
                SkyPlugin::make()
                    ->skyPrefix('sky')
                    ->skyMiddleware(['web'])
                    ->uriPrefix([
                        'post' => 'post',
                        'page' => 'page',
                        'library' => 'library',
                        'faq' => 'faq',
                    ])

                    // enable or disable the resources
                    ->postResource()
                    ->pageResource(false)
                    ->faqResource()
                    // ->libraryResource()
                    ->navigationGroupLabel(fn () => __('string.CMS.label'))
                    ->skyModels([
                        'Faq' => Faq::class,
                        'Post' => Post::class,
                        'PostStatus' => PostStatus::class,
                        'Tag' => Tag::class,
                        'Category' => Category::class,
                    ])

                    ->parsers([BoltParser::class])
                    ->recentPostsLimit(5)
                    ->searchResultHighlightCssClass('highlight')
                    ->defaultFeaturedImage('/img/colord_logo.png')
                    ->skipHighlightingTerms(['iframe'])
                    ->libraryTypes([
                        'FILE' => 'File',
                        'IMAGE' => 'Image',
                        'VIDEO' => 'Video',
                    ])
                    ->tagTypes([
                        'tag' => 'Tag',
                        'category' => 'Category',
                        'footer' => 'Footer',
                        'faq' => 'Faq',
                    ]),
                FilamentBackgroundsPlugin::make()->imageProvider(
                    MyImages::make()
                        ->directory('images/swisnl/filament-backgrounds/triangles')
                ),
                BreezyCore::make()->myProfile(hasAvatars: true)->enableTwoFactorAuthentication(),

            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->pages([
                AdminDashboard::class,
            ])
            ->sidebarCollapsibleOnDesktop()

            ->widgets([
                ResponseChart::class,
                SubscriptionsChart::class,
                PurchaseChart::class,
                CountryPaymentsWidget::class,
            ])->font(
                'Din meduim',
                url: asset('assets/font.css'),

                provider: LocalFontProvider::class
            )
            ->middleware([
                VerifyTenant::class,
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \App\Http\Middleware\HandleLivewireJsonRedirects::class,

            ])
            ->authMiddleware([
                Authenticate::class,
            ])

            ->databaseNotifications()
            ->databaseNotificationsPolling('300s');
            
        // Only enable tenant in production
        if (!app()->environment('local', 'testing')) {
            $panel->tenant(Country::class, slugAttribute: 'slug')
                  ->tenantDomain('{tenant:slug}.'. getHost());
        }
        
        return $panel;

    }
}

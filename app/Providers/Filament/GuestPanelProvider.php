<?php

namespace App\Providers\Filament;

use App\Http\Middleware\UserLocalization;
use App\Http\Middleware\VerifyTenant;
use App\Livewire\About;
use App\Livewire\Blog\BlogPage;
use App\Livewire\Blog\PostPage;
use App\Livewire\CategoriesPage;
use App\Livewire\CustomerAgreement;
use App\Livewire\GeneralFaq;
use App\Livewire\HomePage;
use App\Livewire\HowItWorksPage;
use App\Livewire\Pricing;
use App\Livewire\PrivacyPolicy;
use App\Livewire\SellerAgreement;
use App\Livewire\ServiceShow;
use App\Models\Category;
use App\Models\Country;
use App\Models\Service;
use Cache;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use LaraZeus\Sky\SkyPlugin;

class GuestPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $isMobile = isMobile();

        $categories =
            Cache::remember('categories_data', 60 * 30,
                fn()=>
                Category::query()->select('categories.*' , 'sub_categories.requests_count')
                    ->joinSub(Category::query()->with('requests')->withCount('requests'), 'sub_categories' ,'sub_categories.parent_id' , '=' , 'categories.id' )
                    ->whereNotNull('categories.icon')
                    ->whereNull("categories.parent_id")
                    ->orderBy('sub_categories.requests_count', 'desc')
                    ->limit(5)->get());

        $categoriesSubItems = [];

        foreach ($categories as $category) {
            $categoriesSubItems[] = NavigationItem::make(fn()=>$category->name)->url('/categories/'.$category->id)
                ->icon($category->icon)
                ->group(fn()=>__('services.categories.plural'));
        }

        $categoriesNavItems = $isMobile ? [] : [
            NavigationItem::make(fn()=>__('services.categories.plural'))->icon('heroicon-o-tag')->group(fn()=>__('services.services.plural'))
                ->childItems($categoriesSubItems)
        ];

        if($isMobile){
            $categoriesNavItems = array_merge($categoriesNavItems,$categoriesSubItems);
        }

        $popularServices = Cache::remember('services_data', 60 * 30, fn()=>
                Service::where('services.active' , true)
                    ->withCount('requests')
                    ->orderBy('requests_count', 'desc')
                    ->limit(5)->get());

        $popularServicesSubItems = [];

        foreach ($popularServices as $service) {
            $popularServicesSubItems[] = NavigationItem::make(fn()=> $service->name)->url('/services/'.$service->id)
                ->group(fn()=>__('services.services.popular_services'));
        }

        $popularServicesNavItems =$isMobile ? [] : [
            NavigationItem::make(fn()=>__('services.services.popular_services'))
                ->icon('tabler-brand-linktree')
                ->group(fn()=>__('services.services.plural'))->childItems($popularServicesSubItems)
        ];

        if($isMobile){
            $popularServicesNavItems = array_merge($popularServicesNavItems,$popularServicesSubItems);
        }

        return $panel
            ->id('guest')
            ->default()
            ->darkMode(false)
            ->viteTheme("resources/css/site/theme.css",'site')
            ->discoverPages(in: app_path('Filament/Guest/Pages'), for: 'App\\Filament\\Guest\\Pages')
            ->brandLogo(fn() => app()->getLocale() == 'ar' ? getArLogo():getEnLogo())
            ->path('/')
            ->pages([
                HomePage::class,
                BlogPage::class,
                PostPage::class,
                CategoriesPage::class,
                HowItWorksPage::class,
                About::class,
                ServiceShow::class,
                Pricing::class,
                PrivacyPolicy::class,
                CustomerAgreement::class,
                SellerAgreement::class,
                GeneralFaq::class
            ])
            ->plugins([
                BreezyCore::make()->myProfile()->enableTwoFactorAuthentication(),
                SkyPlugin::make()->faqResource(false)
                    ->pageResource(false)
                    ->libraryResource(false)
                    ->postResource(false)
                    ->tagsResource(false)
            ])
            ->topNavigation()
            ->maxContentWidth(MaxWidth::Full)
            ->middleware([
                VerifyTenant::class,
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->navigationItems(array_merge($categoriesNavItems, $popularServicesNavItems,
                [
                NavigationItem::make('login_customer')->url(fn() => auth('customer')->check() ? '/customer/' : '/customer/login')
                    ->label(fn() => auth('customer')->check() ? __('auth.customer_dashboard') : __('auth.login'))->visible($isMobile),
                NavigationItem::make('login_seller')->url(fn() => auth('seller')->check() ? '/seller/' : '/seller/login')
                    ->label(fn() => auth('seller')->check() ? __('auth.switch_to_seller_dashboard') : __('auth.login_as_pro'))->visible($isMobile),
            ]
            ))
            ->broadcasting(false)
            ->renderHook(
//                PanelsRenderHook::GLOBAL_SEARCH_BEFORE, fn(): string => $isMobile? new HtmlString(getSmLogo()):"",
                PanelsRenderHook::GLOBAL_SEARCH_BEFORE, fn(): string =>   new HtmlString(getSmLogo()) ,
            )
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_BEFORE, fn (): string => Blade::render('@livewire(\'nav-action-btns\')') ,
            );


    }
}

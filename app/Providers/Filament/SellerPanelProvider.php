<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\JADRequestPasswordReset;
use App\Filament\Seller\Clusters\Settings;
use App\Filament\Seller\Pages\SellerDashboard;
use App\Filament\Seller\Pages\SellerNotificationPage;
use App\Filament\Seller\Resources\SellerBlockReportResource;
use App\Http\Middleware\CheckOtpVerification;
use App\Http\Middleware\FilamentAuthenticate;
use App\Http\Middleware\RestrictSellerWebPanel;
use App\Http\Middleware\OptionalEnsureEmailIsVerified;
use App\Http\Middleware\UserLocalization;
use App\Http\Middleware\VerifyTenant;
use App\Livewire\Auth\LoginPage;
use App\Livewire\Auth\SellerOtpLoginPage;
use App\Livewire\Auth\RegisterPage;
use App\Livewire\CustomPersonalInfo;
use App\Livewire\CustomTwoFactorPage;
use App\Livewire\SellerRegisterPage;
use App\Models\Country;
use App\Models\Seller;
use Exception;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\SpatieLaravelTranslatablePlugin;
use Filament\Support\Assets\Js;
use Filament\Support\Enums\MaxWidth;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Vite;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use LaraZeus\Sky\SkyPlugin;
use Swis\Filament\Backgrounds\FilamentBackgroundsPlugin;
use Swis\Filament\Backgrounds\ImageProviders\MyImages;

class SellerPanelProvider extends PanelProvider
{
    /**
     * @throws Exception
     */
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('seller')
            ->path('seller')
            ->authGuard('seller')
            ->brandLogo(fn() => app()->getLocale() == 'ar' ? getArLogo():getEnLogo())
            ->viteTheme('resources/css/filament/seller/theme.css')
            ->assets(app()->runningInConsole() ? [] : [
                Js::make('notify', Vite::asset('resources/js/notification.js'))->module(),
            ])
            ->darkMode(false)
            ->login(SellerOtpLoginPage::class)
            ->registration(SellerRegisterPage::class)
            ->profile(isSimple: false)
            ->emailVerification()
            ->emailVerifiedMiddlewareName(OptionalEnsureEmailIsVerified::class)
            ->plugins([
                    SpatieLaravelTranslatablePlugin::make()
                        ->defaultLocales(['ar', 'en']),
                    FilamentBackgroundsPlugin::make()->imageProvider(
                        MyImages::make()
                            ->directory('images/swisnl/filament-backgrounds/triangles')),

                    BreezyCore::make()->myProfile(hasAvatars: true)
                        ->enableTwoFactorAuthentication()
                        ->passwordUpdateRules(
                            rules: [Password::default()->mixedCase()], // you may pass an array of validation rules as well. (default = ['min:8'])
                            requiresCurrentPassword: false, // when false, the user can update their password without entering their current password. (default = true)
                        )
                        ->myProfileComponents([
                            'personal_info' => CustomPersonalInfo::class,
                            'two_factor_authentication'=>CustomTwoFactorPage::class
                        ])
                    ,

                    SkyPlugin::make()->faqResource(false)
                        ->pageResource(false)
                        ->libraryResource(false)
                        ->postResource(false)
                        ->tagsResource(false),

                    ]
            )
            ->topNavigation()
            ->discoverResources(in: app_path('Filament/Seller/Resources'), for: 'App\\Filament\\Seller\\Resources')
            ->discoverPages(in: app_path('Filament/Seller/Pages'), for: 'App\\Filament\\Seller\\Pages')
            ->discoverClusters(in: app_path('Filament/Seller/Clusters'), for: 'App\\Filament\\Seller\\Clusters')
            ->pages([
                SellerDashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Seller/Widgets'), for: 'App\\Filament\\Seller\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,

            ])
            ->maxContentWidth(MaxWidth::Full)
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
                CheckOtpVerification::class,
                RestrictSellerWebPanel::class,
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('300s')
            ->authMiddleware([
                FilamentAuthenticate::class,
            ])
            ->passwordReset(requestAction:JADRequestPasswordReset::class )
            ->authPasswordBroker('sellers')
            ->tenantMenu(false);
            
        // Only enable tenant in production
        if (!app()->environment('local', 'testing', 'staging')) {
            $panel->tenant(Country::class, slugAttribute: 'slug')
                  ->tenantDomain('{tenant:slug}.' . getHost());
        }
        
        return $panel
            ->renderHook(
                PanelsRenderHook::CONTENT_START,
                fn(): string => Blade::render('@livewire(\'email-verification-alert\')'),
            )
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_BEFORE, fn(): string => Blade::render('@livewire(\'switch-customer\')'),
            )
            ->userMenuItems([
                    'profile' => MenuItem::make()->url(fn() => '/' . $panel->getPath() . '/my-profile'),
                    MenuItem::make()
                        ->label(__('auth.home'))
                        ->url(fn(): string => url($panel->getPath()))
                        ->icon('heroicon-o-home'),
                    MenuItem::make()
                        ->label(fn(): string => __('labels.blocked_users'))
                        ->url(fn(): string => SellerBlockReportResource::getUrl(tenant: getCurrentTenant()))
                        ->icon('heroicon-o-no-symbol'),
                    MenuItem::make()
                        ->label(fn(): string => __('string.notification_settings.settings'))
                        ->url(fn(): string => SellerNotificationPage::getUrl(tenant: getCurrentTenant()))
                        ->icon('heroicon-o-bell'),
                    MenuItem::make()
                        ->label(fn(): string => __('nav.settings'))
                        ->url(fn(): string => Settings::getUrl(tenant: getCurrentTenant()))
                        ->icon('heroicon-o-cog-6-tooth'),
                ]
            );

    }
}

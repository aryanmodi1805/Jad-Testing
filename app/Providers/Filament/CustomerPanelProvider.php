<?php

namespace App\Providers\Filament;


use App\Filament\Customer\Pages\CustomerNotificationPage;
use App\Filament\Customer\Resources\CustomerBlockReportResource;
use App\Filament\Pages\Auth\JADRequestPasswordReset;
use App\Http\Middleware\CheckOtpVerification;
use App\Http\Middleware\FilamentAuthenticate;
use App\Http\Middleware\OptionalEnsureEmailIsVerified;
use App\Http\Middleware\RedirectPendingRequest;
use App\Http\Middleware\VerifyTenant;
use App\Livewire\Auth\CustomerOtpLoginPage;
use App\Livewire\Auth\RegisterPage;
use App\Livewire\CustomPersonalInfo;
use App\Livewire\CustomTwoFactorPage;
use App\Models\Country;
use Carbon\CarbonInterval;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Js;
use Filament\Support\Enums\MaxWidth;
use Filament\View\PanelsRenderHook;
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
use Jeffgreco13\FilamentBreezy\Pages\MyProfilePage;
use LaraZeus\Sky\SkyPlugin;

class CustomerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('customer')
            ->path('customer')
            ->authGuard('customer')
            ->brandLogo(fn() => app()->getLocale() == 'ar' ? getArLogo():getEnLogo())
            ->assets([
                Js::make('notify', Vite::asset('resources/js/notification.js'))->module(),
            ])
            ->viteTheme('resources/css/filament/customer/theme.css')
            ->login(CustomerOtpLoginPage::class)
            ->registration(RegisterPage::class)
            ->maxContentWidth(MaxWidth::Full)
            ->topNavigation()
            ->discoverResources(in: app_path('Filament/Customer/Resources'), for: 'App\\Filament\\Customer\\Resources')
            ->discoverPages(in: app_path('Filament/Customer/Pages'), for: 'App\\Filament\\Customer\\Pages')
            ->discoverClusters(in: app_path('Filament/Customer/Clusters'), for: 'App\\Filament\\Customer\\Clusters')
            ->profile(isSimple: false)
            ->userMenuItems([
                MenuItem::make()
                    ->label(fn(): string => __('labels.blocked_users'))
                    ->url(fn(): string => CustomerBlockReportResource::getUrl(tenant: getCurrentTenant()))
                    ->icon('heroicon-o-no-symbol'),

                MenuItem::make()
                    ->label(fn(): string => __('string.notification_settings.settings'))
                    ->url(fn(): string => CustomerNotificationPage::getUrl(tenant: getCurrentTenant()))
                    ->icon('heroicon-o-bell'),
            ])
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_BEFORE, fn(): string => Blade::render('@livewire(\'switch-seller\')'),
            )
            ->discoverWidgets(in: app_path('Filament/Customer/Widgets'), for: 'App\\Filament\\Customer\\Widgets')
            ->plugin(
                BreezyCore::make()->myProfile(hasAvatars: true)
                    ->enableTwoFactorAuthentication()
                    ->passwordUpdateRules(
                        rules: [Password::default()->mixedCase()->uncompromised(3)], // you may pass an array of validation rules as well. (default = ['min:8'])
                        requiresCurrentPassword: false, // when false, the user can update their password without entering their current password. (default = true)
                    )
                    ->myProfileComponents([
                        'personal_info' => CustomPersonalInfo::class,
                        'two_factor_authentication'=>CustomTwoFactorPage::class
                    ])
                    ->customMyProfilePage(MyProfilePage::class),

            )->plugin(SkyPlugin::make()->faqResource(false)
                ->pageResource(false)
                ->libraryResource(false)
                ->postResource(false)
                ->tagsResource(false))

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
                RedirectPendingRequest::class
            ])
            ->authMiddleware([
                FilamentAuthenticate::class,
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('300s')
            ->passwordReset(requestAction:JADRequestPasswordReset::class )
            ->authPasswordBroker('customers')
            ->broadcasting()
            ->tenantMenu(false);
            
        // Only enable tenant in production
        if (!app()->environment('local', 'testing', 'staging')) {
            $panel->tenant(Country::class, slugAttribute: 'slug')
                  ->tenantDomain('{tenant:slug}.' . getHost());
        }
        
        return $panel
            ->renderHook(
                PanelsRenderHook::CONTENT_START, fn(): string => Blade::render('@livewire(\'email-verification-alert\')'),
            )
            ->emailVerification()
            ->emailVerifiedMiddlewareName(OptionalEnsureEmailIsVerified::class);
    }
}

<?php

namespace App\Providers;

use App\Aliases\JadLogoutController;
use App\Extensions\DatabaseSessionHandler;
use App\Extensions\JADRestPassword;
use App\Extensions\JADVerifyEmail;
use App\Filament\Components\Geocomplete;
use App\Http\Middleware\Authenticate;
use App\Models\Seller;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Http\Controllers\Auth\LogoutController;
use Filament\Notifications\Auth\ResetPassword;
use Filament\Notifications\Auth\VerifyEmail;
use Filament\Resources\Resource;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Guava\FilamentIconPicker\Forms\IconPicker;
use HusamTariq\FilamentTimePicker\Forms\Components\TimePickerField;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Traits\Localizable;
use League\Flysystem\UnableToCheckFileExistence;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;
use Mokhosh\FilamentRating\Columns\RatingColumn;
use Mokhosh\FilamentRating\Components\Rating;
use Mokhosh\FilamentRating\RatingTheme;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Spatie\MediaLibrary\Support\ImageFactory;

class AppServiceProvider extends ServiceProvider
{
    use Localizable;

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Authenticate::redirectUsing(
            fn(): string => Filament::getLoginUrl() ?? Filament::getPanel('customer')->getLoginUrl()
        );
        AuthenticateSession::redirectUsing(
            fn(): string => Filament::getLoginUrl() ?? Filament::getPanel('customer')->getLoginUrl()
        );
        AuthenticationException::redirectUsing(
            fn(): string => Filament::getLoginUrl() ?? Filament::getPanel('customer')->getLoginUrl()
        );
        if (!app()->runningInConsole()) {
            FilamentAsset::register([
                Js::make('local-scripts', Vite::asset('resources/js/app.js'))->module(),
                Css::make('local-styles', Vite::asset('resources/css/local.css')),
                AlpineComponent::make('lightbox', __DIR__ . '/../../resources/js/dist/components/lightbox.js'),
                Js::make('mobile-nav', __DIR__ . '/../../resources/js/mobile-nav.js'),
            ]);
        }


        FilamentColor::register([
            'primary' => [
                50 => '227, 232, 241',
                100 => '148, 165, 200',
                200 => '76, 100, 157',
                300 => '53, 79, 143',
                400 => '32, 58, 128',
                500 => '12, 35, 113',
                600 => '9, 29, 97',
                700 => '6, 23, 82',
                800 => '4, 17, 67',
                900 => '3, 12, 53',
                950 => '3, 12, 53',
            ],
            'secondary' => [
                50 => '231, 237, 249',
                100 => '184, 201, 237',
                200 => '139, 165, 223',
                300 => '96, 128, 208',
                400 => '56, 91, 191',
                500 => '37, 71, 182',
                600 => '31, 61, 158',
                700 => '25, 50, 134',
                800 => '13, 31, 89',
                900 => '4, 13, 48',
                950 => '4, 13, 48',
            ],
        ]);

        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_START,
            fn(): string => <<<HTML
                    <style>
                        [x-cloak] { display: none !important; }
                    </style>
                    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
                    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
                    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
                    <link rel="manifest" href="/site.webmanifest">
            HTML
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_START,
            fn(): string => <<<HTML
                    <!-- Google tag (gtag.js) -->
                    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-16913803520"></script>
                    <script>
                      window.dataLayer = window.dataLayer || [];
                      function gtag(){dataLayer.push(arguments);}
                      gtag('js', new Date());

                      gtag('config', 'AW-16913803520');
                    </script>
            HTML
        );


        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn(): string => <<<HTML
                    <!-- Meta Pixel Code -->
                    <script>
                    !function(f,b,e,v,n,t,s)
                    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
                    n.queue=[];t=b.createElement(e);t.async=!0;
                    t.src=v;s=b.getElementsByTagName(e)[0];
                    s.parentNode.insertBefore(t,s)}(window,document,'script',
                    'https://connect.facebook.net/en_US/fbevents.js');
                     fbq('init', '649977187651056');
                    fbq('track', 'PageView');
                    </script>
                    <noscript>
                     <img height="1" width="1"
                    src="https://www.facebook.com/tr?id=649977187651056&ev=PageView
                    &noscript=1"/>
                    </noscript>
                    <!-- End Meta Pixel Code -->
            HTML

        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn(): string => <<<HTML
                <!-- TikTok Pixel Code Start -->
                    <script>
                        !function (w, d, t) {
                          w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie","holdConsent","revokeConsent","grantConsent"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(
                        var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var r="https://analytics.tiktok.com/i18n/pixel/events.js",o=n&&n.partner;ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=r,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};n=document.createElement("script")
                        ;n.type="text/javascript",n.async=!0,n.src=r+"?sdkid="+e+"&lib="+t;e=document.getElementsByTagName("script")[0];e.parentNode.insertBefore(n,e)};


                         ttq.load('D08URLBC77UDFM0G01MG');
                         ttq.page();
                        }(window, document, 'ttq');
                    </script>
                  <!-- TikTok Pixel Code End -->
            HTML

        );


        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn(): string => <<<HTML

                    <script type="text/javascript">
                        (function(c,l,a,r,i,t,y){
                            c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
                            t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
                            y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
                        })(window, document, "clarity", "script", "sh8pclwb65");
                    </script>
            HTML

        );


        FilamentView::registerRenderHook(
            PanelsRenderHook::FOOTER, fn(): string => Blade::render('@livewire(\'front-wizard\')'),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_START, fn(): string => view('components.full-page-load'));

        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_START, fn(): string => <<<HTML
    <div class="fixed bottom-3 end-3 md:bottom-8 md:end-8 z-10 w-24 h-24 hover:w-28 hover:h-28 transition-all duration-300 ease-in-out">
        <a href="https://wa.me/966567819162?text=%D8%A7%D9%84%D8%B3%D9%84%D8%A7%D9%85%20%D8%B9%D9%84%D9%8A%D9%83%D9%85%20%D9%88%D8%B1%D8%AD%D9%85%D8%A9%20%D8%A7%D9%84%D9%84%D9%87"
           target="_blank"
           >

            <img src="https://aiba.dev/wp-content/uploads/2025/03/whatsapp.webp" alt="WhatsApp">
        </a>
    </div>
HTML
        );

        TextColumn::macro("useFullName", function () {
            return $this->formatStateUsing(fn($record) => $record->full_name);
        });

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->visible(outsidePanels: true)
                ->flagsOnly(false)
                ->labels([
                    'en' => 'English',
                    'ar' => 'العربية',
                ])
                ->userPreferredLocale(fn() => Filament::auth()->user()->locale ?? 'ar')
                ->locales(['ar', 'en']); // also accepts a closure
        });


        Resource::scopeToTenant(false);

        /*Geocomplete::macro("allTypes",function (){
            return $this->types(['secondary_school','shoe_store','shopping_mall','spa','stadium','storage','store','subway_station','supermarket','synagogue','taxi_stand','tourist_attraction','train_station','transit_station','travel_agency','university','veterinary_care','zoo']);
        });*/

        Select::configureUsing(function (Select $item) {
            if (!$item instanceof IconPicker) {
                $item->getOptionLabelFromRecordUsing(fn($record, $component) => $record?->{$component->getRelationshipTitleAttribute()} ?? "");
            }
        });


        Field::macro('translatable', function () {
            return $this->hint(fn() => __("string.translatable"))
                ->hintIcon('heroicon-m-language');
        });

        TextInput::macro('currency', function () {
            return $this->suffix(getCurrencySample());
        });

        TextInput::macro('hasUrl', function () {
            return $this->helperText(__('string.url_hint'))
                ->url();
        });

        Field::macro('stepComponentJsValidation', function () {
            return $this->extraAttributes(function ($component) {
                $element = instanceOfAny($component, [TextInput::class, Textarea::class, Select::class, Geocomplete::class, DatePicker::class, TimePickerField::class, DateRangePicker::class]) ? "\$el" : "\$el.closest('[data-field-wrapper]')";
                return [
                    "x-on:validation-error.window" => <<<JS
                        if (\$event.detail.field !== '{$component->getStatePath()}')
                        {return}
                        \$nextTick(() => {
                            {$element}.classList.add('!ring-danger-600', 'dark:!ring-danger-500','!ring-2','!ring-offset-2','!rounded-md');
                        })
                    JS,
                    "x-on:validated.window" => <<<JS
                        \$nextTick(() => {
                            {$element}.classList.remove('!ring-danger-600', 'dark:!ring-danger-500','!ring-2','!ring-offset-2','!rounded-md' );
                        })
                    JS,
                ];
            }, true);
        });

        Field::macro('ltrDirection', function () {
            /** @return TextInput| Textarea
             * @var TextInput| Textarea $this
             */
            return $this->extraInputAttributes([
                'style' => 'direction: ltr'
            ], true);
        });

        Field::macro('rtlDirection', function () {
            /** @return TextInput| Textarea
             * @var TextInput| Textarea $this
             */
            return $this->extraInputAttributes([
                'style' => 'direction: rtl'
            ], true);
        });

        // Also register on TextInput and Textarea for compatibility
        TextInput::macro('ltrDirection', function () {
            return $this->extraInputAttributes([
                'style' => 'direction: ltr'
            ], true);
        });

        TextInput::macro('rtlDirection', function () {
            return $this->extraInputAttributes([
                'style' => 'direction: rtl'
            ], true);
        });

        Textarea::macro('ltrDirection', function () {
            return $this->extraInputAttributes([
                'style' => 'direction: ltr'
            ], true);
        });

        Textarea::macro('rtlDirection', function () {
            return $this->extraInputAttributes([
                'style' => 'direction: rtl'
            ], true);
        });

        Table::configureUsing(function (Table $table): void {
            $table
                ->deferLoading();
        });

        RatingColumn::configureUsing(function (RatingColumn $ratingColumn): void {
            $ratingColumn
                ->size('md')
                ->color('primary')->theme(RatingTheme::HalfStars);
        });

        Rating::configureUsing(function (Rating $rating): void {
            $rating
                ->size('md')
                ->color('primary')->theme(RatingTheme::HalfStars)
                ->dehydrateStateUsing(fn($state) => intval($state));
        });

        DatePicker::configureUsing(function (DatePicker $component): void {
            $component
                ->native(false);
        });

        SpatieMediaLibraryFileUpload::configureUsing(function (SpatieMediaLibraryFileUpload $component): void {
            $component
                ->imageResizeTargetHeight(800)
                ->imageResizeMode('contain')
                ->downloadable()
                ->openable()
                ->imageResizeTargetWidth(800);
        });

        SpatieMediaLibraryFileUpload::macro('withImageDimensions', function () {
            return $this->saveUploadedFileUsing(static function (SpatieMediaLibraryFileUpload $component, TemporaryUploadedFile $file, ?Model $record): ?string {
                if (!method_exists($record, 'addMediaFromString')) {
                    return $file;
                }

                try {
                    if (!$file->exists()) {
                        return null;
                    }
                } catch (UnableToCheckFileExistence $exception) {
                    return null;
                }

                /** @var FileAdder $mediaAdder */
                $mediaAdder = $record->addMediaFromString($file->get());

                $filename = $component->getUploadedFileNameForStorage($file);

                $ImageFactory = ImageFactory::load($file->getPathname());
                $width = $ImageFactory->getWidth();
                $height = $ImageFactory->getHeight();

                $media = $mediaAdder
                    ->addCustomHeaders($component->getCustomHeaders())
                    ->usingFileName($filename)
                    ->usingName($component->getMediaName($file) ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                    ->storingConversionsOnDisk($component->getConversionsDisk() ?? '')
                    ->withCustomProperties([
                        'width' => $width,
                        'height' => $height,
                    ])
                    ->withManipulations($component->getManipulations())
                    ->withResponsiveImagesIf($component->hasResponsiveImages())
                    ->withProperties($component->getProperties())
                    ->toMediaCollection($component->getCollection() ?? 'default', $component->getDiskName());

                return $media->getAttributeValue('uuid');
            });
        });

        FileUpload::configureUsing(function (FileUpload $component): void {
            $component
                ->downloadable()
                ->openable();
        });

        Collection::macro('recursive', function () {
            return $this->map(function ($value) {
                if (is_array($value) || is_object($value)) {
                    return collect($value)->recursive();
                }

                return $value;
            });
        });
        Section::macro('withoutBorder', function () {
            return $this->extraAttributes([
                'style' => '--tw-ring-color:#ff000000; box-shadow: 0 0 #0000;',
            ], true);
        });

        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return $this->withLocale($notifiable->locale, function () use ($url, $notifiable) {
                $greeting = ($notifiable instanceof Seller) ? __('string.verify_email_greeting_seller', locale: $notifiable->locale) : __('string.verify_email_greeting', locale: $notifiable->locale);

                return (new MailMessage)
                    ->subject(Lang::get('Verify Email Address', locale: $notifiable->locale))
                    ->greeting($greeting)
                    ->theme($notifiable->locale === 'ar' ? 'rtl' : 'ltr')
                    ->line(Lang::get('Please click the button below to verify your email address.', locale: $notifiable->locale))
                    ->action(Lang::get('Verify Email Address', locale: $notifiable->locale), $url)
                    ->line(Lang::get('If you did not create an account, no further action is required.', locale: $notifiable->locale))
                    ->line(Lang::get('string.mail_support', locale: $notifiable->locale))
                    ->line(Lang::get('string.verify_mail_thanks', locale: $notifiable->locale));
            });

        });
        ResetPassword::toMailUsing(function (object $notifiable, string $url) {

            return $this->withLocale(app()->getLocale(), function () use ($url, $notifiable) {
//                if (!\Str::isUrl($url)) {
//                    $url= Filament::getResetPasswordUrl($url, $notifiable);
//                }

                return (new MailMessage)
                    ->subject(Lang::get('Reset Password Notification'))
                    ->theme(app()->getLocale() === 'ar' ? 'rtl' : 'ltr')
                    ->line(Lang::get('We received a request to reset the password for your account. If you didn’t make this request, please ignore this email.'))
                    ->line(Lang::get('To reset your password, please click the link below:'))
                    ->action(Lang::get('Reset Password'), $url)
                    ->line(Lang::get('This password reset link will expire in :count minutes.', ['count' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire')]))
                    ->line(Lang::get('If you did not request a password reset, no further action is required.'));
            });

        });

        /*   if($this->app->environment('production')) {
               \URL::forceScheme('https');
           }*/


    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $loader = AliasLoader::getInstance();
        $loader->alias(LogoutController::class, JadLogoutController::class);
        $loader->alias(VerifyEmail::class, JADVerifyEmail::class);
        $loader->alias(ResetPassword::class, JADRestPassword::class);
        Session::extend('FilamentDBSession', function ($app) {

            $table = config('session.table');
            $minutes = config('session.lifetime');

            return new DatabaseSessionHandler($this->getDatabaseConnection(), $table, $minutes, $app);
        });
    }

    protected function getDatabaseConnection(): Connection
    {
        $connection = config('session.connection');

        return DB::connection($connection);
    }
}

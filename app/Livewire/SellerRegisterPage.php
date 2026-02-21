<?php

namespace App\Livewire;

use App\Filament\Components\Map;
use App\Models\SellerService;
use App\Models\SellerServiceLocation;
use App\Models\Service;
use App\Rules\LatLngInCountry;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Contracts\HasActions;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register;
use Filament\Pages\Concerns\CanUseDatabaseTransactions;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

/**
 * @property Form $form
 */
class SellerRegisterPage extends Register implements HasForms, HasActions
{
    use CanUseDatabaseTransactions;
    use InteractsWithForms;
    use InteractsWithFormActions;

    protected static string $view = 'livewire.seller-register-page';
    protected static string $layout = 'components.layouts.auth';

    public string $page_title = "";
    public string $page_sub_title = "";
    public Collection $selected_services;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    protected string $userModel;

    public function mount(): void
    {
        $this->selected_services = collect();

        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
        }

        $this->callHook('beforeFill');

        $this->form->fill();

        $this->callHook('afterFill');
    }

    public function form(Form $form): Form
    {
        return $form->schema(
            [
                Wizard::make($this->getSteps())
                    ->startOnStep(1)
                    ->cancelAction($this->getCancelFormAction())
                    ->submitAction($this->getSubmitFormAction())
                    ->skippable(false)
                    ->extraAttributes([
                        'class' => '  !shadow-none  !border-0  !bg-transparent  p-0 divide-y-0 !rounded-none sex !ring-0',
                        'style' => '--tw-ring-color: transparent;--tw-shadow: 0 0 #0000;--tw-shadow-colored: 0 0 #0000;box-shadow:var(--tw-ring-offset-shadow, 0 0 #0000),var(--tw-ring-shadow, 0 0 #0000),var(--tw-shadow)'])
                    ->previousAction(fn($action) => $action->label(__('nav.back'))->extraAttributes([
                        'class' => 'mt-2 rounded-md  px-8 py-4 font-semibold leading-5 bg-secondary-500'
                    ]))
                    ->nextAction(fn($action) => $action->label(__('nav.next'))->extraAttributes([
                        'class' => 'mt-2 rounded-md  px-8 py-4 font-semibold leading-5 bg-secondary-500'
                    ])),
            ])
            ->statePath('data');

    }

    protected function getSteps(): array
    {
        $services_q = Service::where('services.active', true)->withCount('requests')->orderBy('requests_count', 'desc');
        $services = $services_q->pluck('name', 'id');
        $popularServices = $services_q->take(6)->pluck('name', 'id');
        return [
            Step::make('')
                ->id('select-services-you-provide')
                ->schema([
                    Placeholder::make('t1')
                        ->hiddenLabel()
                        ->columnSpanFull()
                        ->content(fn() => new HtmlString('<h4 class="text-2xl mt-3 font-bold text-center">' . $this->getPageTitle() . '</h4>
                     <h6 class="text-gray-500 mt-2 font-bold text-center ">' . $this->getPageSubTitle() . '</h6>')),

                    Select::make('service_ids')
                        ->searchDebounce(100)
                        ->multiple()
                        ->extraAttributes([
                            'class' => 'p-2'
                        ])
                        ->validationMessages([
                            'required' => __('auth.create_seller.required_field')
                        ])
                        ->label(__('auth.create_seller.What services do you provide?'))
                        ->required(fn($get) => empty($get('popular_service_ids')))
                        ->searchable()->placeholder(__('labels.search_for_services'))
                        ->options($services),
                    ToggleButtons::make('popular_service_ids')
                        ->multiple()
                        ->validationAttribute(__('services.services.popular_services'))
                        ->label(__('services.services.popular_services'))
                        ->columns(2)
                        ->options($popularServices)
                        ->inlineLabel(false)
                ])
                ->afterValidation(function($get) {
                    $service_ids = $get('service_ids') ?? [];
                    $popular_service_ids = $get('popular_service_ids') ?? [];
                    $this->selected_services = Service::whereIn('id', array_merge($service_ids, $popular_service_ids))->get();
                }),

            Step::make('')
                ->id('Where-would-you-like-to-see-leads-from')
                ->statePath('location_details')
                ->schema([
                    Placeholder::make('t2')
                        ->hiddenLabel()
                        ->columnSpanFull()
                        ->content(fn() => new HtmlString('<h4 class="text-2xl mt-3 font-bold text-center">' .
                            __('auth.create_seller.Where would you like to see leads from?') . '</h4>
                             <h6 class="text-gray-500 mt-2 font-bold text-center ">' .
                            __('auth.create_seller.Tell us the area you cover so we can show you leads for your location') . '</h6>')),

                    Toggle::make('is_nationwide')->label(__('auth.create_seller.I serve customers nationwide'))->live()
                        ->visible(fn($get) => $this->selected_services->contains(fn($service) => $service->is_nationwide)),
                    Fieldset::make(__('auth.create_seller.I serve customers within'))
                        ->hidden(fn($get) => $get('is_nationwide'))
                        ->schema([
                            Select::make('location_range')
                                ->columnSpanFull()
                                ->default(5)
                                ->options(__('string.distance_ranges'))
                                ->requiredIf('is_nationwide', false)
                                ->dehydratedWhenHidden()
                                ->label(__('columns.select_range')),


                            TextInput::make('location_name')
                                ->required()
                                ->columnSpanFull()
                                ->rule(fn($get) => new LatLngInCountry($get('location.lat'), $get('location.lng')))
                                ->label(__('string.wizard.descriptive_location'))
                                ->prefixIcon('tabler-map-pin')
                                ->placeholder(__('services.requests.start_type')),

                            Map::make('location')
                                ->hiddenLabel()
                                ->height(fn() => '100px')
                                ->columnSpanFull()
                                ->label(__('string.default_location'))
                                ->columnSpanFull()
                                ->autocomplete(
                                    fieldName: 'location_details.location_name',
                                    placeField: 'name',
                                    countries: fn($record) => [getTenant()?->code ?? 'sa']
                                )
                                ->draggable()
                                ->clickable()
                                ->autocompleteReverse(true)
                                ->geolocate()
                                ->geolocateLabel(__('string.current_location'))
                                ->defaultZoom(6)
                                ->geolocateOnLoad()
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    $set('latitude', $state['lat']);
                                    $set('longitude', $state['lng']);
                                }),

                            Hidden::make('latitude')
                                ->required(),

                            Hidden::make('longitude')
                                ->required(),
                        ])


                ]),
            Step::make('')
                ->id('identification-document')
                ->statePath('identification_details')
                ->schema([
                    Placeholder::make('t3')
                        ->hiddenLabel()
                        ->columnSpanFull()
                        ->content(fn() => new HtmlString('<h4 class="text-2xl mt-3 font-bold text-center">' . 
                            __('auth.create_seller.Identity Verification') . '</h4>
                             <h6 class="text-gray-500 mt-2 font-bold text-center ">' . 
                            __('auth.create_seller.Upload your identification document for verification') . '</h6>')),
                    
                    FileUpload::make('identification_document')
                        ->label(__('auth.create_seller.Identification Document'))
                        ->acceptedFileTypes(['image/*', 'application/pdf'])
                        ->maxSize(5120) // 5MB
                        ->required()
                        ->disk('public')
                        ->directory('seller-documents')
                        ->helperText(__('auth.create_seller.document_upload_helper'))
                        ->validationMessages([
                            'required' => __('auth.create_seller.identification_document_required'),
                            'max' => __('auth.create_seller.file_too_large'),
                        ]),
                ]),
            Step::make('')
                ->statePath('seller_details')
                ->schema([
                    Placeholder::make('t3')
                        ->id('Some-details-about-you')
                        ->hiddenLabel()
                        ->columnSpanFull()
                        ->content(fn($get) => new HtmlString('<h4 class="text-2xl mt-3 font-bold text-center">' .
                            __('auth.create_seller.Some details about you') . '</h4>
                     <h6 class="text-gray-500 mt-2 font-bold text-center ">' .
                            __('auth.create_seller.You’re just a few steps away from viewing our service leads', ['service_name' => $this->selected_services->pluck('name')->join(', ')]) . '</h6>')),
                    $this->getNameFormComponent(),
                    TextInput::make('company_name')->label(__('cv.company_name'))->nullable()->maxLength(40)->extraInputAttributes(['tabindex' => 4, 'class' => 'block w-full py-4 pr-20  placeholder:text-gray-400 sm:text-sm sm:leading-6']),
                    getPhoneInput('phone', $this->getUserModel())->extraInputAttributes(['tabindex' => 1, 'class' => 'block w-full py-4 pr-20  placeholder:text-gray-400 sm:text-sm sm:leading-6']),
                    $this->getEmailFormComponent()->extraInputAttributes(['tabindex' => 1, 'class' => 'block w-full py-4 pr-20  placeholder:text-gray-400 sm:text-sm sm:leading-6']),
                    $this->getPasswordFormComponent()->extraInputAttributes(['tabindex' => 3, 'class' => 'block w-full py-4 pr-20  placeholder:text-gray-400 sm:text-sm sm:leading-6']),
                    $this->getPasswordConfirmationFormComponent()->extraInputAttributes(['tabindex' => 3, 'class' => 'block w-full py-4 pr-20  placeholder:text-gray-400 sm:text-sm sm:leading-6']),
                    Checkbox::make('terms_of_service')
                        ->accepted()
                        ->label(function ($get) {
                            $url = filament()->getCurrentPanel()->getPath() == "seller" ? SellerAgreement::getSlug() : CustomerAgreement::getSlug();
                            $terms = '<a href="' . url($url) . '" target="_blank" class="text-indigo-500 hover:underline ">' . __('nav.terms-conditions') . '</a>';
                            $policy = '<a href="' . url('/privacy-policy') . '" target="_blank" class="text-indigo-500  hover:underline "  >' . __('nav.privacy-policy') . '</a>';

                            return new HtmlString(__('string.I agree to the terms of service', ['terms' => $terms, 'policy' => $policy]));
                        })->validationAttribute(__('nav.terms-conditions'))
                    ,
                ]),
        ];
    }

    protected function getNameFormComponent(): Component
    {
        return parent::getNameFormComponent()
            ->extraInputAttributes(['tabindex' => 1, 'class' => 'block w-full py-4 pr-20  placeholder:text-gray-400 sm:text-sm sm:leading-6'])
            ->maxLength(30);
    }

    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()
            ->required(false)
            ->nullable()
            ->dehydrateStateUsing(fn ($state) => filled($state) ? $state : null)
            ->helperText(__('auth.email_optional_helper'));
    }

    public function getPageTitle(): string
    {
        return __('auth.create_seller.Secure jobs and grow your business');
    }

    public function getPageSubTitle(): string
    {
        return __('auth.create_seller.1000’s of local and remote clients are already waiting for your services');
    }

    public function getCancelFormAction(): null|string
    {
        return null;
    }

    public function getSubmitFormAction(): Action
    {
        return
            Action::make('register')
                ->label(__('filament-panels::pages/auth/register.form.actions.register.label'))
                ->extraAttributes([
                    'class' => 'mt-2 rounded-md bg-primary-500 px-8 py-4 font-semibold leading-5 bg-secondary-500'
                ])->submit('register');
    }

    public function getScope()
    {
        return filament()->getCurrentPanel()->getId() == 'seller' ? 'seller' : 'customer';
    }

    public function getTitle(): string|Htmlable
    {
        return parent::getTitle() . "-" . __('auth.register_as_pro');
    }

    /**
     * @return array<Action | ActionGroup>
     */
    public function getFormActions(): array
    {
        return [];
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function handleRegistration(array $data): Model
    {
        return $this->customRegister($data);
    }

    public function customRegister(): Model
    {
        $data = $this->form->getState();
        $data = $this->mutateFormDataBeforeRegister($data);

        $service_ids = array_merge(
            $data['service_ids'] ?? [],
            $data['popular_service_ids'] ?? []
        );

        if (empty($service_ids)) {
            throw new \Exception(__('auth.create_seller.please_select_at_least_one_service'));
        }

        $seller_data = $data['seller_details'] ?? [];
        $seller_data['country_id'] = $data['country_id'];
        
        // Handle identification document
        if (isset($data['identification_details']['identification_document'])) {
            $seller_data['identification_document_url'] = $data['identification_details']['identification_document'];
            $seller_data['identification_document_status'] = 'pending';
        }
        
        $location_details = $data['location_details'] ?? [];

        $seller = $this->getUserModel()::create($seller_data);

        // Create location
        $location_details['seller_id'] = $seller->id;
        $location_details['country_id'] = $seller->country_id;
        $location_details['name'] = $location_details['location_name'] ?? "location";
        $location = $seller->location()->create($location_details);

        // Create services and link them to the location
        foreach ($service_ids as $service_id) {
            $sellerService = SellerService::create([
                'seller_id' => $seller->id,
                'service_id' => $service_id
            ]);

            SellerServiceLocation::create([
                'seller_service_id' => $sellerService->id,
                'seller_location_id' => $location->id,
                "location_range" => $location_details['location_range'],
                "is_nationwide" => $location_details['is_nationwide'] ? 1 : 0,
            ]);
        }

        return $seller;
    }

    protected function mutateFormDataBeforeRegister(array $data): array
    {
        $tenant = getCurrentTenant();
        $data['country_id'] = $tenant?->id ?? \App\Models\Country::first()?->id ?? 1;
        return $data;
    }
}

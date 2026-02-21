<?php

namespace App\Filament\Seller\Clusters\Settings\Pages;

use App\Filament\Actions\PageSellerProfileAction;
use App\Filament\Seller\Clusters\Settings;
use App\Forms\Components\TranslatableGrid;
use App\Models\CompanySize;
use App\Models\Seller;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Alignment;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;


class CompanyProfile extends Page implements Forms\Contracts\HasForms
{

    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $slug = 'company-profile';
    protected static string $view = 'filament.seller.clusters.settings.pages.company-profile';
    protected static bool $isDiscovered = true;
    protected static string $model = Seller::class;
    protected static ?string $cluster = Settings::class;

    public ?array $data = [];

    public $user;

    public $userClass;

    public bool $hasAvatars;


    public static $sort = 10;

    public function getTitle(): string
    {
        return __('seller.company.profile');
    }

    public function getHeading(): string
    {
        return __('seller.company.profile');
    }

    public static function getNavigationLabel(): string
    {
        return __('seller.company.profile');

    }

    public function getSubheading(): ?string
    {
        return __('seller.company.text') ?? null;
    }

    public function mount()
    {
        $this->user = Filament::getCurrentPanel()->auth()->user();
        $this->hasAvatars = filament('filament-breezy')->hasAvatars();

        $this->form->model($this->user);
        $this->form->fill($this->user->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getProfileFormSchema())->columns(3)
            ->statePath('data');
    }

    protected function getProfileFormSchema(): array
    {
        $current_tenant = getSubdomain() ?? 'sa' ;

        $groupFields = Forms\Components\Section::make(__('seller.company.profile'))
            ->description(__('seller.company.text'))
            ->columns(5)
            ->footerActions([
                Forms\Components\Actions\Action::make('save')
                    ->submit('submit')->label(__('filament-breezy::default.profile.personal_info.submit.label'))
                ,

            ])->footerActionsAlignment(Alignment::End)
            ->schema([Forms\Components\Group::make()
                ->schema(
                    [
                        Forms\Components\FileUpload::make('cover_image')
                            ->label(__('seller.company.cover_image'))
                            ->disk('public')
                            ->directory('company_images')
                            ->inlineLabel(false)
                            ->imageCropAspectRatio('95:20')
                            ->imageEditor(),
                    ]
                )->columnSpan(5),

                Forms\Components\Group::make()
                    ->schema(($this->hasAvatars) ? [filament('filament-breezy')->getAvatarUploadComponent()] : [])->columnSpan(1),

                Forms\Components\Group::make()
                    ->columns(2)
                    ->schema([
                        TranslatableGrid::make()->nameTextInput('company_name',)
                            ->required()
                            ->label(__('seller.company.company_name')),


                        TranslatableGrid::make()->textArea('company_description')
                            ->required()
                            ->label(__('seller.company.company_description')),

                        Forms\Components\TextInput::make('years_in_business')
                            ->numeric()
                            ->nullable()
                            ->label(__('seller.company.years_in_business')),

                        Forms\Components\TextInput::make('website')
                            ->nullable()
                            ->ltrDirection()
                            ->hasUrl()
                            ->extraInputAttributes([
                                'style' => 'direction: ltr'
                            ])
                            ->suffixIcon('heroicon-m-globe-alt')
                            ->label(__('seller.company.website')),
                        Forms\Components\Select::make('company_size_id')
                            ->options(CompanySize::where('active', 1)->pluck('name', 'id'))
                            ->label(__('seller.company.company_size_id'))
                            ->nullable(),

                    ])->columnSpan(5)
            ])->columnSpan(4);

        return [$groupFields];
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $this->user->update($data);
        $this->sendNotification();
    }

    protected function sendNotification(): void
    {
        Notification::make()
            ->success()
            ->title(__('filament-breezy::default.profile.personal_info.notify'))
            ->send();
    }


    public function getRegisteredMyProfileComponents(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            PageSellerProfileAction::make()->record(Filament::auth()->user()),
        ];
    }
}

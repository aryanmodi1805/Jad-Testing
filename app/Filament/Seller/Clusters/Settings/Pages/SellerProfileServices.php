<?php

namespace App\Filament\Seller\Clusters\Settings\Pages;

use App\Filament\Actions\PageSellerProfileAction;
use App\Filament\Seller\Clusters\Settings;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Alignment;
use Guava\FilamentIconPicker\Forms\IconPicker;

class SellerProfileServices extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;


    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static string $view = 'filament.seller.clusters.settings.pages.seller-profile-services';

    protected static ?string $cluster = Settings::class;

    public ?array $data = [];

    public $user;

    public $userClass;


    public array $only = ['seller_id', 'service_description', 'service_title', 'media'];

    public function getTitle(): string
    {
        return __('seller.seller_profile_services.nav');
    }

    public function getHeading(): string
    {
        return __('seller.seller_profile_services.nav');
    }

    public static function getNavigationLabel(): string
    {
        return __('seller.seller_profile_services.nav');

    }

    public function getSubheading(): ?string
    {
        return __('seller.seller_profile_services.text') ?? null;
    }


    public function mount()
    {
        $this->user = Filament::getCurrentPanel()->auth()->user();
        $this->userClass = get_class($this->user);
        $this->form->fill($this->user->only($this->only));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getProfileFormSchema())
            ->statePath('data');
    }

    protected function getProfileFormSchema(): array
    {
        $groupFields = Forms\Components\Section::make(__('seller.seller_profile_services.nav'))
            ->description(__('seller.seller_profile_services.text'))
            ->model($this->user)
            ->columnSpanFull()
            ->footerActions([
                Forms\Components\Actions\Action::make('submit')
                    ->submit('submit')->label(__('filament-breezy::default.profile.personal_info.submit.label')),

            ])->footerActionsAlignment(Alignment::End)
            ->schema([

                Forms\Components\Repeater::make('SellerProfileServices')
                    ->relationship('sellerProfileServices')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->itemLabel(fn (array $state): ?string => ($state['service_title']['ar'] ?? '') . ' - ' . ($state['service_title']['en'] ?? ''))
                    ->addActionLabel(__('string.add_services'))
                    ->schema([

                        Forms\Components\TextInput::make('service_title.ar')->label(__('seller.seller_profile_services.service_title_ar'))->required()->rtlDirection(),
                        Forms\Components\TextInput::make('service_title.en')->label(__('seller.seller_profile_services.service_title'))->required()->ltrDirection(),
                        Forms\Components\TextInput::make('service_description.ar')->label(__('seller.seller_profile_services.service_description_ar'))->rtlDirection(),
                        Forms\Components\TextInput::make('service_description.en')->label(__('seller.seller_profile_services.service_description'))->ltrDirection(),

                    ])
                ->collapsible()
                ,


            ])->columnSpan(3);

        return [$groupFields];
    }

    public function submit(): void
    {
        $data = collect($this->form->getState())->only($this->only)->all();
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

    protected function getHeaderActions(): array
    {
        return [
            PageSellerProfileAction::make()->record(Filament::auth()->user()),
        ];
    }

}

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

class SocialMedia extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.seller.clusters.settings.pages.social-media';

    protected static ?string $cluster = Settings::class;

    public ?array $data = [];

    public $user;

    public $userClass;


    public array $only = ['seller_id', 'platform', 'link', 'icon', 'active'];
    public function getTitle(): string
    {
        return __('seller.social_media.nav');
    }

    public function getHeading(): string
    {
        return __('seller.social_media.nav');
    }

    public static function getNavigationLabel(): string
    {
        return __('seller.social_media.nav');

    }

    public function getSubheading(): ?string
    {
        return __('seller.social_media.text') ?? null;
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
        $groupFields = Forms\Components\Section::make(__('seller.social_media.nav'))
            ->description(__('seller.social_media.text'))
            ->model($this->user)
            ->columnSpanFull()
            ->footerActions([
                Forms\Components\Actions\Action::make('submit')
                    ->submit('submit')->label(__('filament-breezy::default.profile.personal_info.submit.label')),

            ])->footerActionsAlignment(Alignment::End)
            ->schema([

                Forms\Components\Repeater::make('socialMedia')
                    ->relationship('socialMedia')
                    ->label(__('string.add_social_media'))
                    ->hiddenLabel()
                    ->itemLabel(fn($state) => $state['platform'] ?? '')
                    ->columnSpanFull()
                    ->collapsible()
                ->schema([

                    Forms\Components\TextInput::make('platform')->label(__('seller.social_media.platform')) ->inlineLabel()->required()->ltrDirection(),
                    Forms\Components\TextInput::make('link')->label(__('seller.social_media.link'))
                        ->inlineLabel()->ltrDirection()->hasUrl(),
                    Forms\Components\Toggle::make('active')->label(__('string.active')),

                ])
                ,


            ]);

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

<?php

namespace App\Filament\Seller\Clusters\Settings\Pages;

use App\Filament\Actions\PageSellerProfileAction;
use App\Filament\Seller\Clusters\Settings;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Model;
use League\Flysystem\UnableToCheckFileExistence;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Spatie\MediaLibrary\Support\ImageFactory;

class SellerImagePage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static string $view = 'filament.seller.clusters.settings.pages.seller-image-page';

    protected static ?string $cluster = Settings::class;


    public ?array $data = [];

    public $user;

    public $userClass;


    public array $only = ['seller_id', 'question', 'answer'];

    public function getTitle(): string
    {
        return __('string.Photos');
    }

    public function getHeading(): string
    {
        return __('string.Photos');
    }

    public static function getNavigationLabel(): string
    {
        return __('string.Photos');

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
        $groupFields = Forms\Components\Section::make(__('string.Photos'))
            ->collapsible()
            ->model($this->user)
            ->columnSpanFull()
            ->footerActions([
                Forms\Components\Actions\Action::make('submit')
                    ->submit('submit')->label(__('filament-breezy::default.profile.personal_info.submit.label')),

            ])
            ->footerActionsAlignment(Alignment::End)
            ->schema([
                Forms\Components\SpatieMediaLibraryFileUpload::make('media')
                    ->hiddenLabel()
                    ->multiple()
                    ->columns(1)
                    ->columnSpanFull()
                    ->responsiveImages()
                    ->reorderable()
                    ->moveFiles()
                    ->imageResizeTargetHeight(null)
                    ->imageResizeMode(null)
                    ->imageResizeTargetWidth(null)
                    ->panelLayout('grid')
                    ->loadingIndicatorPosition('left')
                    ->removeUploadedFileButtonPosition('right')
                    ->uploadButtonPosition('left')
                    ->uploadProgressIndicatorPosition('left')
                    ->collection('images')
                    ->imagePreviewHeight('196')
                    ->previewable()
                    ->withImageDimensions()
                    ->image()
                    ->imageEditor()
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

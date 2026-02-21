<?php

namespace App\Filament\Seller\Clusters\Settings\Resources;

use App\Filament\Seller\Clusters\Settings;
use App\Filament\Seller\Clusters\Settings\Resources\ProjectResource\Pages;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use League\Flysystem\UnableToCheckFileExistence;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Spatie\MediaLibrary\Support\ImageFactory;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Settings::class;
    protected static bool $shouldSkipAuthorization = true;

    public function getTitle(): string
    {
        return __('seller.projects.plural');
    }

    public function getHeading(): string
    {
        return __('seller.projects.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('seller.projects.plural');

    }
    public function getSubheading(): ?string
    {
        return __('seller.projects.plural') ?? null;
    }

    public static function getModelLabel(): string
    {
        return __('seller.projects.single');
    }

    public static function getPluralLabel(): ?string
    {
        return __('seller.projects.plural');
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('seller.projects.plural'))
                    ->description(__('seller.projects.description'))
                    ->columns(2)
                    ->schema([
                        Forms\Components\Hidden::make('seller_id')
                            ->dehydrateStateUsing(fn() => auth('seller')->id()),
                        TextInput::make('title.ar')
                            ->label(__('seller.projects.title_ar'))
                            ->rtlDirection()
                            ->required(),
                        TextInput::make('title.en')
                            ->label(__('seller.projects.title_en'))
                            ->ltrDirection()
                            ->required(),

                        SpatieMediaLibraryFileUpload::make('media_main')
                            ->label(__('seller.projects.media_main'))
                            ->responsiveImages()
                            ->image()
                            ->collection('projects.main')
                            ->imageEditor()
                            ->imageResizeTargetHeight(null)
                            ->imageResizeMode(null)
                            ->imageResizeTargetWidth(null)
                            ->imagePreviewHeight('196')
                            ->imageCropAspectRatio('24:18')
                            ->withImageDimensions()
                            ->columnSpan(2)
                            ->required(),
                        SpatieMediaLibraryFileUpload::make('media_more')
                            ->label(__('seller.projects.media_more'))
                            ->responsiveImages()
                            ->previewable()
                            ->image()
                            ->imageResizeTargetHeight(null)
                            ->imageResizeMode(null)
                            ->imageResizeTargetWidth(null)
                            ->collection('projects.more')
                            ->withImageDimensions()
                            ->imageEditor()
                            ->multiple()
                            ->columnSpan(2),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->label(__('seller.projects.title')),


            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('seller_id', auth('seller')->id());
    }
}

<?php

namespace LaraZeus\Sky\Filament\Resources;

use App\Livewire\Blog\PostPage;
use App\Providers\Filament\GuestPanelProvider;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Columns\SpatieTagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use LaraZeus\Sky\Filament\Resources\PostResource\Pages;
use LaraZeus\Sky\Models\Post;
use LaraZeus\Sky\SkyPlugin;
use Wallo\FilamentSelectify\Components\ButtonGroup;

// @mixin Builder<PostScope>
class PostResource extends SkyResource
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 1;

    public static function getModel(): string
    {
        return SkyPlugin::get()->getModel('Post');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make(__('string.post.content'))->schema([
                TextInput::make('title')
                    ->label(__('string.post.post_title'))
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, $state, $context) {
                        if ($context === 'edit') {
                            return;
                        }
                        $set('slug', Str::slug($state));
                    }),
                SkyPlugin::get()->getEditor()::component()
                    ->label(__('string.post.body')),

                Select::make('tag_id')
                    ->label(__('string.tag.single'))

                    ->relationship('tag', 'name')
                    ->required(),

                SpatieMediaLibraryFileUpload::make('featured_image')
                    ->collection('posts')
                    ->disk(SkyPlugin::get()->getUploadDisk())
                    ->directory(SkyPlugin::get()->getUploadDirectory())
                    ->label(__('string.post.featured_image')),
            ]),

            Section::make('SEO')->schema([
                Hidden::make('user_id')
                    ->default(auth()->user()->id)
                    ->required(),

                Hidden::make('post_type')
                    ->label(__('string.post.type'))
                    ->default('post')
                    ->required(),

                Textarea::make('description')
                    ->maxLength(255)
                    ->label(__('string.desc'))
                    ->hint(__('string.post.desc_hint')),

                TextInput::make('slug')
                    ->unique(ignorable: fn (?Post $record): ?Post => $record)
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn ($record) => $record && $record->is_static)
                    ->label(__('string.post.slug')),
            ]),



            Section::make(__('string.post.visibility'))->schema([
                Select::make('status')
                    ->label(__('string.post.status'))
                    ->default('publish')
                    ->required()
                    ->options(postStatus()->pluck('label', 'name')),

                DateTimePicker::make('published_at')
                    ->label(__('string.post.published_at'))
                    ->native(false)
                    ->default(now()),

            ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ViewColumn::make('title_card')
                    ->label(__('string.post.post_title'))

                    ->sortable(['title'])
                    ->searchable(['title'])
                    ->toggleable()
                    ->view('zeus::filament.columns.post-title'),

                ViewColumn::make('status_desc')
                    ->label(__('string.post.status'))
                    ->sortable(['status'])
                    ->searchable(['status'])
                    ->toggleable()
                    ->view('zeus::filament.columns.status-desc')
                    ->tooltip(fn (Post $record): string => $record->published_at->format('Y/m/d | H:i A')),

                TextColumn::make('tag.name')
                    ->label(__('string.tag.single'))

                    ->sortable(['tag_id'])
                    ->searchable(['tag_id'])
                    ->toggleable(),


            ])
            ->defaultSort('id', 'desc')
            ->actions([
                ActionGroup::make([
                    EditAction::make('edit'),
                    Action::make('Open')

                        ->color('warning')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->label(__('string.open'))
                        ->url(fn (Post $record): string => PostPage::getUrl([
                            'postSlug' => $record->slug,
                        ], panel: 'guest'))
                        ->openUrlInNewTab(),
                    DeleteAction::make('delete')->disabled(fn ($record) => $record && $record->is_static),
                    ForceDeleteAction::make()->disabled(fn ($record) => $record && $record->is_static),
                    RestoreAction::make(),
                ]),
            ])
            ->bulkActions([
                DeleteBulkAction::make()->action(function ($action): void {
                    $action->process(static fn ($records) => $records->each(fn ($record) => $record->is_static ? null : $record->delete()));

                    $action->success();
                }),
                ForceDeleteBulkAction::make(),
                RestoreBulkAction::make(),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('status')
                    ->multiple()
                    ->label(__('string.post.status'))
                    ->options(postStatus()->pluck('label', 'name')),

//                Filter::make('password')
//                    ->label(__('Password Protected'))
//                    ->query(fn (Builder $query): Builder => $query->whereNotNull('password')),
//
//                Filter::make('sticky')
//                    ->label(__('Still Sticky'))
//                    // @phpstan-ignore-next-line
//                    ->query(fn (Builder $query): Builder => $query->sticky()),
//
//                Filter::make('not_sticky')
//                    ->label(__('Not Sticky'))
//                    ->query(
//                        fn (Builder $query): Builder => $query
//                            ->whereDate('sticky_until', '<=', now())
//                            ->orWhereNull('sticky_until')
//                    ),
//
//                Filter::make('sticky_only')
//                    ->label(__('Sticky Only'))
//                    ->query(
//                        fn (Builder $query): Builder => $query
//                            ->where('post_type', 'post')
//                            ->whereNotNull('sticky_until')
//                    ),

                SelectFilter::make('tags')
                    ->multiple()->preload()
                    ->relationship('tags', 'name')
                    ->label(__('string.tag.single')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): string
    {
        return __('string.post.single');
    }

    public static function getPluralLabel(): string
    {
        return __('string.post.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('string.post.plural');
    }

    public static function getPluralModelLabel(): string
    {
        return __('string.post.plural');
    }


    /**
     * @return Builder<Post>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('post_type', 'post')
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

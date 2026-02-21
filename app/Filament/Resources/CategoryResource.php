<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Filament\Resources\CategoryResource\Widgets\TreeCategoryWidget;
use App\Forms\Components\TranslatableGrid;
use App\Models\Category;
use App\Models\Scopes\CategoriesActiveScope;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Table;
use Guava\FilamentIconPicker\Forms\IconPicker;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;


class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static bool $isScopedToTenant = false;
    protected static ?int $navigationSort = -20;

    public static function getNavigationGroup(): ?string
    {
        return __('nav.services');
    }

    public static function getNavigationLabel(): string
    {
        return __('services.categories.plural');
    }

    public static function getModelLabel(): string
    {
        return __('services.categories.single');
    }

    public function getTitle(): string|Htmlable
    {
        return __('services.categories.single');
    }

    public static function getPluralLabel(): ?string
    {
        return __('services.categories.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TranslatableGrid::make()->textInput('name')->label( __('string.name'))->required(),

                IconPicker::make('icon')
                    ->required()->optionsLimit(5)
                    ->placeholder(__('string.icon_hint'))
                    ->label(__('string.icon'))
                    ->sets(['tabler']),

                Forms\Components\Select::make('parent_id')
                    ->label(__('services.categories.parent_category'))
                    ->relationship('root', 'name', function (Builder $query, callable $get) {
                        return $query->whereNull('parent_id');
                    })
                    ->searchable()
                    ->preload()
                    ->optionsLimit(10)
                    ->nullable()
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->full_name),

                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->imageCropAspectRatio('30:27')
                    ->imageEditor()
                    ->imageResizeMode('cover')
                    ->imageResizeTargetWidth('300')
                    ->imageResizeTargetHeight('270')
                    ->label(__('string.image'))
                    ->previewable()
                    ->columnSpanFull()
                    ->downloadable()
                    ->directory('category_images'),


                Forms\Components\Toggle::make('active')
                    ->label(__('string.active'))
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->useFullName()
                    ->label(__('services.categories.name'))
                    ->width('200px')
                    ->searchable(),
                Tables\Columns\TextColumn::make('root.full_name')
                    ->width('200px')
                    ->label(__('services.categories.parent_category')),

                Tables\Columns\IconColumn::make('active')
                    ->boolean()
                    ->width('100px')
                    ->label(__('columns.active')),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('columns.created_at')),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('columns.updated_at')),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('columns.deleted_at')),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\TernaryFilter::make('active')
                    ->label(__('columns.active'))
                    ->default(true)
                    ->options([
                        true => __('columns.active'),
                        false => __('columns.inactive'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                DeleteAction::make()->before(
                    fn(Category $record) => $record->children->map(fn(Category $child) => $child->fill(['parent_id' => null])->save())
                ),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkActionGroup::make([
                        DeleteBulkAction::make(),
                        RestoreBulkAction::make(),
                        ForceDeleteBulkAction::make(),
                        ]),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [TreeCategoryWidget::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),

        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }
}

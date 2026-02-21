<?php

namespace LaraZeus\Sky\Filament\Resources;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use LaraZeus\Sky\Filament\Resources\TagResource\Pages;
use LaraZeus\Sky\SkyPlugin;

class TagResource extends SkyResource
{
    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?int $navigationSort = 5;

    public static function getModel(): string
    {
        return SkyPlugin::get()->getModel('Tag');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label(__('string.name'))
                    ->live(onBlur: true)->columnSpanFull()
                    ->afterStateUpdated(function (Set $set, $state) {
                        $set('slug', Str::slug($state));
                    }),
                Hidden::make('slug')
                    ->unique(ignorable: fn(?Model $record): ?Model => $record)
                    ->required(),

                Hidden::make('type')->dehydrateStateUsing(fn() => 'category'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('string.name'))
                    ->toggleable()->searchable()->sortable(),
                TextColumn::make('posts_count')
                    ->label(__('string.tag.posts_count'))
                    ->toggleable()

            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make('edit'),
                    DeleteAction::make('delete'),
                ]),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTags::route('/'),
//            'create' => Pages\CreateTag::route('/create'),
//            'edit' => Pages\EditTag::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): string
    {
        return __('string.tag.single');
    }

    public static function getPluralLabel(): string
    {
        return __('string.tag.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('string.tag.title');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount([
            'posts'
        ]);
    }
}

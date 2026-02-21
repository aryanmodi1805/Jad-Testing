<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartnerResource\Pages;
use App\Forms\Components\Translatable;
use App\Forms\Components\TranslatableGrid;
use App\Models\Partner;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PartnerResource extends Resource
{
    protected static ?string $model = Partner::class;

    protected static ?string $slug = 'partners';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationGroup(): ?string
    {
        return __('nav.settings');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TranslatableGrid::make()->textInput()
                    ->required()
                    ->label(__('columns.name')),

                TextInput::make('email')
                    ->nullable()
                    ->label(__('columns.email')),

                TextInput::make('phone')
                    ->nullable()
                    ->label(__('columns.phone')),

                TextInput::make('address')
                    ->nullable()
                    ->label(__('columns.address')),

                FileUpload::make('image')
                    ->image()
                    ->directory('partners_images')
                    ->required()
                    ->rules('mimes:jpeg,jpg,png,webp')
                    ->label(__('columns.image'))
                    ,

                Toggle::make('show_on_homepage')
                    ->label(__('columns.show_on_homepage'))
                    ->helperText(__('string.show_on_homepage_helper')),

                Toggle::make('active')
                    ->default(1)
                    ->label(__('columns.active')),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label(__('columns.name')),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->label(__('columns.email')),

                TextColumn::make('phone')
                    ->label(__('columns.phone')),

                TextColumn::make('address')
                    ->label(__('columns.address')),

                ToggleColumn::make('show_on_homepage')
                    ->label(__('columns.show_on_homepage'))
                    ->sortable(),

                ImageColumn::make('image')
                    ->label(__('columns.image')),

                TextColumn::make('active')
                    ->label(__('columns.active')),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make()
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make()
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPartners::route('/'),
            'create' => Pages\CreatePartner::route('/create'),
            'edit' => Pages\EditPartner::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationLabel(): string
    {
        return __('localize.partners.plural');
    }

    public static function getModelLabel(): string
    {
        return __('localize.partners.single');
    }

    public function getTitle(): string
    {
        return __('localize.partners.single');
    }

    public static function getPluralLabel(): ?string
    {
        return __('localize.partners.plural');
    }
}

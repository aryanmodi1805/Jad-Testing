<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EstimateBaseResource\Pages;
use App\Forms\Components\Translatable;
use App\Forms\Components\TranslatableGrid;
use App\Models\EstimateBase;
use Filament\Forms\Components\Checkbox;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class EstimateBaseResource extends Resource
{
    protected static ?string $model = EstimateBase::class;

    protected static ?string $slug = 'estimate-bases';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static function getNavigationGroup(): ?string
    {
        return __('nav.settings');
    }
    public static function getNavigationLabel(): string
    {
        return __('services.estimate_bases.plural');
    }

    public static function getModelLabel(): string
    {
        return __('services.estimate_bases.single');
    }

    public function getTitle(): string|Htmlable
    {
        return __('services.estimate_bases.single');
    }

    public static function getPluralLabel(): ?string
    {
        return __('services.estimate_bases.plural');
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TranslatableGrid::make()->textInput()
                    ->required()
                    ->label(__('columns.name')),


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

                ToggleColumn::make('active')
                    ->label(__('columns.active')),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEstimateBases::route('/'),
            'create' => Pages\CreateEstimateBase::route('/create'),
            'edit' => Pages\EditEstimateBase::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [];
    }
}

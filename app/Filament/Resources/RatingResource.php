<?php

namespace App\Filament\Resources;

use App\Enums\RateableType;
use App\Enums\RaterType;
use App\Filament\Resources\RatingResource\Pages;
use App\Models\Country;
use App\Models\Rating;
use App\Models\Request;
use App\Models\Response;
use App\Models\Scopes\ApprovedScope;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Mokhosh\FilamentRating\Columns\RatingColumn;

class RatingResource extends Resource
{
    protected static ?string $model = Rating::class;

    protected static ?string $slug = 'ratings';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationGroup(): ?string
    {
        return __('nav.groups.reports');
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Rating Details')
                    ->schema([
                        TextInput::make('rating')
                            ->required()
                            ->numeric()
                            ->label(__('columns.rating')),
                        TextInput::make('review')
                            ->label(__('columns.review')),
                        TextInput::make('rateable_id')
                            ->required()
                            ->label(__('columns.rateable_id')),
                        TextInput::make('rateable_type')
                            ->required()
                            ->label(__('columns.rateable_type')),
                        TextInput::make('rater_id')
                            ->required()
                            ->label(__('columns.rater_id')),
                        TextInput::make('rater_type')
                            ->required()
                            ->label(__('columns.rater_type')),
                        Checkbox::make('approved')
                            ->label(__('columns.approved')),
                    ]),
                Section::make('Metadata')
                    ->schema([
                    ]),
                TextInput::make('response_id')
                    ->label(__('columns.response_id')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label(__('columns.id')),
                TextColumn::make('rater.name')->label(__('columns.name')),
                TextColumn::make('rater_type_label')->label(__('columns.rater_type')),
                TextColumn::make('review')->label(__('columns.review'))->wrap(),
                RatingColumn::make('rating')->label(__('seller.rate.ratings')),
                TextColumn::make('rateable_type_label')
                    ->label(__('columns.rateable_type'))
                    ->url(function ($record) {
                        if ($record->rateable_type == 'App\Models\Response') {
                            $requestId = Response::find($record->rateable_id)?->request_id;
                            return $requestId ? RequestResource::getUrl('view', ['record' => $requestId]) :"#";
                        }
                        return null;
                    })
                    ->openUrlInNewTab(),
                TextColumn::make('rated_item')->getStateUsing(function ($record) {
                    if ($record->rateable_type == Country::class) {
                        return $record->rateable->name;
                    } elseif ($record->rateable_type == Request::class) {
                        return $record->customer?->name;

                    } elseif ($record->rateable_type == Response::class) {
                        return $record->seller?->name;
                    }
                    return null;
                })->label(__('columns.rateable')),
                TextColumn::make('created_at')
                    ->label(__('columns.created_date'))
                    ->dateTime(),
                ToggleColumn::make('approved')
                    ->label(__('columns.approved'))
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('rateable_type')
                    ->label(__('columns.rateable_type'))
                    ->options(RateableType::class),

                SelectFilter::make('rater_type')
                    ->label(__('columns.rater_type'))
                    ->options(RaterType::class),
                Filter::make('show_on_homepage')
                    ->label(__('columns.show_on_homepage'))
                    ->query(fn($query)=>$query->where( 'show_on_homepage', true))
                    ->toggle(),

            ])
            ->actions([
                Action::make('toggleShowOnHomepage')
                    ->label(fn($record) => $record->show_on_homepage ? __('columns.hide_from_homepage') : __('columns.show_on_homepage'))
                    ->hidden(fn($record) => $record->rateable_type !== RateableType::Country->value)
                    ->action(function (Rating $record) {
                        $record->update(['show_on_homepage' => !$record->show_on_homepage]);
                    })
                    ->icon(fn($record) => $record->show_on_homepage ? 'heroicon-o-eye' : 'heroicon-o-eye-slash')
                    ->color(fn($record) => $record->show_on_homepage ? 'success' : 'danger')
//                    ->visible(fn($record) => $record->rateable_type === RateableType::Response->value)
                ,

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
            'index' => Pages\ListRatings::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
                ApprovedScope::class
            ])->with([
                'rateable',
                'rater',
                'customer',
                'seller'

            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [];
    }

    public static function getNavigationLabel(): string
    {
        return __('localize.ratings.plural');
    }

    public static function getModelLabel(): string
    {
        return __('localize.ratings.single');
    }

    public static function getPluralLabel(): ?string
    {
        return __('localize.ratings.plural');
    }

    public function getTitle(): string
    {
        return __('localize.ratings.single');
    }
}

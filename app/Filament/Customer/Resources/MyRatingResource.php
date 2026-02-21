<?php

namespace App\Filament\Customer\Resources;

use App\Filament\Resources\RequestResource;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Rating;
use App\Models\Request;
use App\Models\Response;
use App\Models\Seller;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Mokhosh\FilamentRating\Columns\RatingColumn;

class MyRatingResource extends Resource
{
    protected static bool $shouldSkipAuthorization = true;

    protected static ?string $model = Rating::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationLabel(): string
    {
        return __('seller.rate.my_ratings_and_reviews');
    }

    public static function getPluralLabel(): ?string
    {
        return  __('seller.rate.my_ratings_and_reviews');
    }

    public static function getModelLabel(): string
    {
        return __('columns.rating');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                \Mokhosh\FilamentRating\Components\Rating::make('rating')
                    ->formatStateUsing(fn($record, $state): float => $state)
                    ->label(__('seller.rate.single'))
                ->columnSpanFull(),
                Textarea::make('review')->label(__('seller.rate.review'))->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('review')->label(__('columns.review'))->wrap(),
                RatingColumn::make('rating')->label(__('columns.rating'))
                    ->color('primary')->width(150),

//                TextColumn::make('rateable_type_label')
//                    ->label(__('columns.rateable_type'))
//                    ->width(200)
//                    ->url(function ($record) {
//                        if ($record->rateable_type == Response::class) {
//                            $requestId = Response::find($record->rateable_id)->request_id;
//                            return \App\Filament\Customer\Resources\RequestResource::getUrl('view', ['record' => $requestId]);
//                        }
//                        return null;
//                    })
//                    ->openUrlInNewTab(),
                TextColumn::make('rated_item')->getStateUsing(function ($record){
                    if($record->rateable_type == Country::class){
                        return $record->rateable->name;
                    }elseif($record->rateable_type == Request::class){
                        return $record->customer?->name;

                    }elseif ($record->rateable_type == Response::class){
                        return $record->seller?->name;
                    }
                    return null;
                })->label(__('columns.rateable'))
                ->width(200),

                TextColumn::make('created_at')
                    ->label(__('columns.created_date'))
                    ->sortable()
                    ->width(300)
                    ->dateTime(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }


    public static function getPages(): array
    {
        return [
            'index' => MyRatingResource\Pages\ManageRatings::route('/'),

        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])->with([
                'rateable',
                'rater',
                'customer',
                'seller'

            ])
            ->where('rater_id', Filament::auth()->id())
            ->where('rater_type', Customer::class)->latest();
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\Country;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Mokhosh\FilamentRating\Columns\RatingColumn;

class CountryPaymentsWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 4;

    protected static bool $isDiscovered = false;
    protected static ?string $maxHeight = '250px';
    protected static ?string $pollingInterval = '350s';
    protected int|string|array $columnSpan = '2';


    /**
     * @return string|null
     */
    public static function getHeading(): ?string
    {
        return __('wallet.country_statistics');
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(fn() => __('wallet.country_statistics'))
            ->paginated([4,8,16,25,50,100])
            ->defaultPaginationPageOption(4)
            ->query(
                fn() => Country::query()->withoutGlobalScopes()
                    ->withSum([
                    'purchases as cash_purchases' => fn($query) => $query->active()->where('is_form_wallet', 0),
                    'purchases as wallet_purchases' => fn($query) => $query->active()->where('is_form_wallet', 1),

                ], 'amount')
                    ->withAvg(['ratings as rates_avg' => fn($query) => $query->where('approved', true)], 'rating')
                ->orderBy('active','desc')
            )
            ->columns([

                Tables\Columns\TextColumn::make('name')->label(__('columns.country_name')),
                Tables\Columns\TextColumn::make('cash_purchases')->label(__('wallet.country_Payments'))->suffix(fn($record) => $record->currency?->symbol ?? ''),
                Tables\Columns\TextColumn::make('wallet_purchases')->label(__('wallet.wallet_payments'))->suffix(__('wallet.credits')),
                RatingColumn::make('rates_avg')->label(__('columns.rating'))->size('sm')
                    ->tooltip(fn($record) => number_format(($record->rates_avg ?? 0), 1) . '/5'),
            ])
            ->filters([
                Filter::make('active')
                    ->query(fn( $query) => $query->where('active', true))
                    ->default(true)
                    ->label(__('columns.active'))
                    ->toggle()
            ]);
    }
}

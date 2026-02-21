<?php

namespace App\Filament\Seller\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables;
use Filament\Tables\Table;
use Livewire\Attributes\On;

#[On('refreshWallet')]
class WalletBalanceStateTable extends BaseWidget
{
    protected static bool $isDiscovered = false;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $pollingInterval = null;

    /**
     * @return string|null
     */
    public static function getHeading(): ?string
    {
        return __('wallet.balance_state');
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('wallet.balance_state');
    }

    public function getTableRecordTitle(Model $record): ?string
    {
        return __('wallet.balance_state');
    }
    public function table(Table $table): Table
    {
        return $table
            ->striped()
            ->defaultSort('created_at','desc')
            ->query(
            fn()=> auth('seller')->user()->balanceStates()
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label(__('string.date'))->dateTime(),
                Tables\Columns\TextColumn::make('payable.name')->label(__('wallet.payable')) ,
                Tables\Columns\TextColumn::make('tx.processor_id')->label(__('wallet.processor_id'))->badge()
                    ->formatStateUsing(fn($state)=> __('wallet.'.$state))
                    ->color(fn (string $state): string => match ($state) {
                        'charge' => 'warning',
                        'deposit' => 'success',
                        'withdraw' => 'danger',
                        'transfer' => 'info',
                        default=> 'gray'
                    }),
                Tables\Columns\TextColumn::make('value')->label(__('wallet.my_balance')),

                Tables\Columns\TextColumn::make('tx.amount')->label(__('wallet.credits')),
                Tables\Columns\TextColumn::make('tx.commission')->label(__('wallet.commission')) ,
//                Tables\Columns\TextColumn::make('tx.received')->label(__('wallet.received')) ,
                Tables\Columns\TextColumn::make('tx.currency')
                    ->label(__('wallet.currency'))
                    ->formatStateUsing(fn ($state) => strtoupper($state) === 'CREDIT' ? __('wallet.credits') : $state),
                Tables\Columns\TextColumn::make('tx.status')->label(__('wallet.status'))->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'success' => 'success',
                        'canceled' => 'danger',
                        'failed' => 'red',
                        default=> 'gray'
                    }),
                Tables\Columns\TextColumn::make('tx.from.name')->label(__('wallet.from')),
                Tables\Columns\TextColumn::make('tx.to.name')->label(__('wallet.to')),

                Tables\Columns\TextColumn::make('tx.meta')->label(__('wallet.meta'))
                ->getStateUsing(fn($record)=> ($record->tx?->meta ?? [])['data'] ?? ($record->tx?->meta ?? [])['description'] ?? " " ),
//                Tables\Columns\TextColumn::make('tx.archived')->label(__('wallet.archived')),



            ]);
    }
}

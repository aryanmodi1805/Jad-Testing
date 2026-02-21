<?php

namespace App\Filament\Wallet\Actions;

use App\Models\Seller;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use O21\LaravelWallet\Models\Transaction;

class AdminTransactionAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'admin_transaction';
    }

    public static function make(?string $name = null): static
    {
        return parent::make($name ?? static::getDefaultName());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('wallet.create_transaction'))
            ->icon('heroicon-o-banknotes')
            ->color('success')
            ->modalHeading(__('wallet.create_transaction'))
            ->modalDescription(__('wallet.admin_transaction'))
            ->modalWidth('lg')
            ->form([


                Select::make('user_id')
                    ->label(__('wallet.select_user'))
                    ->required()
                    ->searchable()
                    ->getSearchResultsUsing(function (string $search, callable $get) {

                            $users = Seller::where(function($query) use ($search) {
                                $query->where('name', 'like', "%{$search}%")
                                      ->orWhere('email', 'like', "%{$search}%")
                                      ->orWhere('phone', 'like', "%{$search}%");
                            })->limit(50)->get();

                            return $users->mapWithKeys(function ($user) {
                                $balance = method_exists($user, 'balance') ? $user->balance()->value : 0;
                                return [$user->id => $user->name . ' (' . $user->email . ') [' . $user->phone . '] - Balance: ' . $balance . ' Credits'];
                            })->toArray();

                    })
                    ->getOptionLabelUsing(function ($value, callable $get) {


                            $user = Seller::find($value);


                        if ($user) {
                            $balance = method_exists($user, 'balance') ? $user->balance()->value : 0;
                            return $user->name . ' (' . $user->email . ') [' . $user->phone . '] - Balance: ' . $balance . ' Credits';
                        }

                        return '';
                    })
                    ->reactive(),

                Radio::make('transaction_type')
                    ->label(__('wallet.transaction_type'))
                    ->options([
                        'charge' => __('wallet.charge'),
                        'withdraw' => __('wallet.withdraw'),
                        'deposit' => __('wallet.deposit'),
                    ])
                    ->required()
                    ->inline()
                    ->reactive(),

                TextInput::make('amount')
                    ->label(__('wallet.amount'))
                    ->numeric()
                    ->minValue(0.01)
                    ->step(0.01)
                    ->required()
                    ->suffix('Credits'),

                Textarea::make('reason')
                    ->label(__('wallet.reason'))
                    ->placeholder(__('wallet.admin_adjustment'))
                    ->maxLength(255)
                    ->required(),
            ])
            ->action(function (array $data): void {

                $userId = $data['user_id'];
                $transactionType = $data['transaction_type'];
                $amount = $data['amount'];
                $reason = $data['reason'];

                try {

                        $user = Seller::findOrFail($userId);


                    // Check if user implements wallet functionality
                    if (!method_exists($user, 'balance')) {
                        throw new \Exception('Selected user does not support wallet functionality');
                    }

                    // Create the transaction based on type
                    $transaction = null;

                    switch ($transactionType) {
                        case 'charge':
                        case 'deposit':
                            // Add credits to user using the same pattern as existing code
                            $transaction = charge($amount)
                                ->to($user)
                                ->overCharge()
                                ->meta([
                                    'data' => $reason . ' - ' . __('wallet.admin_adjustment'),
                                    'admin_user' => auth()->user()->name,
                                    'admin_transaction' => true,
                                ])
                                ->commit();
                            break;

                        case 'withdraw':
                            // Deduct credits from user using the same pattern as existing code
                            $transaction = tx($amount)
                                ->currency(config('wallet.default_currency', 'CREDIT'))
                                ->processor('withdraw')
                                ->from($user)
                                ->meta([
                                    'data' => $reason . ' - ' . __('wallet.admin_adjustment'),
                                    'admin_user' => auth()->user()->name,
                                    'admin_transaction' => true,
                                ])
                                ->commit();
                            break;
                    }

                    if ($transaction) {
                        $newBalance = $user->balance()->value;

                        Notification::make()
                            ->title(__('wallet.transaction_created'))
                            ->body("Transaction: {$amount} credits {$transactionType} for {$user->name}. New balance: {$newBalance} credits")
                            ->success()
                            ->duration(7000)
                            ->send();

                        // Try to refresh the wallet widgets if we're on a page that supports it
                        try {
                            $this->dispatch('refreshWallet');
                        } catch (\Exception $e) {
                            // Silently ignore if dispatch doesn't work
                        }
                    }

                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Error')
                        ->body($e->getMessage())
                        ->danger()
                        ->duration(5000)
                        ->send();
                }
            });
    }
}

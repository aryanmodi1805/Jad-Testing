<?php

namespace App\Filament\Resources;

use App\Concerns\SubscribeFrom;
use App\Filament\Actions\RefundRequestTableAction;
use App\Filament\Resources\PurchaseResource\Pages;
use App\Filament\Resources\PurchaseResource\RelationManagers;
use App\Models\PaymentMethod;
use App\Models\Purchase;
use App\Models\Request;
use App\Models\Seller;
use App\Services\Payment\ClickPay;
use App\Services\Payment\PaymentService;
use Filament\Forms;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?int $navigationSort = 11;

    public static function getNavigationGroup(): ?string
    {
        return __('wallet.payments.nav_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('wallet.purchases');
    }

    public static function getModelLabel(): string
    {
        return __('wallet.purchase');
    }

    public static function getPluralLabel(): ?string
    {
        return __('wallet.purchases');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->actionsPosition(Tables\Enums\ActionsPosition::BeforeCells)
            ->columns(SubscribeFrom::getPurchaseColumns())
            ->filters([

                TernaryFilter::make('status')
                    ->label(__('wallet.payment_status.single'))
                    ->placeholder(__('labels.all'))
                    ->trueLabel(__('wallet.payment_status.success'))
                    ->falseLabel(__('wallet.payment_status.failed'))
                    ->queries(
                        true: fn(Builder $query) => $query->whereStatus(true),
                        false: fn(Builder $query) => $query->whereStatus(false),
                        blank: fn(Builder $query) => $query, // In this example, we do not want to filter the query when it is blank.
                    ),
//                SelectFilter::make('payable')
//                    ->relationship('payable', 'name')
//                    ->preload()
//
//                    ->options([
//                        Seller::class,
//                    ])
//                    ->query(function ( $query, $state) {
////                        return $query->whereHasMorph('payable', $state);
//                    })
//                QueryBuilder::make()
//                    ->constraints([
//                        BooleanConstraint::make('status'),
//                    ]),

            ])
            ->actions([
                RefundRequestTableAction::make(),

                Tables\Actions\Action::make('refund_process')->label(__('subscriptions.refund_process_action'))
                    ->visible(fn($record) => !$record->is_form_wallet && $record->request_refund)
                    ->iconButton()->outlined()
                    ->icon('heroicon-o-cube-transparent')
                    ->requiresConfirmation()
                    ->form(
                        [
                            Forms\Components\TextInput::make('refund_amount')->label(__('subscriptions.refund_amount'))->required()
                                ->suffix(fn($record) => " " . $record->getCurrency())
                                ->numeric()
                                ->minValue(1)
                                ->formatStateUsing(fn($record) => $record->refund_amount)
                                ->maxValue(fn($record) => $record->refund_amount)
                                ->lte(fn($record) => $record->refund_amount,true),
                            Forms\Components\Textarea::make('refund_reason')
                                ->afterStateHydrated(function ($record, $component, $state) {
                                    $component->state($record->paymentDetails?->refund_reason);
                                })
                                ->label(__('subscriptions.refund_reason'))->required(),
                        ]
                    )
                    ->color('warning')
                    ->action(function (Model $record, array $data) {

                        $paymentMethod = $record->paymentDetails?->method;
                        $paymentClass = PaymentService::getPayment($paymentMethod->type);
                        $gateway = new $paymentClass($paymentMethod->details);

                        if ($paymentMethod) {
                            $cart_id = $record->paymentDetails?->payment_details->cart_id ?? "r_" . $record->id;
                            $refund = $gateway->refund(
                                $record->previous_tran_ref,
                                $cart_id,
                                $record->refund_amount,
                                $data['refund_reason'],
                                $record->currency,
                                $record->id);

                            Log::channel('Tap')->info('refund request --'.json_encode($refund));

                            $record->confirmRefund($refund['success'] ? 1 : 0, $refund['result']['tran_ref'], $refund['result'], $refund['result']['payment_result']['response_message']);


                        }

                    }),

//                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                MorphToSelect::make('payable')
                    ->types([
                        MorphToSelect\Type::make(Seller::class)->label(__('seller'))
                            ->titleAttribute('name'),
                    ]),
                MorphToSelect::make('purchasable')
                    ->types([
                        MorphToSelect\Type::make(Request::class)->label(__('Request'))
                            ->titleAttribute('id'),
                    ]),


                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_form_wallet')
                    ->required(),
                Forms\Components\TextInput::make('transaction_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('payment')
                    ->maxLength(255)
                    ->default(null),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePurchases::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('paymentDetails'); // TODO: Change the autogenerated stub
    }

    public function getTitle(): string|Htmlable
    {
        return __('wallet.purchases');
    }
}

<?php

namespace App\Filament\Wallet\Actions;

use App\Forms\Components\PackageSelectList;
use App\Models\Package;
use Exception;
use Filament\Actions\Action;

use App\Http\Requests\WalletWithdrawRequest;
use FundsAPI\Exceptions\BadRequestException;
use FundsAPI\Payout;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use O21\LaravelWallet\Contracts\Transaction;

class WithdrawalCreditAction extends Action
{

//    protected Model|Closure|null $record = null;


    public static function getDefaultName(): ?string
    {
        return 'Withdrawal Credit';
    }

    private float $amount = 0;

    protected function setUp(): void
    {
        parent::setUp();


        $this->form([
//            TextInput::make('amount')->required(),
            PackageSelectList::make('package_id')
                ->gridDirection('row')
                ->options(fn() => Package::where('is_active', 1)->get()->pluck('name', 'id')),
        ]);
        $this->icon('heroicon-o-document-check');

        $this->action(function (array $data,): void {
//            dd($data);
            try {
                $package = Package::find($data['package_id']);
                if ($package) {
                    deposit($package->credits)->to(auth('seller')->user())->overCharge()->meta([
                        'data' => __('wallet.pay') . " " . __('wallet.packages.single') ." (" . $package->name . ") [". $package->credits . " credits ] ," . $package->description,
                        'package' => $package->toArray(),
                    ])
                        ->commit();
                    $this->amount = $package->credits;
                }
                if (!empty($this->amount)) {
                    $this->successNotificationTitle(__('wallet.added_to_balance', ['amount' => $this->amount]));
                    $this->success();
                }
                $this->dispatch('refresh');

            } catch (Exception $ex) {
                $this->failureNotificationTitle = $ex->getMessage();
                $this->failure();
            }


        })
            ->label(__('wallet.charge'));
    }

    public function withdraw(WalletWithdrawRequest $request): JsonResponse
    {
        $amount = $request->get('amount');
        $destination = $request->get('destination');

        $fee = $this->getWithdrawFee();
        $commission = num($amount)->mul($fee['percent'] / 100)->add($fee['fixed']);

        $tx = tx($amount)
            ->commission($commission)
            ->processor('withdraw')
            ->from(auth('seller')->user())
            ->status('awaiting_approval')->commit();
            //->after(
            /**
             * Creating payout in after() closure allows you to avoid
             * the situation when the transaction is created, but the payout is not.
             */
                /*function (Transaction $transaction)
                use ($destination, $store, $btcPayConfig) {
                    $payout = $this->createPayout(
                        $transaction->received, // received = amount - commission
                        $destination,
                        $transaction
                    );

                    $transaction->updateMeta([
                        'payout' => [
                            'id'      => $payout->getId(),
                        ],
                        'comment' => [
                            'type'  => 'text',
                            'value' => $destination,
                        ]
                    ]);
                }*/
           // )

          //  ->commit();

        return response()->json($tx->toApi());
    }
/*
    protected function createPayout(
        string $amount,
        string $destination,
        Transaction $tx
    ): Payout {
        try {
            $payout = FundsAPI::createPayout(
                $amount,
                $destination,
                $tx->getCurrency(),
                meta: [
                    'txid' => $tx->getId(),
                ]
            );
        } catch (BadRequestException $e) {
            $response = @json_decode(
                \Str::extractJson($e->getMessage()),
                true,
                512,
                JSON_THROW_ON_ERROR
            ) ?? [];

            $code = $response['code'] ?? $e->getCode();
            $message = $response['message'] ?? $e->getMessage();

            throw new HttpResponseException(
                response()->json([
                    'errors' => [
                        $code => [
                            $message,
                        ],
                    ],
                ], 422)
            );
        }

        return $payout;
    }*/


}

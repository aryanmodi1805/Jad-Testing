<?php

namespace App\Filament\Wallet\Actions;

use App\Enums\ResponseStatus;
use App\Interfaces\CanPayItem;
use App\Models\Request;
use App\Models\Response;
use App\Notifications\SellerResponseNotification;
use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;


class PayRequestAction extends Action
{

//    protected Model|Closure|null $record = null;

    protected CanPayItem $purchasable;

    public static function getDefaultName(): ?string
    {
        return 'Pay Request';
    }

    private float $amount = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->icon('heroicon-o-puzzle-piece');
        $this->color('success');
        $this->action(function ($record): void {
            try {
                $seller = auth('seller')->user();
                $this->purchasable = $record;
                if(empty($this->purchasable)){
                    $this->cancel();
                    return;
                }
                $this->amount = $this->purchasable->getFinalPrice();

                if ($seller->is_purchased($this->purchasable)) {
                    $this->failureNotificationTitle(__('string.has_been_bought'));
                    $this->failure();
                    return;
                }
                $paid = $seller->payItem($this->purchasable, config('wallet.default_currency'));


                if (  $paid) {
                    $this->createResponse($this->purchasable);
                    $seller->updateAvgResponse();
//                    $this->successNotificationTitle(__('wallet.withdraw_form_balance', ['amount' => $this->amount]).__('string.request-been-transferred'));
//                    $this->success();
                    Notification::make()
                        ->success()
                        ->title(__('wallet.withdraw_form_balance', ['amount' => $this->amount]))
                        ->duration(5000)
                        ->send();

                    Notification::make()
                        ->success()
                        ->title(__('string.request-been-transferred'))
                        ->duration(5000)
                        ->send();

                } else {
                    $this->failureNotificationTitle = "Process Fails ";
                    $this->failure();
                }


            } catch (Exception $ex) {
                $this->failureNotificationTitle = $ex->getMessage();
                $this->failure();
            }


        })
            ->label(__('wallet.pay'));
    }



    protected function createResponse(CanPayItem $purchasable): void
    {
        $response = Response::updateOrCreate([
            'request_id' => $purchasable->id,
            'service_id' => $purchasable->service->id,
            'seller_id' => auth('seller')->id(),
        ],[
            'status' => ResponseStatus::Pending,
            'notes' => null,
        ]);
        $request = Request::find($purchasable->id);
        $request->customer->notify(new SellerResponseNotification($response));

    }
}

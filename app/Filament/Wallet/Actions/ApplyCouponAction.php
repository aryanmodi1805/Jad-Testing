<?php

namespace App\Filament\Wallet\Actions;

use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;

class ApplyCouponAction extends Action
{

//    protected Model|Closure|null $record = null;


    public static function getDefaultName(): ?string
    {
        return 'apply Coupon';
    }


    protected function setUp(): void
    {
        parent::setUp();


        $this->form([
            TextInput::make('code')->required(),
        ]);


        $this->hidden();
        $this->icon('heroicon-o-document-check');

        $this->action(function (array $data,): void {
            try {
                $coupon = auth('seller')->user()->redeemCoupon($data['code']);
//                    $coupon = auth('seller')->user()->redeemCoupon($data['code']);
                charge($coupon->value)->to(auth('seller')->user())->overCharge()->commit();

                if ($coupon->value)
                    $this->successNotificationTitle(__('wallet.added_to_balance', ['amount' => $coupon->value]));
            } catch (Exception $ex) {
                $this->failureNotificationTitle = $ex->getMessage();
                $this->failure();
            }

            $this->success();
        })
            ->label(__('wallet.apply_coupon'));
    }
}

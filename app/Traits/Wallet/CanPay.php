<?php

namespace App\Traits\Wallet;

use App\Concerns\SubscribeFrom;
use App\Interfaces\CanPayItem;
use App\Models\Purchase;
use O21\LaravelWallet\Models\Transaction;

trait CanPay
{
    public function payItem(CanPayItem $item, ?string $currency = null, $payment = "wallet"): bool
    {
        $price = $item->getFinalPrice();
        $this->assertHaveFunds($price, $currency);


        if (!$this->isEnoughFunds($price, $currency)) return false;

        $tx = tx($price)
            ->currency($currency)
            ->processor('withdraw')
            ->from($this)
            ->meta($item->getWalletMeta() ?? [])
            ->after(function (Transaction $tx)
            use ($price, $item, $payment) {

                SubscribeFrom::createPurchase(
                    price: $price,
                    item: $item,
                    payment: $payment,
                    payable: $this,
                    transaction_id: $tx->id,
                    chargeable: $tx,
                    payment_detail_id: null,
                    is_form_wallet: 1,
                    currency: $tx->currency,
                    country_id: $this->country_id ??null,
                    status: 1
                );




            }) //  insert to purchased request

//            ->status('awaiting_approval')

            ->commit();

        return true;
    }

    public function purchases()
    {
        return $this->morphMany(Purchase::class, 'payable');
    }


    /**
     * Have I been previously purchased the given item
     * @param $purchasable
     * @return bool
     */
    public function is_purchased($purchasable): bool
    {
        if (!$purchasable) {
            return false;
        }

        return $this->purchases()
            ->where('purchasable_id', $purchasable->id)
            ->where('status', 1)
            ->exists();
    }


}

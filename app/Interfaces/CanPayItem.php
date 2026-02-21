<?php

namespace App\Interfaces;

interface CanPayItem
{
    public function getFinalPrice():float;
    public function getWalletMeta(): array;
    public function getPaymentTitle(): string;

}

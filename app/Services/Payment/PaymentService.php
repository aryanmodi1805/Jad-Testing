<?php

namespace App\Services\Payment;


class PaymentService
{
    public const paymentsList = [
        'clickPay' => 'Click pay',
        'manual' => 'يدوي',
        'floosak' => 'فلوسك',
        'one_cash' => 'ONE كاش',
        'saba_cash' => 'سبأ كاش',
        'cash_pay' => 'كاش',
        'pyes' => 'بيس',
        'kuraimi_haseb' => 'الكريمي حاسب',
        'mobile_money' => 'موبايل موني',
        'hawala' => 'حواله',
        'payPal' => 'باي بال',
        'stripe' => 'Stripe',
        'tap' => 'Tap',
    ];
    public const paymentsListClass = [
        // Kuraimi::class,
        Pyes::class,//
        Floosak::class,//
        Tamkeen::class,
        SabaCash::class,
        OneCash::class,
        MobileMoney::class,
        Hawala::class,
        OnDelivered::class,
        Stripe::class,
        PayPal::class,
        ClickPay::class,
        Tap::class
    ];

    public const enabledPaymentsListClass = [
        Stripe::class,
//        Pyes::class,
//        Floosak::class,
//        Tamkeen::class,
//        SabaCash::class,
//        OneCash::class,
//        MobileMoney::class,
        Hawala::class,
        ClickPay::class,
//        OnDelivered::class,
//        PayPal::class,
        Tap::class
    ];

    public static function getAllPaymentsGetWay()
    {
        $list = collect();
        foreach (self::paymentsListClass as $payment) {
            $list->prepend($payment::getName(), $payment::getProviderName());
        }
        return $list;
    }

    public static function getEnabledPaymentsGetWay()
    {
        $list = collect();
        foreach (self::enabledPaymentsListClass as $payment) {
            $list->prepend($payment::getName(), $payment::getProviderName());
        }
        return $list;
    }

    public static function getDefaultData($provider): array
    {

        foreach (self::paymentsListClass as $payment) {
            if ($provider == $payment::getProviderName())
                return $payment::getKeysData();


        }
        return [];
    }

    public static function getAdditionalData($provider): array
    {

        foreach (self::paymentsListClass as $payment) {
            if ($provider == $payment::getProviderName())
                return $payment::getAdditionalFields();


        }
        return [];
    }

    public static function getDefaultName($provider): string
    {

        foreach (self::paymentsListClass as $payment) {
            if ($provider == $payment::getProviderName())
                return $payment::getName();


        }
        return "";
    }

    public static function getPayment($provider)
    {

        foreach (self::paymentsListClass as $payment) {
            if ($provider == $payment::getProviderName())
                return $payment;


        }
        return null;
    }
}

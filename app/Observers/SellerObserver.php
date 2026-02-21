<?php

namespace App\Observers;

use App\Mail\GlobalMail as MailGlobalMail;
use App\Models\Seller;
use App\Models\SellerNotificationSetting;
use App\Settings\SellerNotificationSettings;
use Illuminate\Support\Facades\Mail;

class SellerObserver
{
    /**
     * Handle the Seller "created" event.
     */
    public function created(Seller $seller): void
    {
        $defaultNotificationSettings = app(SellerNotificationSettings::class)->toArray();

        SellerNotificationSetting::create(
            array_merge(
                ['seller_id'=> $seller->id],
                $defaultNotificationSettings
            )
        );

        $seller->update([
            'locale' => app()->getLocale()
        ]);

    }

    /**
     * Handle the Seller "updated" event.
     */
    public function updated(Seller $seller): void
    {

    }

    /**
     * Handle the Customer "updating" event.
     */
    public function updating(Seller $seller): void
    {
        if ($seller->isDirty('phone')) {
            $seller->phone_verified_at = null;
        }
    }

    /**
     * Handle the Seller "deleted" event.
     */
    public function deleted(Seller $seller): void
    {
        //
    }

    /**
     * Handle the Seller "restored" event.
     */
    public function restored(Seller $seller): void
    {
        //
    }

    /**
     * Handle the Seller "force deleted" event.
     */
    public function forceDeleted(Seller $seller): void
    {
        //
    }
}

<?php

namespace App\Observers;

use App\Models\Customer;
use App\Models\CustomerNotificationSetting;
use App\Models\SellerNotificationSetting;
use App\Settings\CustomerNotificationSettings;
use App\Settings\SellerNotificationSettings;

class CustomerObserver
{
    /**
     * Handle the Customer "created" event.
     */
    public function created(Customer $customer): void
    {
        $defaultNotificationSettings = app(CustomerNotificationSettings::class)->toArray();

        CustomerNotificationSetting::create(
            array_merge(
                ['customer_id'=> $customer->id],
                $defaultNotificationSettings
            )
        );

        $customer->update([
            'locale' => app()->getLocale()
        ]);
    }

    /**
     * Handle the Customer "updated" event.
     */
    public function updated(Customer $customer): void
    {

    }
    /**
     * Handle the Customer "updating" event.
     */
    public function updating(Customer $customer): void
    {
        if ($customer->isDirty('phone')) {
            $customer->phone_verified_at = null;
        }
    }

    /**
     * Handle the Customer "deleted" event.
     */
    public function deleted(Customer $customer): void
    {
        //
    }

    /**
     * Handle the Customer "restored" event.
     */
    public function restored(Customer $customer): void
    {
        //
    }

    /**
     * Handle the Customer "force deleted" event.
     */
    public function forceDeleted(Customer $customer): void
    {
        //
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (\App\Models\Customer::doesntHave('notificationSettings')->get() as $customer) {
            $defaultNotificationSettings = app(\App\Settings\CustomerNotificationSettings::class)->toArray();

            \App\Models\CustomerNotificationSetting::create(
                array_merge(
                    ['customer_id' => $customer->id],
                    $defaultNotificationSettings
                )
            );
        }

        foreach (\App\Models\Seller::doesntHave('notificationSettings')->get() as $seller) {
            $defaultNotificationSettings = app(\App\Settings\SellerNotificationSettings::class)->toArray();

            \App\Models\SellerNotificationSetting::create(
                array_merge(
                    ['seller_id' => $seller->id],
                    $defaultNotificationSettings
                )
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

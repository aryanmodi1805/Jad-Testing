<?php

namespace App\Livewire;

use App\Filament\Customer\Resources\RequestResource\Pages\ListRequests;
use App\Models\Customer;
class SwitchCustomer extends SwitchAccount
{
    public $destinationClass = Customer::class;
    public $destinationPage = ListRequests::class;

    public $source = 'seller';

    public $destination = 'customer';

}

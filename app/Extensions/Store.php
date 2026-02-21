<?php

namespace App\Extensions;

use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Session\Store as BaseStore;
use Illuminate\Support\Str;

class Store extends BaseStore
{
    public function regenerateToken()
    {
        $this->put('_token', Str::random(40));
    }

}

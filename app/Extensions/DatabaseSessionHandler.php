<?php

namespace App\Extensions;

use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Session\DatabaseSessionHandler as BaseDatabaseSessionHandler;

class DatabaseSessionHandler extends BaseDatabaseSessionHandler
{
    protected function addUserInformation(&$payload): DatabaseSessionHandler|static
    {
        if ($this->container->bound(Factory::class) && Filament::auth()->user() != null) {
            $payload['user_id'] = Filament::auth()->id();
            $payload['user_type'] = Filament::auth()->user()::class;
        }

        return $this;
    }

}

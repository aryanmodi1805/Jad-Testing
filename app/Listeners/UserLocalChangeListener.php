<?php

namespace App\Listeners;

namespace App\Listeners;

use Filament\Facades\Filament;


class UserLocalChangeListener
{
    public function handle(object $event): void
    {
        if(auth('customer')->check()) {
            auth('customer')->user()->update(['locale' => $event->locale]);
        }

        if (auth('seller')->check()) {
            auth('seller')->user()->update(['locale' => $event->locale]);
        }

        if (auth('admin')->check()) {
            auth('admin')->user()->update(['locale' => $event->locale]);
        }

    }
}

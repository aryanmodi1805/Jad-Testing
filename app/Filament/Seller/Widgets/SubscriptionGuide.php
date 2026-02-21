<?php

namespace App\Filament\Seller\Widgets;

use Filament\Widgets\Widget;

class SubscriptionGuide extends Widget
{
    protected static string $view = 'filament.seller.widgets.subscription-guide';
    protected static bool $isDiscovered =false;
    protected static ?string $pollingInterval = null;
    protected int | string | array $columnSpan ='full';
}

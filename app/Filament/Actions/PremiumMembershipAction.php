<?php

namespace App\Filament\Actions;

use App\Filament\Seller\Pages\SubscriptionPlans;
use Filament\Actions\Action;
use Filament\Forms\Components\ViewField;
use Filament\Support\Enums\MaxWidth;


class PremiumMembershipAction extends Action
{
//    protected Model|Closure|null $record = null;

    public static function getDefaultName(): ?string
    {
        return 'Premium Membership';
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->icon('heroicon-o-star');
        $this->url(SubscriptionPlans::getUrl());
        $this->color('success');
        $this->successNotificationTitle(__('seller.premium_membership'));
        $this->modalWidth(MaxWidth::MaxContent);
         $this->modalSubmitAction(false);


        $this->action(function (array $data,): void {
        })
            ->label(__('seller.premium_membership'));
    }

    public static function getFromComponents(): array
    {
        return [
            ViewField::make('price')
                ->view('forms.components.pricing-plans-field'),

        ];
    }


}

<?php

namespace App\Filament\Actions;

use App\Models\PricingPlan;
use Filament\Actions\Action;
use Filament\Forms\Components\ViewField;


class SubscribeNowAction extends Action
{
//    protected Model|Closure|null $record = null;

    public static function getDefaultName(): ?string
    {
        return 'Subscribe Now';
    }

    public static function getFromComponents(): array
    {
        return [
            ViewField::make('price')
                ->view('forms.components.pricing-plans-field'),

        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->icon('heroicon-o-clock');
        $this->requiresConfirmation();
        $this->extraAttributes(['class' => 'px-6 py-2 w-full border-2  rounded-full shadow-2xl  text-primary-600']);
        $this->action(function (array $arguments) {
            $pricingPlan = PricingPlan::find($arguments['post']);
            dd($pricingPlan);


        });
        $this->label(__('subscriptions.subscribe_now'));
    }


}

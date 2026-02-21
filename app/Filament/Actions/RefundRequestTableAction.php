<?php

namespace App\Filament\Actions;

use App\Concerns\SubscribeFrom;
use App\Enums\Wallet\SubscriptionPlanType;
use App\Models\PaymentMethod;
use App\Models\PricingPlan;
use App\Services\Payment\PaymentService;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;


class RefundRequestTableAction extends Action
{
//    protected Model|Closure|null $record = null;


    public static function getDefaultName(): ?string
    {
        return 'refund_request';
    }


    protected function setUp(): void
    {
        parent::setUp();
        $this->label(__('subscriptions.request_refund'))
            ->iconButton()
            ->disabled(fn($record) => $record->isDisableRefund())
            ->visible(fn($record) => $record->allowRefund())
            ->icon('heroicon-o-arrow-uturn-left')
            ->requiresConfirmation()
            ->form(
                [
                    TextInput::make('refund_amount')->label(__('subscriptions.refund_amount'))->required()
                        ->suffix(fn($record) => " " . $record->getCurrency())
                        ->formatStateUsing(fn($record) => $record->amount)
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(fn($record) => $record->amount)
                    ->lte(fn($record) => $record->amount,true),
                   Textarea::make('refund_reason')->label(__('subscriptions.refund_reason'))->required(),
                ]
            )
            ->action(function (Model $record, array $data) {
                $record->refund($data['refund_amount'], $data['refund_reason']);
            });

    }
}

<?php

namespace App\Filament\Seller\Pages;

use App\Filament\Actions\SubscribeAction;
use App\Filament\Seller\Widgets\SubscriptionGuide;
use App\Models\PricingPlan;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

#[On('refreshPlans')]
class SubscriptionPlans extends Page implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;


    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.seller.pages.subscription-plans';

    public PricingPlan|null $plan = null;
    public bool $isAlreadySubscribed = false;
    public string $action_type = 'new_subscription';

    protected static ?int $navigationSort = 7;

    public static function shouldRegisterNavigation(): bool
    {
        $generalSettings = app(\App\Settings\GeneralSettings::class);
        return $generalSettings->show_subscriptions_page ?? false;
    }

    public static function getNavigationLabel(): string
    {
        return __('subscriptions.subscription_plans');
    }

    public static function getLabel(): string
    {
        return __('subscriptions.subscription_plans');
    }

    public function payoutAction()
    {
        return \Filament\Actions\Action::make('payout')->label(__('subscriptions.notification_payment_due'))
            ->closeModalByClickingAway(false)
            ->closeModalByEscaping(false)
            ->modalContentFooter(null)
            ->modalSubmitAction(false)
            ->modalContent(function ($arguments, $action) {
                $url = $arguments['return_url'];
                return new HtmlString(
                    '
                   <iframe src="' . $url . '" frameborder="0" width="100%" height="700" ></iframe>

                   '
                );
            });

    }

    public function table(Table $table): Table
    {
        $sellerId = auth(filament()->getAuthGuard())->user()->id;
        return $table
            ->paginated(false)
            ->query(fn() => PricingPlan::withCount(['subscriptions' => fn($query) => $query->where('seller_id', $sellerId)->active()])
                ->with('currency')
                ->where('is_active', 1)
            )
            ->columns([
                Stack::make([

                    ViewColumn::make('name')->view('components.pricing-plan')
                        ->extraCellAttributes(['class' => 'plan-container ring-0 border-0 border-none bg-transparent h-full mb-4'])

                ])->extraAttributes([
                    'class' => 'stack-component sellerContainer mb-8'
                ])->space(2)

            ])
            ->contentGrid([
                'md' => 2,
                'lg' => 2,
                'xl' => 3,
                '2xl' => 4,
                'sm' => 1,
                'xs' => 1,
                'default' => 1
            ])
            ->filters([

            ])
            ->actions([
                SubscribeAction::make(), //->visible(fn($record) => $record->subscriptions_count == 0),
//                Action::make('already_subscribed')->label(__('subscriptions.already_subscribed'))
//                    ->visible(fn($record) => $record->subscriptions_count > 0)->disabled()->color('warning')
//                    ->button()->extraAttributes(['class' => '-mt-16 mx-auto']),

            ])
            ->bulkActions([

            ]);


    }

    public function getTitle(): string|Htmlable
    {
        return __('subscriptions.subscription_plans');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SubscriptionGuide::class
        ];
    }

}

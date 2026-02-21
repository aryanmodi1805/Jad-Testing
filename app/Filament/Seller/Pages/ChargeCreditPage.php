<?php

namespace App\Filament\Seller\Pages;

use App\Filament\Wallet\Actions\ChargeCreditAction;
use App\Models\PaymentMethod;
use Filament\Actions\Action;
use Filament\Actions\Contracts\HasActions;
use Filament\Facades\Filament;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Support\Enums\Alignment;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;

class ChargeCreditPage extends Page implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithFormActions;


    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.seller.pages.charge-credit-page';
    protected static string $layout= 'components.layouts.simple';
    protected static bool $shouldRegisterNavigation = false;
    public static string | Alignment $formActionsAlignment = Alignment::Center;


    protected function getLayoutData(): array
    {
        return [
            'hasTopbar' => false,
            'maxWidth' => 'full',
        ];
    }

    public function hasLogo(): bool
    {
        return false;
    }

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function getTitle(): string|Htmlable
    {
        return "";
     //   return __('wallet.charge');
    }

    public function charge(): void
    {
        $credit_price = getCurrentTenant()?->credit_price ?? 1;
        $data = $this->form->getState();
        ChargeCreditAction::doChargeAction(data: $data, livewire: $this, credit_price: $credit_price, action: 'api');
    }

    /**
     * @return array<int | string, string | Form>
     */
    protected function getForms(): array
    {
        $payment_methods = PaymentMethod::select('id', 'name', 'logo', 'type')->where('active', 1)->get()->pluck('name_html', 'id');
        $tenant = getCurrentTenant();
        $credit_price = $tenant?->credit_price ?? 1;
        $currency = $tenant?->currency?->symbol ?? "";
        $vat_percentage = $tenant?->vat_percentage ?? 0;
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema(ChargeCreditAction::getFormArray($currency, $payment_methods, $credit_price, $vat_percentage))
                    ->statePath('data'),
            ),
        ];
    }

    public function form(Form $form): Form
    {
        return $form;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getChargeFormAction(),
        ];
    }

    public function getChargeFormAction(): Action
    {
        return Action::make('chargeView')
            ->label(__('wallet.pay'))
            ->extraAttributes(['class' => 'mx-4'])
            ->submit('charge');
    }
}

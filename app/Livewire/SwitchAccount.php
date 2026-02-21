<?php

namespace App\Livewire;

use App\Filament\Customer\Resources\RequestResource\Pages\ListRequests;
use App\Models\Customer;
use App\Rules\LatLngInCountry;
use App\Rules\PasswordValidation;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Auth\VerifyEmail;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class SwitchAccount extends Component implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;
    use WithRateLimiting;

    public $destinationClass = null;
    public $destinationPage = null;

    public $source = null;

    public $destination = null;

    public function render()
    {
        return view('livewire.switch-account');
    }

    public function switchAction(): Action
    {
        return Action::make('switch')
            ->label(__('string.switch_panel', ['destination' => __("string.$this->destination")]))
            ->link()
            ->action(function (Action $action) {
                if (auth($this->source)->check()) {
                    $sourceAccount = auth($this->source)->user();
                    if ($sourceAccount->{"{$this->destination}_id"} != null) {
                        if (auth($this->destination)->check()) {
                            $this->redirectToPanel();
                        } else {
                            auth($this->destination)->login($sourceAccount->associatedAccount);
                            $this->redirectToPanel();
                        }
                    } else {
                        $exists = $this->destinationClass::where('email', $sourceAccount->email)->exists();
                        if ($sourceAccount->email_verified_at != null) {
                            if ($exists) {
                                $this->replaceMountedAction('loginAssociated');

                            } else {
                                $this->replaceMountedAction('associated');
                            }
                        } else {
                            if ($exists) {
                                $action->failureNotificationTitle(__('string.switch_account_verify_email_first', ['destination_lower' => strtolower(__("string.$this->destination"))]));
                            } else {
                                $action->failureNotificationTitle(__('string.switch_account_verify_email', ['destination_lower' => strtolower(__("string.$this->destination"))]));
                            }

                            $sourceAccount->notify(new VerifyEmail());
                            $action->failure();
                        }

                    }
                } else {
                    redirect()->refresh();
                }
            });

    }

    public function redirectToPanel(): void
    {
        redirect()->to($this->destinationPage::getUrl(panel: $this->destination, tenant: getCurrentTenant()));

    }

    public function associatedAction(): Action
    {
        return Action::make('associated-customer')
            ->mountUsing(function (Action $action) {
                $sourceAccount = auth($this->source)->user();
                $destinationAccount = $this->destinationClass::where('email', $sourceAccount->email)->first();
                if (!$destinationAccount || $sourceAccount->country_id != $destinationAccount->country_id) {
                    $action->failureNotificationTitle(__('string.no_account_to_associated'));
                    $action->failure();
                    return;
                }
            }
            )
            ->requiresConfirmation()
            ->successNotificationTitle(__('string.create_associated_customer_success'))
            ->modalDescription(__('string.create_associated_account_desc', ['destination_lower' => strtolower(__("string.$this->destination")), 'source_lower' => strtolower(__("string.$this->source"))]))
            ->modalHeading(__('string.create_associated_account', ['destination' => __("string.$this->destination")]))
            ->successNotificationTitle(__('string.create_associated_account_success', ['destination' => __("string.$this->destination")]))
            ->action(function (Action $action) {
                $sourceAccount = auth($this->source)->user();

                $associatedAccount = $this->destinationClass::create([
                    'name' => $sourceAccount->name,
                    'email' => $sourceAccount->email,
                    'password' => $sourceAccount->password,
                    'phone' => $sourceAccount->phone,
                    'email_verified_at' => $sourceAccount->email_verified_at,
                    'phone_verified_at' => $sourceAccount->phone_verified_at,
                    'avatar_url' => $sourceAccount->avatar_url,
                    'country_id' => $sourceAccount->country_id,
                    'tokens' => $sourceAccount->tokens,
                    'locale' => $sourceAccount->locale,
                    "{$this->source}_id" => $sourceAccount->id,
                ]);

                $sourceAccount->update(["{$this->destination}_id" => $associatedAccount->id]);

                $action->success();

                auth($this->destination)->login($associatedAccount);

                $this->redirectToPanel();

            })->extraAttributes([
                'style' => 'display:none'
            ]);

    }

    public function loginAssociatedAction(): Action
    {

        return Action::make('login-associated-customer')
            ->requiresConfirmation()
            ->mountUsing(function (Action $action) {
                $sourceAccount = auth($this->source)->user();
                $destinationAccount = $this->destinationClass::where('email', $sourceAccount->email)->first();
                if ($sourceAccount->country_id != $destinationAccount->country_id) {
                    $action->failure();
                    $action->cancel();
                }
            }
            )
            ->modalDescription(__('string.link_associated_account_desc', ['destination_lower' => strtolower(__("string.$this->destination"))]))
            ->modalHeading(__('string.link_associated_account', ['destination' => __("string.$this->destination")]))
            ->successNotificationTitle(__('string.link_associated_account_success', ['destination' => __("string.$this->destination")]))
            ->failureNotificationTitle(__('string.no_account_to_associated'))
            ->form([
                TextInput::make('password')
                    ->label(__('filament-panels::pages/auth/login.form.password.label'))
                    ->password()
                    ->revealable(filament()->arePasswordsRevealable())
                    ->rule(new PasswordValidation(auth($this->source)->user(), $this->destination))
                    ->autocomplete('current-password')
                    ->required()
                    ->extraInputAttributes(['tabindex' => 1])
            ])
            ->modalSubmitActionLabel(__('string.associate_and_login'))
            ->beforeFormValidated(function (Action $action) {
                try {
                    $this->rateLimit(5);
                } catch (TooManyRequestsException $exception) {
                    $this->getRateLimitedNotification($exception)?->send();
                    $action->halt();
                }

            })
            ->action(function (Action $action) {
                $sourceAccount = auth($this->source)->user();

                $destinationAccount = $this->destinationClass::where('email', $sourceAccount->email)->first();

                if ($sourceAccount->country_id != $destinationAccount->country_id) {
                    $action->failure();
                    return;
                }

                $sourceAccount->update(["{$this->destination}_id" => $destinationAccount->id]);

                $destinationAccount->update([
                    'email_verified_at' => $sourceAccount->email_verified_at,
                    "{$this->source}_id" => $sourceAccount->id,
                ]);

                $this->redirectToPanel();

            })->extraAttributes([
                'style' => 'display:none'
            ]);

    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.password' => __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }

    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        return Notification::make()
            ->title(__('filament-panels::pages/auth/login.notifications.throttled.title', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => $exception->minutesUntilAvailable,
            ]))
            ->body(array_key_exists('body', __('filament-panels::pages/auth/login.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/login.notifications.throttled.body', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => $exception->minutesUntilAvailable,
            ]) : null)
            ->danger();
    }

}

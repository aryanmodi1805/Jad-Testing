<?php

namespace App\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Pages\Auth\PasswordReset\RequestPasswordReset;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class JADRequestPasswordReset extends RequestPasswordReset
{
    public function getHeading(): string | Htmlable
    {
        return new  HtmlString(__('filament-panels::pages/auth/password-reset/request-password-reset.heading')
            . '<br> <span class="text-gray-700  text-sm dark:text-gray-300">'.__('passwords.request_password_reset_heading').' </span>'
        );

    }
//
//    /**
//     * @return array<int | string, string | Form>
//     */
//    protected function getForms(): array
//    {
//        return [
//            'form' => $this->form(
//                $this->makeForm()
//                    ->schema([
//                        Placeholder::make('heading')->hiddenLabel()
//                            ->content(__('passwords.heading'))
//                        ->extraAttributes(['class' => 'm-0  font-semibold  ']),
//                        $this->getEmailFormComponent(),
//                    ])
//                    ->statePath('data'),
//            ),
//        ];
//    }
    protected function getRequestFormAction(): Action
    {
        $action = parent::getRequestFormAction() ;
        $action->label(__('passwords.request_password_reset_action'));
        return $action;
    }
}

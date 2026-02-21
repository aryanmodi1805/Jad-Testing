<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Illuminate\Support\HtmlString;

class ContactAction extends ActionGroup
{

    public static function getDefaultName(): ?string
    {
        return 'Contact';
    }

    public static function make(array $actions = [] , $record = null): static
    {
        $static = app(static::class, ['actions' => [
            Action::make('call')
                ->label($record->customer->phone)
                ->icon('heroicon-o-phone')
                ->color('success')
                ->extraAttributes(['target' => '_blank',"dir"=>"ltr"])
                ->url('tel:' . $record->customer->phone),
            Action::make('email')
                ->label($record->customer->email)
                ->color('secondary')
                ->icon('heroicon-o-envelope')
                ->extraAttributes(['target' => '_blank',"dir"=>"ltr"])
                ->url('mailto:' . $record->customer->email),
        ]]);


        $static->configure();

        $static
            ->button()
            ->icon('heroicon-o-user')
            ->color('primary')
            ->label(__('string.contact') . ' ' . $record->customer->name);

        return $static;
    }
}

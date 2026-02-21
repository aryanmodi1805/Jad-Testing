<?php

namespace App\Filament\Pages\Tenancy;

use App\Forms\Components\Translatable as ComponentsTranslatable;
use App\Models\Country;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;
use Illuminate\Database\Eloquent\Model;

class RegisterBranch extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Register Branch';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                ComponentsTranslatable::make()
                    ->required(),
                TextInput::make('code')
                    ->required()
                    ->unique()
                    ->maxLength(255),

            ]);
    }
    protected function handleRegistration(array $data): Model
    {
        $team = Country::create($data);

        $team->users->attach(auth()->user());

        return $team;
    }
}

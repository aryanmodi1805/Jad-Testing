<?php

namespace App\Livewire;

use Filament\Forms;
use Jeffgreco13\FilamentBreezy\Livewire\PersonalInfo;

class CustomPersonalInfo extends PersonalInfo
{

    public array $only = ['name', 'email', 'phone'];


    protected function getProfileFormSchema(): array
    {

        $groupFields = Forms\Components\Group::make([
            $this->getNameComponent()->maxLength(30),
            $this->getPhoneComponent(),
            Forms\Components\Placeholder::make('email')->content($this->user->email)->label(__('cv.email')),
        ])->columnSpan(2);

        return ($this->hasAvatars)
            ? [filament('filament-breezy')->getAvatarUploadComponent(), $groupFields]
            : [$groupFields];
    }

    protected function getPhoneComponent(): \App\Extensions\CustomPhoneInput
    {
        return  getPhoneInput('phone',  $this->userClass , $this->user);
    }
//    public function submit(): void
//    {
//
//        $data = collect($this->form->getState())->only($this->only)->all();
//        $this->user->update($data);
//        $this->sendNotification();
//    }
    public function render()
    {
        return view('livewire.custom-personal-info');
    }
}

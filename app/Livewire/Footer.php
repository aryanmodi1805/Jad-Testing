<?php

namespace App\Livewire;
use App\Settings\AboutSettings;
use App\Settings\SocialMediaSettings;
use Livewire\Component;

class Footer extends Component
{

    public ?string $location;
    public ?string $phone;
    public ?string $email;


    public function mount()
    {
        $aboutSettings = app(AboutSettings::class);

        $this->location = $aboutSettings->getLocationContent(app()->getLocale());
        $this->phone = $aboutSettings->phone;
        $this->email = $aboutSettings->email;

    }

    public function render()
    {
        return view('livewire.footer', [
                'socialMedia' => app(SocialMediaSettings::class)->getSocialMediaLinks()
                ,]
        );
    }
}

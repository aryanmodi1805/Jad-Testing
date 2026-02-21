<?php

namespace App\Filament\Components;

class Geocomplete extends \Cheesegrits\FilamentGoogleMaps\Fields\Geocomplete
{
    public function getTypes(): array
    {
        $types = $this->evaluate($this->types);

        return $types;
    }

}

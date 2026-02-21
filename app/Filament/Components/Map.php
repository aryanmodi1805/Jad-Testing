<?php

namespace App\Filament\Components;

class Map extends \Cheesegrits\FilamentGoogleMaps\Fields\Map
{
    public function getTypes(): array
    {
        $types = $this->evaluate($this->types);

        return $types;
    }

}

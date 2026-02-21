<?php

namespace App\Tables\Columns;

use Filament\Tables\Columns\Column;

class SubscriptionFeaturesColumn extends Column
{
    protected string $view = 'tables.columns.subscription-features-column';
   public string $type="";

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

}

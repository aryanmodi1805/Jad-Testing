<?php
namespace App\Livewire;

use App\Filament\Actions\WizardAction;
use App\Filament\Components\Map;
use App\Interfaces\HasWizard;
use App\Traits\InteractsWithServiceWizard;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\Component;

#[lazy]
class WizardComponent extends Component implements HasActions , Forms\Contracts\HasForms
{
    use InteractsWithActions;

    use Forms\Concerns\InteractsWithForms;

}

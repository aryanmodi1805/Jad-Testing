<?php

namespace App\Filament\Actions;

use Closure;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;

class RatingTableAction extends Action
{
    protected ?Model $rateable=null;
    protected ?Model $rater=null;

    public static function getDefaultName(): ?string
    {
        return 'Rating Action';
    }

    public function rateable(Model|Closure|null $rateable): static
    {
        $this->rateable = $rateable;
        return $this;
    }

    public function getRateable(): ?Model
    {
        $rateable = $this->evaluate($this->rateable);
        if ($rateable) {
            return $rateable;
        }
        return null;
    }

    public function rater(Model|Closure|null $rater): static
    {
        $this->rater = $rater;

        return $this;
    }

    public function getRater(): ?Model
    {
        $rater = $this->evaluate($this->rater);
        if ($rater) {
            return $rater;
        }
        return null;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->icon('heroicon-o-star');
        $this->color('warning');
        $this->successNotificationTitle(__('seller.rate.send_success'));
        $this->form(
            fn() => ratingAction::getFromComponents()
        );

        $this->action(function (array $data, $action): void {
            $this->rateable = $this->getRecord();
            $this->rater = $this->getRater()??auth()->user();
            ratingAction::executeAction($data, $this->rateable, $this->rater, $this);
        })
            ->label(__('seller.rate.single'));
    }
}

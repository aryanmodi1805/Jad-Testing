<?php

namespace App\Filament\Actions;

use Closure;
use Filament\Infolists\Components\Actions\Action;
use Illuminate\Database\Eloquent\Model;

class RatingInfoListAction extends Action
{
    protected Model|Closure|null $rateable = null;
    protected Model|Closure|null $rater = null;

    public static function getDefaultName(): ?string
    {
        return 'rating_info_list_action';
    }

    public function rateable(Model|Closure|null $rateable): static
    {
        $this->rateable = $rateable;
        return $this;
    }

    public function getRateable(): ?Model
    {
        return $this->evaluate($this->rateable);
    }

    public function rater(Model|Closure|null $rater): static
    {
        $this->rater = $rater;
        return $this;
    }

    public function getRater(): ?Model
    {
        return $this->evaluate($this->rater);
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
            $rateable = $this->getRateable();
            $rater = $this->getRater() ?? auth()->user();
            ratingAction::executeAction($data, $rateable, $rater, $this);
        })
            ->label(__('seller.rate.single'));
    }


}

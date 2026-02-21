<?php

namespace App\Filament\Actions;

use App\Models\Country;
use Closure;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Illuminate\Database\Eloquent\Model;
use Mokhosh\FilamentRating\Components\Rating;

class RatingAction extends Action
{
    protected Model|Closure|null $rateable = null;
    protected Model|Closure|null $rater = null;

    public static function getDefaultName(): ?string
    {
        return 'rating-action';
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
        $this->successNotificationTitle(__('seller.rate.send_success'));

        $this->form(static::getFromComponents());

        $this->action(function (array $data): void {
            $rateable = $this->getRateable();
            $rater = $this->getRater() ?? auth()->user();
            self::executeAction($data, $rateable, $rater, $this);
        })->label(__('seller.rate.single'));
    }

    public static function getFromComponents(): array
    {
        return [
            Rating::make('rating')
                ->label(__('seller.rate.single'))
                ->default(5)
                ->required()
                ->size('lg'),
            Textarea::make('review')->label(__('seller.rate.review'))->maxLength(550),
        ];
    }

    public static function executeAction(array $data, $rateable, $rater, $action): void
    {
        try {
            /*   @var Model $rateable */
            $existingRating = $rateable->ratings()
                ->whereMorphedTo('rater', $rater)
                ->first();

            if ($existingRating) {
                $existingRating->rating = (int) ($data['rating'] ?? $existingRating->rating);
                $existingRating->review = $data['review'];
                $existingRating->language = app()->currentLocale();
                if($rateable instanceof Country)
                    $existingRating->approved =false;
                else
                $existingRating->approved =true;
                $existingRating->save();
            } else {
                $rating = new \App\Models\Rating();
                $rating->rating = (int)$data['rating'];
                $rating->review = $data['review'];
                $rating->approved = !$rateable instanceof Country;
                $rating->language = app()->currentLocale();
                $rating->rater()->associate($rater);

                $rateable->ratings()->save($rating);
            }

            if (session()->exists('response_id'))
            {
                session()->forget('response_id');
            }
            if (session()->exists('request_id'))
            {
                session()->forget('request_id');
            }
            $action->success();
        } catch (Exception $ex) {
            $action->failureNotificationTitle = $ex->getMessage();
            $action->failure();
        }
    }
}

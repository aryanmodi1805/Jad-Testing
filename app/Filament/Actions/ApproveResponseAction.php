<?php

namespace App\Filament\Actions;

use App\Enums\RequestStatus;
use App\Enums\ResponseStatus;
use App\Events\RefreshRequestEvent;
use App\Events\RefreshResponseEvent;
use App\Filament\Resources\RequestResource\Pages\ViewRequest;
use App\Models\Response;
use App\Notifications\ResponseRatingNotification;
use App\Notifications\SiteReviewRequestNotification;
use Closure;
use Exception;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;
use Mokhosh\FilamentRating\Components\Rating;

class ApproveResponseAction extends Action
{
    protected Model|Closure|null $response = null;

    public static function getDefaultName(): ?string
    {
        return 'Approve Response';
    }

    public function response(Model|Closure|null $response): static
    {
        $this->response = $response;
        return $this;
    }

    public function getResponse(): ?Model
    {
        return $this->evaluate($this->response);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->icon('heroicon-o-check-circle');
        $this->color('success');
        $this->successNotificationTitle(__('responses.response_approved_successfully'));
        $this->action(function (array $data, $livewire , $action): void {
            /* @var $response Response*/
            $response = $this->getResponse();

            try {
                $request = $response->request;

                if (!$response->is_approved && $request->status !== RequestStatus::Completed) {
                    $response->status = ResponseStatus::Hired;
                    $response->is_approved = true;
                    $response->save();

                    $request->status = RequestStatus::Completed;
                    $request->save();
                    $request->responses()->where('id', '!=', $response->id)->update(['status' => ResponseStatus::Rejected]);

                   self::notify($response);

                    $action->success();
                    $sellerIds = $response->request?->responses()->pluck('seller_id')->toArray();
                    broadcast( new RefreshResponseEvent($sellerIds))->toOthers();
//                    broadcast( new RefreshRequestEvent($sellerIds))->toOthers();

                    $livewire->replaceMountedAction('rating-action');
                }
            } catch (Exception $ex) {
                $action->failureNotificationTitle = $ex->getMessage();
                $action->failure();
            }
//
//            $this->saveRating($data, $response);
//            self::executeAction($response, $this);
        })
            ->label(__('Approve'));
    }

//    protected function saveRating(array $data, $response): void
//    {
//        $seller = $response->seller;
//        $rater = auth()->user();
//
//        try {
//            $rating = new \App\Models\Rating();
//            $rating->rating = $data['rating'] ?? 3.5;
//            $rating->review = $data['review'] ?? 'Great seller!';
//            $rating->rater()->associate($rater);
//            $rating->response_id=$response->id;
//
//            $seller->ratings()->save($rating);
//        } catch (Exception $ex) {
//            $this->failureNotificationTitle = $ex->getMessage();
//            $this->failure();
//        }
//    }



    public static function notify($response): void
    {
        /*   @var Response $response */

        Notification::send($response->request->customer, new ResponseRatingNotification($response));

//        if (!$request->customer->siteReview) {
//            Notification::send($request->customer, new SiteReviewRequestNotification($request->customer));
//        }
    }
}



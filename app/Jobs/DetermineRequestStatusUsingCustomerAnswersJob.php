<?php

namespace App\Jobs;

use App\Enums\QuestionType;
use App\Enums\RequestStatus;
use App\Models\Request;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DetermineRequestStatusUsingCustomerAnswersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected Request $request;

    /**
     * Create a new job instance.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $isPending = false;

        foreach ($this->request->customerAnswers as $customerAnswer) {
            $question = $customerAnswer->question;

            if ($question->is_custom) {
                $isPending = true;
                break;
            }

            if (in_array($question->type, [QuestionType::SELECT, QuestionType::Checkbox])) {
                $customAnswer = $question->answers()->where('is_custom', true)->first();

                if ($customAnswer && $customerAnswer->answer_id == $customAnswer->id) {
                    $isPending = true;
                    break;
                }
            }
        }

        if ($isPending) {
            $this->request->status = RequestStatus::Pending;
            $this->request->save();
        }

    }
}

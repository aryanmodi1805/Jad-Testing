<?php

namespace App\Jobs;

use App\Enums\RequestStatus;
use App\Models\CustomerAnswer;
use App\Models\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use OpenAI\Laravel\Facades\OpenAI;

class ReviewRequestAIJob implements ShouldQueue , ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Request $request;
    protected CustomerAnswer $customerAnswer;
    public function __construct(CustomerAnswer $customerAnswer)
    {
        $this->customerAnswer= $customerAnswer;
        $this->request= $customerAnswer->request;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $result = OpenAI::audio()->transcribe([
            'model' => 'whisper-1',
            'file'=>Storage::disk('public')->readStream($this->customerAnswer->voice_note)
        ]);
        $this->customerAnswer->voice_note_text =$result->text;
        $this->customerAnswer->save();
        $user_query = <<<EOT
            Please evaluate the following text (Converted from Audio) to determine if it is Safe For Work (SFW) or potentially harmful.
            Return a JSON response with the following structure:
            {
                "text": "user_input",
                "is_acceptable": true_or_false,
                "reasons": "reasons_for_decision",
                "confidence": "confidence_percentage",
                "SFW_words": ["list_of_triggered_words"],
                "html": "highlight_triggered_words_in_red_with_full_input_html_format"
            }
            Text to check: "{$this->customerAnswer->voice_note_text}"
        EOT;
        $result = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo-0125',
            'messages' => [
                ['role' => 'system', 'content' => "You are a moderation SFW expert, your job is to check the texts if it's SFW or not."],
                ['role' => 'user', 'content' => $user_query.$this->customerAnswer->voice_note_text],
            ],
            'temperature'=> 0.2,
            'max_tokens' => 3000,
            'top_p' => 0.1,
            'frequency_penalty' => 0.2,
            'presence_penalty' => 0.1,
            'stop' => null
        ]);

        $result = collect(json_decode($result->choices[0]->message->content));
        $this->customerAnswer->voice_note_moderation = $result;
        $this->customerAnswer->save();

        if($result["is_acceptable"]==false) {
            $this->request->status = RequestStatus::Pending;
            $this->request->save();
        }
    }
    public $uniqueFor = 3600;

    public function uniqueId(): string
    {
        return $this->customerAnswer->id;
    }
}
